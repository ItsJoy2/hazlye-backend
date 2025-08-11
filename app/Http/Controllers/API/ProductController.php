<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['images', 'category', 'variants.color', 'variants.options.size'])
            ->active()
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

        $product->makeHidden('Purchase_price');

        // Add full URL to all images
        if ($product->main_image) {
            $product->main_image = $this->getFullImageUrl($product->main_image);
        }

        if ($product->images) {
            $product->images->transform(function ($image) {
                $image->image_path = $this->getFullImageUrl($image->image_path);
                return $image;
            });
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

    protected function getFullImageUrl($path)
    {
        return $path ? url('public/storage/' . $path) : null;
    }

    protected function addFullImageUrls($products)
    {
        $products->getCollection()->transform(function ($product) {
            if ($product->main_image) {
                $product->main_image = $this->getFullImageUrl($product->main_image);
            }

            if ($product->main_image_2) {
                $product->main_image_2 = $this->getFullImageUrl($product->main_image_2);
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

    public function byCategory(Category $category, Request $request)
    {
        try {
            $perPage = $request->input('per_page', 12);

            $products = Product::with(['category', 'variants.color', 'variants.options.size'])
                ->where('category_id', $category->id)
                ->withCount('reviews')
                ->withAvg('reviews', 'rating')
                ->latest()
                ->paginate($perPage);

            $products->each(function ($product) {
                $product->makeHidden('Purchase_price');
            });

            // Add full URL to all images
            $this->addFullImageUrls($products);

            return response()->json([
                'success' => true,
                'data' => [
                    'category' => [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'image' => $category->image ? $this->getFullImageUrl($category->image) : null
                    ],
                    'products' => $products
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load products',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function couponsCheck(Request $request)
{
    $couponCode = $request->input('coupon');

    $coupon = Coupon::where('code', $couponCode)->first();

    if (!$coupon) {
        return response()->json([
            'success' => false,
            'message' => 'Coupon not found'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'data' => [
            'id' => $coupon->id,
            'code' => $coupon->code,
            'type' => $coupon->type,
            'amount' => $coupon->amount,
            'minimum_purchase' => $coupon->min_purchase,
            'start_date' => $coupon->start_date,
            'end_date' => $coupon->end_date,
            'is_active' => $coupon->is_active,
            'is_currently_valid' => $coupon->is_active &&
                                 now()->between($coupon->start_date, $coupon->end_date)
        ]
    ]);
}

public function featured()
{
    $products = Product::where('is_featured', true)
        ->with(['category', 'images', 'variants'])
        ->latest()
        ->get();

    // Add full URL to all images
    $products->transform(function ($product) {
        if ($product->main_image) {
            $product->main_image = $this->getFullImageUrl($product->main_image);
        }

        if ($product->images) {
            $product->images->transform(function ($image) {
                $image->image_path = $this->getFullImageUrl($image->image_path);
                return $image;
            });
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
        'status' => true,
        'data' => $products
    ]);
}

public function offer()
{
    $products = Product::where('is_offer', true)
        ->with(['category', 'images', 'variants'])
        ->latest()
        ->get();

    // Add full URL to all images
    $products->transform(function ($product) {
        if ($product->main_image) {
            $product->main_image = $this->getFullImageUrl($product->main_image);
        }

        if ($product->images) {
            $product->images->transform(function ($image) {
                $image->image_path = $this->getFullImageUrl($image->image_path);
                return $image;
            });
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
        'status' => true,
        'data' => $products
    ]);
}

public function campaign()
{
    $products = Product::where('is_campaign', true)
        ->with(['category', 'images', 'variants'])
        ->latest()
        ->get();

    // Add full URL to all images
    $products->transform(function ($product) {
        if ($product->main_image) {
            $product->main_image = $this->getFullImageUrl($product->main_image);
        }

        if ($product->images) {
            $product->images->transform(function ($image) {
                $image->image_path = $this->getFullImageUrl($image->image_path);
                return $image;
            });
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
        'status' => true,
        'data' => $products
    ]);
}

}
