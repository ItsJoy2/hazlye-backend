<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Review;
use App\Models\Product;
use App\Models\ReviewImage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class AdminReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::with(['user', 'product', 'images'])
            ->orderBy('is_approved')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.pages.reviews.index', compact('reviews'));
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
            'name' => 'required|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'description' => 'required|string|max:1000',
            'is_approved' => 'required|boolean',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'images' => 'max:5'
        ]);

        try {
            // Create user with random Bangladeshi mobile number
            $mobile = '01' . rand(3, 9) . rand(10000000, 99999999);

            $user = User::create([
                'name' => $request->name,
                'mobile' => $mobile,
                'password' => bcrypt('12345678')
            ]);

            $review = Review::create([
                'user_id' => $user->id,
                'product_id' => $request->product_id,
                'rating' => $request->rating,
                'description' => $request->description,
                'is_approved' => $request->is_approved
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

            return redirect()->route('admin.reviews.index')
                ->with('success', 'Review created successfully');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create review: ' . $e->getMessage());
        }
    }

    public function approve(Review $review)
    {
        $review->update(['is_approved' => true]);

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Review approved successfully');
    }

    public function destroy(Review $review)
    {
        // Delete associated images from storage
        foreach ($review->images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }

        $review->delete();

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Review deleted successfully');
    }
}