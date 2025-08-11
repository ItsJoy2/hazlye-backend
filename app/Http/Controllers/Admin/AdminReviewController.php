<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with('product', 'user');

        if ($request->has('is_approved') && $request->is_approved !== 'all') {
            $query->where('is_approved', $request->is_approved);
        }

        // Filter by product if provided
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $reviews = $query->orderBy('created_at', 'desc')->paginate(15);
        $products = Product::pluck('name', 'id');

        return view('admin.pages.reviews.index', compact('reviews', 'products'));
    }

    public function create()
    {
        $products = Product::pluck('name', 'id');
        $users = User::pluck('name', 'id');
        return view('admin.pages.reviews.create', compact('products', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'user_id' => 'nullable|exists:users,id',
            'guest_name' => 'required_if:user_id,null|string|max:255',
            'guest_email' => 'nullable|email|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'is_approved' => 'nullable|boolean',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $review = Review::create([
            'product_id' => $request->product_id,
            'user_id' => $request->user_id,
            'guest_name' => $request->guest_name,
            'guest_email' => $request->guest_email,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_approved' => $request->is_approved ?? false,
        ]);

        if ($request->hasFile('images')) {
            $review->addImages($request->file('images'));
        }

        return redirect()->route('admin.reviews.index')->with('success', 'Review created successfully');
    }

    public function edit(Review $review)
    {
        $products = Product::pluck('name', 'id');
        $users = User::pluck('name', 'id');
        return view('admin.pages.reviews.edit', compact('review', 'products', 'users'));
    }

    public function update(Request $request, Review $review)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'user_id' => 'nullable|exists:users,id',
            'guest_name' => 'required_if:user_id,null|string|max:255',
            'guest_email' => 'nullable|email|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'is_approved' => 'nullable|boolean',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $review->update([
            'product_id' => $request->product_id,
            'user_id' => $request->user_id,
            'guest_name' => $request->guest_name,
            'guest_email' => $request->guest_email,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_approved' => $request->is_approved ?? $review->is_approved,
        ]);

        if ($request->hasFile('images')) {
            $review->addImages($request->file('images'));
        }

        return redirect()->route('admin.reviews.index')->with('success', 'Review updated successfully');
    }

    public function show(Review $review)
    {
        $review->load('product', 'user', 'images');
        return view('admin.pages.reviews.show', compact('review'));
    }


    public function approve(Review $review)
    {
        $review->update(['is_approved' => true]);
        return redirect()->back()->with('success', 'Review approved successfully');
    }

    public function destroy(Review $review)
    {
        $review->delete();
        return redirect()->route('admin.reviews.index')->with('success', 'Review deleted successfully');
    }
}