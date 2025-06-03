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
            'data' => $options->map(function ($option) {
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
            $product = Product::with(['variants.color', 'variants.options.size'])->findOrFail($itemData['product_id']);

            $hasVariants = $product->has_variants;

            $variant = null;
            $option = null;
            $colorName = null;
            $sizeName = null;
            $variantId = null;
            $optionId = null;
            $colorId = null;
            $sizeId = null;
            $colorCode = null;

            if ($hasVariants) {
                // Handle products with variants
                if (empty($itemData['color_name'])) {
                    throw new \Exception("Color is required for product with variants: {$product->name}");
                }

                $variant = $product->variants->first(function ($variant) use ($itemData) {
                    return $variant->color->name === $itemData['color_name'];
                });

                if (!$variant) {
                    throw new \Exception("Color '{$itemData['color_name']}' not available for product: {$product->name}. Available colors: " .
                        $product->variants->pluck('color.name')->implode(', '));
                }

                if (empty($itemData['size_name'])) {
                    throw new \Exception("Size is required for product with variants: {$product->name}");
                }

                $option = $variant->options->first(function ($option) use ($itemData) {
                    return $option->size->name == $itemData['size_name'];
                });

                if (!$option) {
                    throw new \Exception("Size '{$itemData['size_name']}' not available for product: {$product->name}. Available sizes: " .
                        $variant->options->pluck('size.name')->implode(', '));
                }

                if ($option->stock < $itemData['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}. Available: {$option->stock}, Requested: {$itemData['quantity']}");
                }

                $colorName = $variant->color->name;
                $sizeName = $option->size->name;
                $variantId = $variant->id;
                $optionId = $option->id;
                $colorId = $variant->color->id;
                $sizeId = $option->size->id;
                $colorCode = $variant->color->code;
            } else {
                // Handle products without variants
                if ($product->total_stock < $itemData['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->name}. Available: {$product->total_stock}, Requested: {$itemData['quantity']}");
                }

                // Only set size if the product has a size (but no variants)
                if ($product->size_id && !empty($itemData['size_name'])) {
                    $sizeName = $itemData['size_name'];
                    $sizeId = $product->size_id;
                }
            }

            $itemPrice = $product->regular_price * $itemData['quantity'];
            $subtotal += $itemPrice;

            $items[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price' => (float) $product->regular_price,
                'quantity' => (int) $itemData['quantity'],
                'size_name' => $sizeName,
                'color_name' => $colorName,
                'size_id' => $sizeId,
                'color_id' => $colorId,
                'variant_id' => $variantId,
                'option_id' => $optionId,
                'color_code' => $colorCode
            ];
        }

        $discount = 0;
        $couponCode = null;
        $couponDetails = null;

        if ($request->coupon_code) {
            $coupon = Coupon::with('products')->where('code', $request->coupon_code)->first();

            if ($coupon) {
                $productIds = collect($request->items)->pluck('product_id')->toArray();

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

        $latestOrder = Order::latest()->first();
        $datePart = now()->format('Ymd');
        $randomDigits = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $orderNumber = "H-{$datePart}-{$randomDigits}"; 

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

            if ($item['option_id']) {
                // Update variant option stock
                $rowsAffected = ProductVariantOption::where('id', $item['option_id'])
                    ->decrement('stock', $item['quantity']);
            } else {
                // Update product stock directly
                $rowsAffected = Product::where('id', $item['product_id'])
                    ->decrement('total_stock', $item['quantity']);
            }

            if ($rowsAffected) {
                $inventoryUpdates['items_updated']++;
                $inventoryUpdates['total_stock_reduced'] += $item['quantity'];
            }
        }

        DB::commit();

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
                'items' => array_map(function ($item) {
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
