<?php

namespace App\Http\Controllers\Api;

use Log;
use Illuminate\Http\Request;
use App\Models\HomepageSection;
use App\Http\Controllers\Controller;

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
        Log::error('HomepageSection show error: '.$e->getMessage());
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
                'slug' => $category->slug,
                'order' => $category->pivot->order ?? 0,
                'products' => $category->products->map(function ($product) {
                    if (!$product) return null;

                    $productData = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'price' => $product->regular_price,
                        'discount_price' => $product->discount_price,
                        'total_stock' => $product->total_stock,
                        'image' => $product->image_url ?? null,
                        'images' => $product->images->pluck('image') ?? [],
                        'is_featured' => $product->is_featured,
                        'is_offer' => $product->is_offer,
                        'variants' => []
                    ];

                    if ($product->variants->count() > 0) {
                        $productData['variants'] = $product->variants->map(function ($variant) {
                            return [
                                'id' => $variant->id,
                                'color_id' => $variant->color_id,
                                'color_name' => $variant->color->name ?? null,
                                'image' => $variant->image,
                                'options' => $variant->options->map(function ($option) {
                                    return [
                                        'id' => $option->id,
                                        'size_id' => $option->size_id,
                                        'size_name' => $option->size->name ?? null,
                                        'price' => $option->price,
                                        'stock' => $option->stock,
                                        'sku' => $option->sku
                                    ];
                                })
                            ];
                        });
                    }

                    return $productData;
                })->filter()->values()
            ];
        })->filter()->values()
    ];
}
}