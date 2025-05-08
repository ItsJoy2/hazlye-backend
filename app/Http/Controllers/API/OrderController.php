<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\DeliveryOption;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantOption;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function deliveryOptions()
    {
        $options = DeliveryOption::where('is_active', true)->get();

        return response()->json([
            'success' => true,
            'data' => $options->map(function($option) {
                return [
                    'id' => $option->id,
                    'name' => $option->name,
                    'charge' => (float) $option->charge,
                    'estimated_days' => $option->estimated_days
                ];
            })
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'delivery_option_id' => 'required|exists:delivery_options,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.size_name' => 'nullable|string',
            'items.*.color_name' => 'nullable|string',
            'coupon_code' => 'nullable|string|exists:coupons,code',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $deliveryOption = DeliveryOption::findOrFail($request->delivery_option_id);
            $subtotal = 0;
            $items = [];
            $inventoryUpdates = [
                'items_updated' => 0,
                'total_stock_reduced' => 0
            ];

            foreach ($request->items as $itemData) {
                $product = Product::with(['variants.color', 'variants.options.size'])
                            ->findOrFail($itemData['product_id']);

                // Find variant with matching color
                $variant = $product->variants->first(function ($variant) use ($itemData) {
                    return $variant->color->name === ($itemData['color_name'] ?? null);
                });

                if (!$variant) {
                    throw new \Exception("Color '{$itemData['color_name']}' not available for product: {$product->name}. Available colors: " .
                        $product->variants->pluck('color.name')->implode(', '));
                }

                // Find option with matching size
                $option = $variant->options->first(function ($option) use ($itemData) {
                    return $option->size->name == ($itemData['size_name'] ?? null);
                });

                if (!$option) {
                    throw new \Exception("Size '{$itemData['size_name']}' not available for product: {$product->name}. Available sizes: " .
                        $variant->options->pluck('size.name')->implode(', '));
                }

                // Verify stock
                if ($option->stock < $itemData['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}. Available: {$option->stock}, Requested: {$itemData['quantity']}");
                }

                $itemPrice = $product->regular_price * $itemData['quantity'];
                $subtotal += $itemPrice;

                $items[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => (float) $product->regular_price,
                    'quantity' => (int) $itemData['quantity'],
                    'size_name' => $itemData['size_name'] ?? null,
                    'color_name' => $itemData['color_name'] ?? null,
                    'size_id' => $option->size->id ?? null,
                    'color_id' => $variant->color->id ?? null,
                    'variant_id' => $variant->id,
                    'option_id' => $option->id,
                    'color_code' => $variant->color->code ?? null
                ];
            }

            if ($request->coupon_code) {
                $coupon = Coupon::where('code', $request->coupon_code)->first();

                if ($coupon) {
                    $productIds = collect($request->items)->pluck('product_id')->toArray();

                    // Calculate subtotal for applicable products if coupon is product-specific
                    $applicableSubtotal = $subtotal;
                    if (!$coupon->apply_to_all) {
                        $applicableSubtotal = collect($request->items)
                            ->filter(fn($item) => in_array($item['product_id'], $coupon->products->pluck('id')->toArray()))
                            ->sum(fn($item) => $item['price'] * $item['quantity']);
                    }

                    if (!$coupon->isValid($subtotal, $productIds)) {
                        throw new \Exception('Coupon is not valid for this order. Minimum purchase: ' . $coupon->min_purchase);
                    }

                    $discount = $coupon->calculateDiscount($subtotal, $productIds, $applicableSubtotal);
                    $couponCode = $coupon->code;
                    $couponDetails = [
                        'code' => $coupon->code,
                        'discount_type' => $coupon->type,
                        'discount_value' => (float) $coupon->amount,
                        'min_purchase' => (float) $coupon->min_purchase,
                        'apply_to_all' => $coupon->apply_to_all,
                        'applicable_products' => $coupon->apply_to_all ? null : $coupon->products->pluck('id')
                    ];
                }
            }

            // Coupon handling
            $discount = 0;
            $couponCode = null;
            $couponDetails = null;

            if ($request->coupon_code) {
                $coupon = Coupon::with('products')->where('code', $request->coupon_code)->first();

                if ($coupon) {
                    // Calculate subtotal for applicable products if coupon is product-specific
                    $applicableSubtotal = $subtotal;
                    if (!$coupon->apply_to_all) {
                        $applicableSubtotal = collect($items)
                            ->filter(fn($item) => $coupon->products->contains('id', $item['product_id']))
                            ->sum(fn($item) => $item['price'] * $item['quantity']);
                    }

                    if (!$coupon->isValid($subtotal, $productIds)) {
                        throw new \Exception('Coupon is not valid for this order. Minimum purchase: ' . $coupon->min_purchase);
                    }

                    $discount = $coupon->calculateDiscount($subtotal, $productIds, $applicableSubtotal);
                    $couponCode = $coupon->code;
                    $couponDetails = [
                        'code' => $coupon->code,
                        'discount_type' => $coupon->type,
                        'discount_value' => (float) $coupon->amount,
                        'min_purchase' => (float) $coupon->min_purchase,
                        'apply_to_all' => $coupon->apply_to_all,
                        'applicable_products' => $coupon->apply_to_all ? null : $coupon->products->pluck('id')
                    ];
                }
            }

            $total = $subtotal + $deliveryOption->charge - $discount;

            // Create order

            $latestOrder = Order::latest()->first();
            $nextId = $latestOrder ? $latestOrder->id + 1 : 1;
            $orderNumber = 'H-' . str_pad($nextId, 5, '101', STR_PAD_LEFT);
            $order = Order::create([
                'order_number' => $orderNumber,
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
                'subtotal' => (float) $subtotal,
                'delivery_charge' => (float) $deliveryOption->charge,
                'discount' => (float) $discount,
                'total' => (float) $total,
                'coupon_code' => $couponCode,
                'status' => 'pending',
                'comment' => $request->comment,
            ]);

            // Create order items and update stock
            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'],
                    'size_id' => $item['size_id'],
                    'color_id' => $item['color_id'],
                    'product_name' => $item['product_name'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'size_name' => $item['size_name'],
                    'color_name' => $item['color_name'],
                ]);

                $rowsAffected = ProductVariantOption::where('id', $item['option_id'])
                    ->decrement('stock', $item['quantity']);

                if ($rowsAffected) {
                    $inventoryUpdates['items_updated']++;
                    $inventoryUpdates['total_stock_reduced'] += $item['quantity'];
                }
            }

            DB::commit();

            // Prepare response data
            $responseData = [
                'order' => [
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'customer' => [
                        'name' => $order->name,
                        'phone' => $order->phone,
                        'address' => $order->address
                    ],
                    'delivery' => [
                        'method' => $deliveryOption->name,
                        'charge' => (float) $deliveryOption->charge,
                        'estimated_days' => $deliveryOption->estimated_days
                    ],
                    'payment' => [
                        'subtotal' => (float) $order->subtotal,
                        'discount' => (float) $order->discount,
                        'delivery_charge' => (float) $order->delivery_charge,
                        'total' => (float) $order->total,
                        'coupon_applied' => $order->coupon_code,
                        'coupon_details' => $couponDetails
                    ],
                    'items' => array_map(function($item) {
                        return [
                            'product_id' => $item['product_id'],
                            'product_name' => $item['product_name'],
                            'variant' => [
                                'color' => $item['color_name'],
                                'color_code' => $item['color_code'],
                                'size' => $item['size_name']
                            ],
                            'quantity' => $item['quantity'],
                            'unit_price' => $item['price'],
                            'total_price' => $item['price'] * $item['quantity']
                        ];
                    }, $items),
                    'created_at' => $order->created_at->toIso8601String()
                ],
                'inventory_updates' => $inventoryUpdates
            ];

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $responseData
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Order creation failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}