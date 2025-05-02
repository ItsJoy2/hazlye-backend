<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantOption;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AdminProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'variants'])->latest()->paginate(10);
        return view('admin.pages.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        $colors = Color::all();
        $sizes = Size::all();
        return view('admin.pages.products.create', compact('categories', 'colors', 'sizes'));
    }

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //         'description' => 'required|string',
    //         'regular_price' => 'required|numeric|min:0',
    //         'category_id' => 'required|exists:categories,id',
    //         'main_image' => 'required|image',
    //         'variants' => 'required|array|min:1',
    //         'variants.*.color_id' => 'required|exists:colors,id',
    //         'variants.*.image' => 'required|image',
    //         'variants.*.options' => 'required|array|min:1',
    //         'variants.*.options.*.size_id' => 'required|exists:sizes,id',
    //         'variants.*.options.*.price' => 'required|numeric|min:0',
    //         'variants.*.options.*.stock' => 'required|integer|min:0',
    //     ]);

    //     \DB::beginTransaction();

    //     try {
    //         // Handle main image upload
    //         $mainImagePath = $request->file('main_image')->store('products', 'public');



    //         // Create product
    //         $product = Product::create([
    //             'productId' => $request->productId ?? Str::random(8),
    //             'name' => $request->name,
    //             'slug' => Str::slug($request->name),
    //             'description' => $request->description,
    //             'regular_price' => $request->regular_price,
    //             'category_id' => $request->category_id,
    //             'main_image' => $request->file('main_image')->store('products', 'public'),
    //             'featured' => $request->featured ?? false,
    //         ]);

    //         // Then create variants
    //     foreach ($request->variants as $variantData) {
    //         $variant = ProductVariant::create([
    //             'product_id' => $product->id,
    //             'color_id' => $variantData['color_id'],
    //             'image' => $variantData['image']->store('products', 'public'),
    //         ]);

    //         // Then create options
    //         foreach ($variantData['options'] as $optionData) {
    //             ProductVariantOption::create([
    //                 'variant_id' => $variant->id,
    //                 'size_id' => $optionData['size_id'],
    //                 'price' => $optionData['price'],
    //                 'stock' => $optionData['stock'],
    //             ]);
    //         }
    //     }

    //     \DB::commit();
    //     return redirect()->route('admin.products.index')->with('success', 'Product created successfully');

    //     } catch (\Exception $e) {
    //         \DB::rollBack();
    //         return back()->withInput()->with('error', 'Error creating product: ' . $e->getMessage());
    //     }
    // }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'regular_price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'main_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'variants' => 'required|array|min:1',
            'variants.*.color_id' => 'required|exists:colors,id',
            'variants.*.image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'variants.*.options' => 'required|array|min:1',
            'variants.*.options.*.size_id' => 'required|exists:sizes,id',
            'variants.*.options.*.price' => 'required|numeric|min:0',
            'variants.*.options.*.stock' => 'required|integer|min:0',
        ]);

        \DB::beginTransaction();

        try {
            // Handle main image upload
            $mainImagePath = $request->file('main_image')->store('products', 'public');

            // Create product
            $product = Product::create([
                'productId' => Str::random(8),
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'regular_price' => $request->regular_price,
                'category_id' => $request->category_id,
                'main_image' => $mainImagePath,
                'featured' => $request->featured ?? false,
            ]);

            // Create variants
            foreach ($request->variants as $variantData) {
                // Handle variant image upload
                $variantImagePath = $variantData['image']->store('products', 'public');

                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'color_id' => $variantData['color_id'],
                    'image' => $variantImagePath,
                ]);

                // Create options
                foreach ($variantData['options'] as $optionData) {
                    ProductVariantOption::create([
                        'variant_id' => $variant->id,
                        'size_id' => $optionData['size_id'],
                        'price' => $optionData['price'],
                        'stock' => $optionData['stock'],
                    ]);
                }
            }

            \DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Product created successfully');

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Product creation failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error creating product: ' . $e->getMessage());
        }
    }



    public function edit(Product $product)
    {
        $product->load(['variants.options', 'variants.color']);
        $categories = Category::all();
        $colors = Color::all();
        $sizes = Size::all();
        return view('admin.pages.products.edit', compact('product', 'categories', 'colors', 'sizes'));
    }

    public function update(Request $request, Product $product)
    {
        // Add validation for existing images when new ones aren't provided
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'regular_price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'main_image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048', // Changed to 'sometimes'
            'variants' => 'required|array|min:1',
            'variants.*.color_id' => 'required|exists:colors,id',
            'variants.*.image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048', // Changed to 'sometimes'
            'variants.*.options' => 'required|array|min:1',
            'variants.*.options.*.size_id' => 'required|exists:sizes,id',
            'variants.*.options.*.price' => 'required|numeric|min:0',
            'variants.*.options.*.stock' => 'required|integer|min:0',
        ]);

        \DB::beginTransaction();

        try {
            // Update product basic info
            $updateData = [
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'regular_price' => $request->regular_price, // Changed from basePrice to regular_price
                'category_id' => $request->category_id,
                'featured' => $request->featured ?? false,
            ];

            // Handle main image update if provided
            if ($request->hasFile('main_image')) {
                // Delete old image if exists
                if ($product->main_image) {
                    Storage::disk('public')->delete($product->main_image);
                }
                $updateData['main_image'] = $request->file('main_image')->store('products', 'public');
            }

            $product->update($updateData);

            // Handle variants update
            $existingVariantIds = $product->variants->pluck('id')->toArray();
            $updatedVariantIds = [];

            foreach ($request->variants as $variantData) {
                $variantData = (object) $variantData;

                if (isset($variantData->id)) {
                    // Update existing variant
                    $variant = ProductVariant::find($variantData->id);
                    if ($variant) {
                        $updatedVariantIds[] = $variant->id;

                        $updateVariantData = [
                            'color_id' => $variantData->color_id,
                        ];

                        // Handle variant image update if provided
                        if (isset($variantData->image)) {
                            // Delete old image if exists
                            if ($variant->image) {
                                Storage::disk('public')->delete($variant->image);
                            }
                            $updateVariantData['image'] = $variantData->image->store('products', 'public');
                        } elseif (isset($variantData->existing_image)) {
                            $updateVariantData['image'] = $variantData->existing_image;
                        }

                        $variant->update($updateVariantData);

                        // Handle options update
                        $existingOptionIds = $variant->options->pluck('id')->toArray();
                        $updatedOptionIds = [];

                        foreach ($variantData->options as $optionData) {
                            $optionData = (object) $optionData;

                            if (isset($optionData->id)) {
                                // Update existing option
                                $option = ProductVariantOption::find($optionData->id);
                                if ($option) {
                                    $updatedOptionIds[] = $option->id;
                                    $option->update([
                                        'size_id' => $optionData->size_id,
                                        'price' => $optionData->price,
                                        'stock' => $optionData->stock,
                                    ]);
                                }
                            } else {
                                // Create new option
                                $option = ProductVariantOption::create([
                                    'variant_id' => $variant->id,
                                    'size_id' => $optionData->size_id,
                                    'price' => $optionData->price,
                                    'stock' => $optionData->stock,
                                ]);
                                $updatedOptionIds[] = $option->id;
                            }
                        }

                        // Delete options that were removed
                        ProductVariantOption::where('variant_id', $variant->id)
                            ->whereNotIn('id', $updatedOptionIds)
                            ->delete();
                    }
                } else {
                    // Create new variant
                    $imagePath = $variantData->image->store('products', 'public');

                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'color_id' => $variantData->color_id,
                        'image' => $imagePath,
                    ]);

                    $updatedVariantIds[] = $variant->id;

                    // Create options for new variant
                    foreach ($variantData->options as $optionData) {
                        $optionData = (object) $optionData;
                        ProductVariantOption::create([
                            'variant_id' => $variant->id,
                            'size_id' => $optionData->size_id,
                            'price' => $optionData->price,
                            'stock' => $optionData->stock,
                        ]);
                    }
                }
            }

            // Delete variants that were removed
            $variantsToDelete = array_diff($existingVariantIds, $updatedVariantIds);
            if (!empty($variantsToDelete)) {
                $variants = ProductVariant::whereIn('id', $variantsToDelete)->get();
                foreach ($variants as $variant) {
                    // Delete variant image if exists
                    if ($variant->image) {
                        Storage::disk('public')->delete($variant->image);
                    }
                    $variant->delete();
                }
            }

            \DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Product updated successfully');

        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->withInput()->with('error', 'Error updating product: ' . $e->getMessage());
        }
    }

    public function destroy(Product $product)
    {
        // Delete all variant images first
        foreach ($product->variants as $variant) {
            Storage::disk('public')->delete($variant->image);
        }

        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully');
    }

    public function variant(Request $request)
    {
        $variantIndex = (int)$request->index;
        $colors = Color::all();
        $sizes = Size::all();

        $html = view('admin.pages.products.partials.__variant', [
            'variantIndex' => $variantIndex,
            'colors' => $colors,
            'sizes' => $sizes
        ])->render();

        return response()->json(['html' => $html]);
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

    public function show(Product $product)
{
    // Eager load all necessary relationships
    $product->load([
        'category',
        'variants.color',
        'variants.options.size',
        'variants' => function($query) {
            $query->withCount('options');
        }
    ]);

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

    return view('admin.pages.products.show', [
        'product' => $product,
        'inventorySummary' => $inventorySummary
    ]);
}

}
