<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SearchController extends Controller
{
    // public function search(Request $request)
    // {
    //     $query = $request->input('query');
    //     $minPrice = $request->input('min_price');
    //     $maxPrice = $request->input('max_price');
    //     $categoryId = $request->input('category_id');
    //     $colorId = $request->input('color_id');
    //     $sizeId = $request->input('size_id');
    //     $featured = $request->input('featured');
    //     $sortBy = $request->input('sort_by', 'created_at');
    //     $sortOrder = $request->input('sort_order', 'desc');
    //     $perPage = $request->input('per_page', 10);

    //     $products = Product::when($query, function ($q) use ($query) {
    //             $q->where(function($q) use ($query) {
    //                 $q->where('name', 'LIKE', "%{$query}%")
    //                   ->orWhere('description', 'LIKE', "%{$query}%")
    //                   ->orWhere('sku', 'LIKE', "%{$query}%");
    //             });
    //         })
    //         ->when($minPrice, function ($q) use ($minPrice) {
    //             $q->where('regular_price', '>=', $minPrice);
    //         })
    //         ->when($maxPrice, function ($q) use ($maxPrice) {
    //             $q->where('regular_price', '<=', $maxPrice);
    //         })
    //         ->when($categoryId, function ($q) use ($categoryId) {
    //             $q->where('category_id', $categoryId);
    //         })
    //         ->when($featured, function ($q) use ($featured) {
    //             $q->where('featured', filter_var($featured, FILTER_VALIDATE_BOOLEAN));
    //         })
    //         ->when($colorId || $sizeId, function ($q) use ($colorId, $sizeId) {
    //             $q->whereHas('variants', function ($q) use ($colorId) {
    //                 if ($colorId) {
    //                     $q->where('color_id', $colorId);
    //                 }
    //             })
    //             ->whereHas('variants.options', function ($q) use ($sizeId) {
    //                 if ($sizeId) {
    //                     $q->where('size_id', $sizeId);
    //                 }
    //             });
    //         })
    //         ->with(['category', 'variants.color', 'variants.options.size', 'approvedReviews'])
    //         ->orderBy($sortBy, $sortOrder)
    //         ->paginate($perPage);

    //     return response()->json([
    //         'success' => true,
    //         'data' => $products,
    //         'message' => 'Search results'
    //     ]);
    // }
    // public function searchByName(Request $request)
    // {
    //     try {
    //         $validated = $request->validate([
    //             'name' => 'required|string|min:2|max:255',
    //         ]);

    //         $products = Product::where('name', 'LIKE', '%' . $validated['name'] . '%')
    //             ->with(['category', 'variants.color', 'variants.options.size'])
    //             ->paginate($request->input('per_page', 10));

    //         return response()->json([
    //             'success' => true,
    //             'data' => $products,
    //         ]);

    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'errors' => $e->errors(),
    //         ], 422);
    //     }
    // }

    public function search(Request $request)
{
    return response()->json(['message' => 'Search endpoint works!']);
}

public function searchByName(Request $request)
{
    return response()->json(['message' => 'SearchByName endpoint works!']);
}

}
