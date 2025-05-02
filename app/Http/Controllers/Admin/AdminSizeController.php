<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminSizeController extends Controller
{
    public function index()
    {
        $sizes = Size::withCount('products')->latest()->paginate(10);
        return view('admin.pages.sizes.index', compact('sizes'));
    }

    public function create()
    {
        return view('admin.pages.sizes.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:sizes,name',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Size::create($request->only(['name']));

        return redirect()->route('admin.sizes.index')
            ->with('success', 'Size created successfully');
    }

    public function show(Size $size)
    {
        $size->load('products');
        return view('admin.pages.sizes.show', compact('size'));
    }

    public function edit(Size $size)
    {
        return view('admin.pages.sizes.edit', compact('size'));
    }

    public function update(Request $request, Size $size)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:sizes,name,'.$size->id,
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $size->update($request->only(['name']));

        return redirect()->route('admin.sizes.index')
            ->with('success', 'Size updated successfully');
    }

    public function destroy(Size $size)
    {
        if ($size->products()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete size with associated products');
        }

        $size->delete();
        return redirect()->route('admin.sizes.index')
            ->with('success', 'Size deleted successfully');
    }
}