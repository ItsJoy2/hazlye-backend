<?php

namespace App\Http\Controllers\Api;

use App\Models\HomepageSection;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomepageSectionController extends Controller
{
    public function index()
{
    $sections = HomepageSection::with(['categories' => function($query) {
            $query->with(['products' => function($query) {
                $query->active()
                      ->with('images') 
                      ->orderBy('created_at', 'desc');
            }]);
        }])
        ->where('is_active', true)
        ->orderBy('position')
        ->get()
        ->map(function ($section) {
            return $this->formatSection($section);
        });

    return response()->json($sections);
}
public function show($id)
{
    try {
        $section = HomepageSection::with(['categories' => function($query) {
                $query->with(['products' => function($query) {
                    $query->active() // Use the same scope as index
                          ->with('images') // Eager load images consistently
                          ->orderBy('created_at', 'desc');
                }]);
            }])
            ->where('is_active', true)
            ->findOrFail($id);

        return response()->json($this->formatSection($section));

    } catch (\Exception $e) {
        \Log::error('HomepageSection show error: '.$e->getMessage());
        return response()->json([
            'error' => 'Server error',
            'message' => $e->getMessage()
        ], 500);
    }
}

protected function formatSection($section)
{
    if (!$section) {
        return null;
    }

    return [
        'id' => $section->id,
        'name' => $section->name,
        'position' => $section->position,
        'categories' => $section->categories->map(function ($category) {
            if (!$category) return null;

            return [
                'id' => $category->id,
                'name' => $category->name,
                'order' => $category->pivot->order ?? 0, // Default value if pivot doesn't exist
                'products' => $category->products->map(function ($product) {
                    if (!$product) return null;

                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'price' => $product->regular_price,
                        'discount_price' => $product->discount_price,
                        'image' => $product->image_url ?? null, // Safe null access
                        'images' => $product->images->pluck('image') ?? [], // Safe array access
                        'is_featured' => $product->is_featured,
                        'is_offer' => $product->is_offer
                    ];
                })->filter()->values() // Remove nulls and reindex
            ];
        })->filter()->values() // Remove nulls and reindex
    ];
}
}