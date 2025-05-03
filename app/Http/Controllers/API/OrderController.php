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

class OrderController extends Controller
{
    public function deliveryOptions()
    {
        $options = DeliveryOption::where('is_active', true)->get();
        return response()->json($options);
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
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $deliveryOption = DeliveryOption::findOrFail($request->delivery_option_id);
            $subtotal = 0;
            $items = [];

            foreach ($request->items as $itemData) {
                $product = Product::with(['variants.color', 'variants.options.size'])
                            ->findOrFail($itemData['product_id']);

                // Find variant with matching color
                $variant = $product->variants->first(function ($variant) use ($itemData) {
                    return $variant->color->name === ($itemData['color_name'] ?? null);
                });

                if (!$variant) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Color not available for product: ' . $product->name,
                        'product_id' => $product->id,
                        'available_colors' => $product->variants->pluck('color.name')
                    ], 400);
                }

                // Find option with matching size
                $option = $variant->options->first(function ($option) use ($itemData) {
                    return $option->size->name === ($itemData['size_name'] ?? null);
                });

                if (!$option) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Size not available for product: ' . $product->name,
                        'product_id' => $product->id,
                        'available_sizes' => $variant->options->pluck('size.name')
                    ], 400);
                }

                // Verify stock
                if ($option->stock < $itemData['quantity']) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Insufficient stock for product: ' . $product->name,
                        'product_id' => $product->id,
                        'color' => $variant->color->name,
                        'size' => $option->size->name,
                        'available_stock' => $option->stock
                    ], 400);
                }

                $itemPrice = $product->regular_price * $itemData['quantity'];
                $subtotal += $itemPrice;

                $items[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->regular_price,
                    'quantity' => $itemData['quantity'],
                    'size_name' => $itemData['size_name'] ?? null,
                    'color_name' => $itemData['color_name'] ?? null,
                    'size_id' => $option->size->id ?? null,
                    'color_id' => $variant->color->id ?? null,
                    'variant_id' => $variant->id,
                    'option_id' => $option->id
                ];
            }

            // Coupon handling
            $discount = 0;
            $couponCode = null;

            if ($request->coupon_code) {
                $coupon = Coupon::where('code', $request->coupon_code)->first();

                if ($coupon && !$coupon->isValid($subtotal)) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Coupon is not valid for this order',
                        'reasons' => [
                            'is_active' => $coupon->is_active,
                            'date_valid' => now()->between($coupon->start_date, $coupon->end_date),
                            'min_purchase' => $subtotal >= $coupon->min_purchase
                        ]
                    ], 400);
                }

                if ($coupon) {
                    $discount = $coupon->calculateDiscount($subtotal);
                    $couponCode = $coupon->code;
                }
            }

            $total = $subtotal + $deliveryOption->charge - $discount;

            // Create order
            $order = Order::create([
                'order_number' => 'ORD-' . now()->format('Ymd') . '-' . Str::upper(Str::random(4)),
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
                'subtotal' => $subtotal,
                'delivery_charge' => $deliveryOption->charge,
                'discount' => $discount,
                'total' => $total,
                'coupon_code' => $couponCode,
                'status' => 'pending',
                'comment' => $request->comment,
            ]);

            // Create order items and update stock
            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    ...$item
                ]);

                ProductVariantOption::where('id', $item['option_id'])
                    ->decrement('stock', $item['quantity']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Order created successfully',
                'order_number' => $order->order_number,
                'order' => $order->load('items', 'coupon'),
                'summary' => [
                    'subtotal' => $subtotal,
                    'delivery_charge' => $deliveryOption->charge,
                    'discount' => $discount,
                    'total' => $total
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Order creation failed',
                'error' => $e->getMessage(),
                'trace' => $e->getTrace()
            ], 500);
        }
    }
}