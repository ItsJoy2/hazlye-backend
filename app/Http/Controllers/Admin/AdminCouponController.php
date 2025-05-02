<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class AdminCouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::all();

        return response()->json([
            'success' => true,
            'data' => $coupons
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:coupons,code',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|string|in:fixed,percentage',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'min_purchase' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $coupon = Coupon::create([
            'code' => strtoupper($request->code),
            'amount' => $request->amount,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'min_purchase' => $request->min_purchase,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Coupon created successfully',
            'data' => $coupon
        ], 201);
    }

    public function show(Coupon $coupon)
    {
        return response()->json([
            'success' => true,
            'data' => $coupon
        ]);
    }

    public function update(Request $request, Coupon $coupon)
    {
        $request->validate([
            'code' => 'required|string|unique:coupons,code,' . $coupon->id,
            'amount' => 'required|numeric|min:0',
            'type' => 'required|string|in:fixed,percentage',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'min_purchase' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $coupon->update([
            'code' => strtoupper($request->code),
            'amount' => $request->amount,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'min_purchase' => $request->min_purchase,
            'is_active' => $request->is_active ?? $coupon->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Coupon updated successfully',
            'data' => $coupon
        ]);
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return response()->json([
            'success' => true,
            'message' => 'Coupon deleted successfully'
        ]);
    }
}