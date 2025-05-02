<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryOption;
use Illuminate\Http\Request;

class AdminDeliveryOptionController extends Controller
{
    public function index()
    {
        $deliveryOptions = DeliveryOption::all();

        return response()->json([
            'success' => true,
            'data' => $deliveryOptions
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'charge' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $deliveryOption = DeliveryOption::create([
            'name' => $request->name,
            'charge' => $request->charge,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Delivery option created successfully',
            'data' => $deliveryOption
        ], 201);
    }

    public function show(DeliveryOption $deliveryOption)
    {
        return response()->json([
            'success' => true,
            'data' => $deliveryOption
        ]);
    }

    public function update(Request $request, DeliveryOption $deliveryOption)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'charge' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $deliveryOption->update([
            'name' => $request->name,
            'charge' => $request->charge,
            'is_active' => $request->is_active ?? $deliveryOption->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Delivery option updated successfully',
            'data' => $deliveryOption
        ]);
    }

    public function destroy(DeliveryOption $deliveryOption)
    {
        $deliveryOption->delete();

        return response()->json([
            'success' => true,
            'message' => 'Delivery option deleted successfully'
        ]);
    }
}