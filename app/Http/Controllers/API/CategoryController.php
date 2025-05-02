<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('children')->whereNull('parent_id')->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    public function show(Category $category)
    {
        $category->load('children', 'parent');

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }

    public function products(Category $category, Request $request)
    {
        $perPage = $request->input('per_page', 12);

        $products = $category->products()
            ->with(['images' => function($query) {
                $query->where('is_primary', true);
            }])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }
}