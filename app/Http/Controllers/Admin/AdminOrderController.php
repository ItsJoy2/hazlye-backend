<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class AdminOrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['items', 'coupon'])
            ->latest()
            ->paginate(10);

        return view('admin.pages.orders.index', compact('orders'));
    }

    public function edit(Order $order)
    {
        $order->load(['items.product', 'coupon']);

        return view('admin.pages.orders.edit', compact('order'));
    }
    public function show(Order $order)
    {
        $order->load(['items.product', 'coupon']);

        return view('admin.pages.orders.show', compact('order'));
    }
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|string|max:255',
            'comment' => 'nullable|string'
        ]);

        $order->update($validated);

        return back()->with('success', 'Order status updated successfully');
    }
    public function customerList()
    {
        $customers = Order::select([
            'name',
            'phone',
            DB::raw('MIN(address) as primary_address'),
            DB::raw('COUNT(DISTINCT orders.id) as order_count'),
            DB::raw('SUM(order_items.quantity) as total_products'),
            DB::raw('SUM(orders.total) as total_spent'),
            DB::raw('MAX(orders.created_at) as last_order_at')
        ])
        ->join('order_items', 'orders.id', '=', 'order_items.order_id')
        ->groupBy('name', 'phone')
        ->orderBy('total_spent', 'desc')
        ->paginate(20);

    // Format dates and numbers
    $customers->getCollection()->transform(function ($customer) {
        return [
            'name' => $customer->name,
            'phone' => $customer->phone,
            'primary_address' => $customer->primary_address,
            'order_count' => $customer->order_count,
            'total_products' => $customer->total_products,
            'total_spent' => number_format($customer->total_spent, 2),
            'last_order_at' => Carbon::parse($customer->last_order_at)->format('M d, Y'),
            'last_order_raw' => $customer->last_order_at
        ];
    });

    return view('admin.pages.customers.index', compact('customers'));
    }
}