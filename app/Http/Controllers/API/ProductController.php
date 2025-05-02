<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'variants.color', 'variants.options.size'])
            ->latest()
            ->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function show(Product $product)
    {
        $product->load([
            'category',
            'variants.color',
            'variants.options.size',
            'variants' => function($query) {
                $query->withCount('options');
            }
        ]);

        // Calculate inventory summary
        $inventorySummary = [
            'total_variants' => $product->variants->count(),
            'total_options' => $product->variants->sum('options_count'),
            'total_stock' => $product->variants->sum(function($variant) {
                return $variant->options->sum('stock');
            }),
            'out_of_stock' => $product->variants->sum(function($variant) {
                return $variant->options->where('stock', '<=', 0)->count();
            })
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'product' => $product,
                'inventory' => $inventorySummary
            ]
        ]);
    }

    public function featured()
    {
        $products = Product::with(['category', 'variants.color', 'variants.options.size'])
            ->where('featured', true)
            ->latest()
            ->take(8)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function byCategory(Category $category)
    {
        $products = Product::with(['category', 'variants.color', 'variants.options.size'])
            ->where('category_id', $category->id)
            ->latest()
            ->paginate(12);

        return response()->json([
            'success' => true,
            'data' => [
                'category' => $category,
                'products' => $products
            ]
        ]);
    }
}