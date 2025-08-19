<?php

namespace App\Http\Controllers\Admin;


use id;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminReviewController extends Controller
{
    public function index()
    {
        $products = Product::pluck('name', 'id');
        $query = Review::with(['user', 'product', 'images'])->latest();

        // Add filters if they exist in the request
        if (request()->has('is_approved') && request('is_approved') !== 'all') {
            $query->where('is_approved', request('is_approved'));
        }

        if (request()->has('product_id') && request('product_id')) {
            $query->where('product_id', request('product_id'));
        }

        $reviews = $query->paginate(10);

        return view('admin.pages.reviews.index', compact('reviews', 'products'));
    }
    public function create()
    {
        $products = Product::all();
        return view('admin.pages.reviews.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'description' => 'required|string',
        ]);

        Review::create([
            'user_id' => auth()->id(),
            'product_id' => $request->product_id,
            'rating' => $request->rating,
            'description' => $request->description,
            'is_approved' => true
        ]);

        return redirect()->route('admin.reviews.index')->with('success', 'Review added successfully');
    }

    public function edit(Review $review)
    {
        $products = Product::all();
        return view('admin.pages.reviews.edit', compact('review', 'products'));
    }

    public function update(Request $request, Review $review)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'description' => 'required|string',
            'is_approved' => 'required|boolean'
        ]);

        $review->update($request->all());

        return redirect()->route('admin.reviews.index')->with('success', 'Review updated successfully');
    }

    public function destroy(Review $review)
    {
        $review->delete();
        return redirect()->route('admin.reviews.index')->with('success', 'Review deleted successfully');
    }
}