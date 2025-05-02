<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ProductVariantOption;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_option_id' => 'required|exists:product_variant_options,id',
            'items.*.quantity' => 'required|integer|min:1',
            'coupon_code' => 'nullable|exists:coupons,code',
            'comment' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Calculate order totals
            $subtotal = 0;
            $items = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $option = ProductVariantOption::with(['variant.color', 'size'])
                    ->findOrFail($item['variant_option_id']);

                $price = $option->price;
                $quantity = $item['quantity'];
                $itemTotal = $price * $quantity;

                $items[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $price,
                    'quantity' => $quantity,
                    'size_name' => $option->size->name,
                    'color_name' => $option->variant->color->name,
                ];

                $subtotal += $itemTotal;
            }

            // Calculate delivery charge (example: free for orders over $50)
            $deliveryCharge = $subtotal > 5000 ? 0 : 500; // 500 = $5.00

            // Apply coupon discount if provided
            $discount = 0;
            if ($request->coupon_code) {
                $coupon = Coupon::where('code', $request->coupon_code)
                    ->where('valid_from', '<=', now())
                    ->where('valid_to', '>=', now())
                    ->first();

                if ($coupon) {
                    if ($coupon->type === 'fixed') {
                        $discount = $coupon->value;
                    } else {
                        $discount = ($subtotal * $coupon->value) / 100;
                    }
                }
            }

            $total = $subtotal + $deliveryCharge - $discount;

            // Create the order
            $order = Order::create([
                'order_number' => 'ORD-' . Str::upper(Str::random(8)),
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
                'subtotal' => $subtotal,
                'delivery_charge' => $deliveryCharge,
                'discount' => $discount,
                'total' => $total,
                'coupon_code' => $request->coupon_code,
                'status' => 'pending',
                'comment' => $request->comment,
            ]);

            // Create order items
            foreach ($items as $item) {
                $order->items()->create($item);
            }

            // Update product stock
            foreach ($request->items as $item) {
                $option = ProductVariantOption::find($item['variant_option_id']);
                $option->decrement('stock', $item['quantity']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'order' => $order,
                'order_number' => $order->order_number
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Order failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Order $order)
    {
        $order->load(['items', 'coupon']);

        return response()->json([
            'success' => true,
            'order' => $order
        ]);
    }

    public function userOrders($userId)
    {
        // In a real app, you'd authenticate the user and get their ID
        $orders = Order::with(['items'])
            ->where('user_id', $userId) // Assuming you have user_id column
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'orders' => $orders
        ]);
    }
}