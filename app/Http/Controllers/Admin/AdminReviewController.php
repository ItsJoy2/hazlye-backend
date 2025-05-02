<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class AdminReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with('product');

        // Filter by approval status if provided
        if ($request->has('is_approved')) {
            $query->where('is_approved', $request->is_approved);
        }

        // Filter by product if provided
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $reviews = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    public function show(Review $review)
    {
        $review->load('product');

        return response()->json([
            'success' => true,
            'data' => $review
        ]);
    }

    public function update(Request $request, Review $review)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'is_approved' => 'nullable|boolean',
        ]);

        $review->update([
            'name' => $request->name,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_approved' => $request->is_approved ?? $review->is_approved,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully',
            'data' => $review
        ]);
    }

    public function approve(Review $review)
    {
        $review->update([
            'is_approved' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review approved successfully',
            'data' => $review
        ]);
    }

    public function destroy(Review $review)
    {
        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully'
        ]);
    }
}