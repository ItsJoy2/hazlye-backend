<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use App\Models\ReviewImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    public function index(Product $product)
    {
        $reviews = $product->reviews()
            ->with('user', 'images')
            ->where('is_approved', true)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    public function store(Request $request, $product)
    {
        // Find product by ID or slug
        $product = Product::where('id', $product)
                   ->orWhere('slug', $product)
                   ->firstOrFail();

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'description' => 'required|string|max:1000',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'images' => 'max:5'
        ]);

        // Check if user already reviewed this product
        $existingReview = Review::where('user_id', Auth::id())
            ->where('product_id', $product->id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this product'
            ], 409);
        }

        try {
            $review = Review::create([
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'rating' => $request->rating,
                'description' => $request->description,
                'is_approved' => false
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('review_images', 'public');
                    ReviewImage::create([
                        'review_id' => $review->id,
                        'image_path' => $path,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Review submitted successfully. It will be visible after approval.',
                'data' => $review->load('images')
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit review',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Review $review)
    {
        if (!$review->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found or not approved yet'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $review->load('user', 'images')
        ]);
    }
}