<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

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
}