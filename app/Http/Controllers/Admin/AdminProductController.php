<?php

namespace App\Http\Controllers\Admin;

use App\Models\Size;
use App\Models\Color;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\ProductVariantOption;
use Illuminate\Support\Facades\Storage;

class AdminProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->latest()->paginate(20);
        return view('admin.pages.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        $colors = Color::all();
        $sizes = Size::all();
        return view('admin.pages.products.create', compact('categories', 'colors', 'sizes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku' => 'required|unique:products,sku',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:products,slug|max:255',
            'short_description' => 'nullable|string',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'size_id' => 'nullable|exists:sizes,id',
            'total_stock' => 'required|integer|min:0',
            'Purchase_price' => 'required|numeric|min:0',
            'regular_price' => 'required|numeric|min:0|gt:Purchase_price',
            'discount_price' => 'nullable|numeric|min:0|lt:regular_price',
            'main_image' => 'required|image|max:6144',
            'gallery_images' => 'required|array|min:1',
            'gallery_images.*' => 'image|mimes:jpeg,png,jpg|max:6144',
            'featured' => 'boolean',
            'Offer' => 'boolean',
            'campaign' => 'boolean',
            'status' => 'boolean',
            'keyword_tags' => 'nullable|string',
            'keyword_tags.*' => 'string|max:255',
            'variants' => 'sometimes|array',
            'variants.*.color_id' => 'required_with:variants|exists:colors,id',
            'variants.*.image' => 'required_with:variants|image|max:6144',
            'variants.*.options' => 'required_with:variants|array',
            'variants.*.options.*.size_id' => 'required_with:variants.*.options|exists:sizes,id',
            'variants.*.options.*.price' => 'required_with:variants.*.options|numeric|min:0',
            'variants.*.options.*.stock' => 'required_with:variants.*.options|integer|min:0',
            'variants.*.options.*.sku' => 'required_with:variants.*.options|string',
        ]);

        try {
            DB::beginTransaction();

            $mainImagePath = $request->file('main_image')->store('products', 'public');

            $keywordTags = $request->keyword_tags
                ? explode(',', $request->keyword_tags)
                : null;

            $slug = $request->slug ?: Str::slug($validated['name']);
            $product = Product::create([
                'sku' => $validated['sku'],
                'name' => $validated['name'],
                'slug' => $slug,
                'short_description' => $validated['short_description'],
                'description' => $validated['description'],
                'category_id' => $validated['category_id'],
                'size_id' => $validated['size_id'] ?? null,
                'total_stock' => $validated['total_stock'],
                'buy_price' => $validated['Purchase_price'],
                'regular_price' => $validated['regular_price'],
                'discount_price' => $validated['discount_price'] ?? null,
                'main_image' => $mainImagePath,
                'is_featured' => $request->has('featured'),
                'is_offer' => $request->has('Offer'),
                'is_campaign' => $request->has('campaign'),
                'status' => $request->has('status'),
                'keyword_tags' => $keywordTags,
            ]);

            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $index => $file) {
                    $path = $file->store('products/gallery', 'public');

                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $path,
                        'sort_order' => $index
                    ]);
                }
            }

            if ($request->has('variants')) {
                foreach ($request->variants as $variantData) {
                    $variantImagePath = $variantData['image']->store('products/variants', 'public');

                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'color_id' => $variantData['color_id'],
                        'image' => $variantImagePath,
                    ]);

                    foreach ($variantData['options'] as $optionData) {
                        ProductVariantOption::create([
                            'variant_id' => $variant->id,
                            'size_id' => $optionData['size_id'],
                            'price' => $optionData['price'],
                            'stock' => $optionData['stock'],
                            'sku' => $optionData['sku'],
                        ]);
                    }
                }

                $product->update(['total_stock' => $product->variants->sum(function($v) {
                    return $v->options->sum('stock');
                })]);
            }

            DB::commit();

            return redirect()->route('admin.products.index')
                ->with('success', 'Product created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product creation failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error creating product: ' . $e->getMessage());
        }
    }


public function show(Product $product)
{

    $product->load([
        'category',
        'images',
        'variants' => function($query) {
            $query->with(['color', 'options' => function($q) {
                $q->with('size')->orderBy('price');
            }]);
        }
    ]);


    $inventorySummary = [
        'total_variants' => $product->variants->count(),
        'total_options' => $product->variants->sum(fn($v) => $v->options->count()),
        'total_stock' => $product->variants->sum(fn($v) => $v->options->sum('stock')),
        'out_of_stock' => $product->variants->sum(fn($v) => $v->options->where('stock', '<=', 0)->count())
    ];

    return view('admin.pages.products.show', compact('product', 'inventorySummary'));
}

    public function edit(Product $product)
    {
        $categories = Category::all();
        $colors = Color::all();
        $sizes = Size::all();
        $product->load(['variants.color', 'variants.options.size']);

        return view('admin.pages.products.edit', compact('product', 'categories', 'colors', 'sizes'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sku' => 'required|unique:products,sku,' . $product->id,
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:products,slug,'.$product->id.'|max:255',
            'short_description' => 'nullable|string',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'size_id' => 'nullable|exists:sizes,id',
            'total_stock' => 'required|integer|min:0',
            'Purchase_price' => 'required|numeric|min:0',
            'regular_price' => 'required|numeric|min:0|gt:Purchase_price',
            'discount_price' => 'nullable|numeric|min:0|lt:regular_price',
            'main_image' => 'sometimes|image|max:6144',
            'gallery_images' => 'sometimes|array|min:1',
            'gallery_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:6144',
            'featured' => 'boolean',
            'Offer' => 'boolean',
            'campaign' => 'boolean',
            'status' => 'boolean',
            'keyword_tags' => 'nullable|string',
            'keyword_tags.*' => 'string|max:255',
            'variants' => 'sometimes|array',
            'variants.*.id' => 'sometimes|exists:product_variants,id',
            'variants.*.color_id' => 'required_with:variants|exists:colors,id',
            'variants.*.image' => 'sometimes|image|max:2048',
            'variants.*.options' => 'required_with:variants|array',
            'variants.*.options.*.id' => 'sometimes|exists:product_variant_options,id',
            'variants.*.options.*.size_id' => 'required_with:variants.*.options|exists:sizes,id',
            'variants.*.options.*.price' => 'required_with:variants.*.options|numeric|min:0',
            'variants.*.options.*.stock' => 'required_with:variants.*.options|integer|min:0',
            'variants.*.options.*.sku' => 'required_with:variants.*.options|string',
            'deleted_variants' => 'sometimes|array',
            'deleted_variants.*' => 'exists:product_variants,id',
            'deleted_gallery_images' => 'sometimes|array',
            'deleted_gallery_images.*' => 'exists:product_images,id',
        ]);

        try {
            DB::beginTransaction();

            // Handle main image upload if new one is provided
            $mainImagePath = $request->hasFile('main_image')
                ? $request->file('main_image')->store('products', 'public')
                : $product->main_image;


                $keywordTags = $request->filled('keyword_tags') ? array_map('trim', explode(',', $request->keyword_tags)): [];
                $slug = $request->slug ?: Str::slug($validated['name']);
            // Update product
            $product->update([
                'sku' => $validated['sku'],
                'name' => $validated['name'],
                'slug' => $slug,
                'short_description' => $validated['short_description'],
                'description' => $validated['description'],
                'category_id' => $validated['category_id'],
                'size_id' => $validated['size_id'] ?? null,
                'total_stock' => $validated['total_stock'],
                'buy_price' => $validated['Purchase_price'],
                'regular_price' => $validated['regular_price'],
                'discount_price' => $validated['discount_price'] ?? null,
                'main_image' => $mainImagePath,
                'is_featured' => $request->has('featured'),
                'is_offer' => $request->has('Offer'),
                'is_campaign' => $request->has('campaign'),
                'status' => $request->has('status'),
                'keyword_tags' => $keywordTags,
            ]);

            // Handle gallery images - delete marked images first
            if ($request->has('deleted_gallery_images')) {
                ProductImage::whereIn('id', $request->deleted_gallery_images)
                    ->where('product_id', $product->id)
                    ->delete();
            }

            // Add new gallery images
            if ($request->hasFile('gallery_images')) {
                $existingImagesCount = $product->images()->count();

                foreach ($request->file('gallery_images') as $index => $file) {
                    $path = $file->store('products/gallery', 'public');

                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $path,
                        'sort_order' => $existingImagesCount + $index
                    ]);
                }
            }

            // Handle deleted variants
            if ($request->has('deleted_variants')) {
                ProductVariant::whereIn('id', $request->deleted_variants)
                    ->where('product_id', $product->id)
                    ->delete();
            }

            // Handle variants if exists
            if ($request->has('variants')) {
                foreach ($request->variants as $variantData) {
                    // Update existing variant or create new one
                    if (isset($variantData['id'])) {
                        $variant = ProductVariant::find($variantData['id']);

                        // Update variant image if new one is provided
                        $variantImagePath = isset($variantData['image'])
                            ? $variantData['image']->store('products/variants', 'public')
                            : $variant->image;

                        $variant->update([
                            'color_id' => $variantData['color_id'],
                            'image' => $variantImagePath,
                        ]);
                    } else {
                        $variantImagePath = $variantData['image']->store('products/variants', 'public');

                        $variant = ProductVariant::create([
                            'product_id' => $product->id,
                            'color_id' => $variantData['color_id'],
                            'image' => $variantImagePath,
                        ]);
                    }

                    // Handle variant options
                    foreach ($variantData['options'] as $optionData) {
                        if (isset($optionData['id'])) {
                            // Update existing option
                            ProductVariantOption::where('id', $optionData['id'])
                                ->where('variant_id', $variant->id)
                                ->update([
                                    'size_id' => $optionData['size_id'],
                                    'price' => $optionData['price'],
                                    'stock' => $optionData['stock'],
                                    'sku' => $optionData['sku'],
                                ]);
                        } else {
                            // Create new option
                            ProductVariantOption::create([
                                'variant_id' => $variant->id,
                                'size_id' => $optionData['size_id'],
                                'price' => $optionData['price'],
                                'stock' => $optionData['stock'],
                                'sku' => $optionData['sku'],
                            ]);
                        }
                    }
                }

                // Update total stock based on variants
                $product->update(['total_stock' => $product->variants->sum(function($v) {
                    return $v->options->sum('stock');
                })]);
            }

            DB::commit();

            return redirect()->route('admin.products.index')
                ->with('success', 'Product updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product update failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error updating product: ' . $e->getMessage());
        }
    }

    public function destroy(Product $product)
    {
        // Delete main image
        Storage::disk('public')->delete($product->main_image);

        // Delete gallery images
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }

        // Delete variant images
        foreach ($product->variants as $variant) {
            if ($variant->image) {
                Storage::disk('public')->delete($variant->image);
            }
            $variant->delete();
        }

        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully');
    }

    public function deleteImage(ProductImage $image)
    {
        Storage::disk('public')->delete($image->image_path);
        $image->delete();
        return back()->with('success', 'Image deleted successfully');
    }


    public function variant(Request $request)
    {
        try {
            $variantIndex = (int)$request->index;
            $colors = Color::all();
            $sizes = Size::all();

            $html = view('admin.pages.products.partials.__variant', [
                'variantIndex' => $variantIndex,
                'colors' => $colors,
                'sizes' => $sizes,
                'variant' => null // Important for create form
            ])->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);

        } catch (\Exception $e) {
            Log::error('Variant generation failed: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate variant form'
            ], 500);
        }
    }

    public function option(Request $request)
    {
        $variantIndex = (int)$request->variant;
        $optionIndex = (int)$request->index;
        $sizes = Size::all();

        $html = view('admin.pages.products.partials.__option', [
            'variantIndex' => $variantIndex,
            'optionIndex' => $optionIndex,
            'sizes' => $sizes
        ])->render();

        return response()->json(['html' => $html]);
    }
    protected function processGalleryImages($images, $product)
    {
        foreach ($images as $image) {
            // Generate unique filename
            $filename = 'gallery_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

            // Store image
            $path = $image->storeAs('public/products/gallery', $filename);

            // Save to database - make sure this is using the correct field names
            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => str_replace('public/', '', $path), // This should match your database column
                'sort_order' => 0
            ]);
        }

    }
}