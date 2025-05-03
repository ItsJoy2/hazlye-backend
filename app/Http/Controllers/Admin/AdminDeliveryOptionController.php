<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryOption;
use Illuminate\Http\Request;

class AdminDeliveryOptionController extends Controller
{
    public function index()
    {
        $deliveryOptions = DeliveryOption::paginate(10);
        return view('admin.pages.delivery-options.index', compact('deliveryOptions'));
    }

    public function create()
    {
        return view('admin.pages.delivery-options.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'charge' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        DeliveryOption::create($validated);

        return redirect()->route('admin.delivery-options.index')
            ->with('success', 'Delivery option created successfully.');
    }

    public function edit(DeliveryOption $deliveryOption)
    {
        return view('admin.pages.delivery-options.edit', compact('deliveryOption'));
    }

    public function update(Request $request, DeliveryOption $deliveryOption)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'charge' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        $deliveryOption->update($validated);

        return redirect()->route('admin.delivery-options.index')
            ->with('success', 'Delivery option updated successfully.');
    }

    public function destroy(DeliveryOption $deliveryOption)
    {
        $deliveryOption->delete();

        return redirect()->route('admin.delivery-options.index')
            ->with('success', 'Delivery option deleted successfully.');
    }
}