<?php

// app/Http/Controllers/Admin/CouponController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminCouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::latest()->paginate(10);
        return view('admin.pages.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('admin.pages.coupons.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'nullable|string|unique:coupons,code',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:fixed,percentage',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'min_purchase' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        // Generate random code if not provided
        if (empty($validated['code'])) {
            $validated['code'] = Str::upper(Str::random(8));
        }

        Coupon::create($validated);

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon created successfully');
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.pages.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:coupons,code,'.$coupon->id,
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:fixed,percentage',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'min_purchase' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $coupon->update($validated);

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon updated successfully');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon deleted successfully');
    }
}