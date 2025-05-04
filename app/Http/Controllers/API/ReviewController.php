<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
{
    $validator = Validator::make($request->all(), [
        'rating' => 'required|integer|between:1,5',
        'comment' => 'nullable|string|max:500',
        'guest_name' => 'required_without:user_id|string|max:100',
        'guest_email' => 'required_without:user_id|email|max:100',
        'images' => 'nullable|array',
        'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    $review = Review::create([
        'product_id' => $product->id,
        'user_id' => auth()->id(),
        'guest_name' => $request->guest_name,
        'guest_email' => $request->guest_email,
        'rating' => $request->rating,
        'comment' => $request->comment
    ]);

    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            $path = $image->store('review_images', 'public');
            $review->images()->create(['image_path' => $path]);
        }
    }

    return response()->json([
        'success' => true,
        'data' => $review->load('images')
    ], 201);
}
}