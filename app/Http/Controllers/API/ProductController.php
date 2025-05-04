<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'variants.color', 'variants.options.size'])
            ->latest()
            ->paginate(12);

        // Add full URL to all images
        $this->addFullImageUrls($products);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function show(Product $product)
    {
        $product->load([
            'category',
            'variants.color',
            'variants.options.size',
            'variants' => function($query) {
                $query->withCount('options');
            }
        ]);

        // Add full URL to all images
        if ($product->main_image) {
            $product->main_image = $this->getFullImageUrl($product->main_image);
        }

        if ($product->category && $product->category->image) {
            $product->category->image = $this->getFullImageUrl($product->category->image);
        }

        if ($product->variants) {
            $product->variants->transform(function ($variant) {
                if ($variant->image) {
                    $variant->image = $this->getFullImageUrl($variant->image);
                }
                return $variant;
            });
        }

        // Calculate inventory summary
        $inventorySummary = [
            'total_variants' => $product->variants->count(),
            'total_options' => $product->variants->sum('options_count'),
            'total_stock' => $product->variants->sum(function($variant) {
                return $variant->options->sum('stock');
            }),
            'out_of_stock' => $product->variants->sum(function($variant) {
                return $variant->options->where('stock', '<=', 0)->count();
            })
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'product' => $product,
                'inventory' => $inventorySummary
            ]
        ]);
    }

    public function featured()
    {
        $products = Product::with(['category', 'variants.color', 'variants.options.size'])
            ->where('featured', true)
            ->latest()
            ->take(8)
            ->get();

        // Add full URL to all images
        $products->transform(function ($product) {
            if ($product->main_image) {
                $product->main_image = $this->getFullImageUrl($product->main_image);
            }

            if ($product->category && $product->category->image) {
                $product->category->image = $this->getFullImageUrl($product->category->image);
            }

            if ($product->variants) {
                $product->variants->transform(function ($variant) {
                    if ($variant->image) {
                        $variant->image = $this->getFullImageUrl($variant->image);
                    }
                    return $variant;
                });
            }

            return $product;
        });

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function byCategory(Category $category)
    {
        $products = Product::with(['category', 'variants.color', 'variants.options.size'])
            ->where('category_id', $category->id)
            ->latest()
            ->paginate(12);

        // Add full URL to all images
        $this->addFullImageUrls($products);

        return response()->json([
            'success' => true,
            'data' => [
                'category' => $category->image ? $category->only(['id', 'name', 'slug', 'image']) : $category->only(['id', 'name', 'slug']),
                'products' => $products
            ]
        ]);
    }

    protected function getFullImageUrl($path)
    {
        return $path ? url('storage/' . $path) : null;
    }

    protected function addFullImageUrls($products)
    {
        $products->getCollection()->transform(function ($product) {
            if ($product->main_image) {
                $product->main_image = $this->getFullImageUrl($product->main_image);
            }

            if ($product->category && $product->category->image) {
                $product->category->image = $this->getFullImageUrl($product->category->image);
            }

            if ($product->variants) {
                $product->variants->transform(function ($variant) {
                    if ($variant->image) {
                        $variant->image = $this->getFullImageUrl($variant->image);
                    }
                    return $variant;
                });
            }

            return $product;
        });
    }
}