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
    // public function __construct()
    // {
    //     $this->middleware('auth:api')->except(['index', 'show']);
    // }

    // public function index(Product $product)
    // {
    //     $reviews = $product->reviews()->with('user', 'images')->get();
    //     return response()->json($reviews);
    // }

    public function store(Request $request, $product)
    {
        $product = Product::where('id', $product)
                   ->orWhere('slug', $product)
                   ->firstOrFail();

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'description' => 'required|string|max:1000',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'images' => 'max:5'
        ]);

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
            'message' => 'Review submitted successfully',
            'review' => $review->load('images')
        ], 201);
    }

    public function show(Review $review)
    {
        return response()->json($review->load('user', 'images'));
    }
}