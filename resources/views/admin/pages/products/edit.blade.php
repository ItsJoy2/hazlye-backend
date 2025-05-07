@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3>Edit Product</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" style="width: 600px;">
                @csrf
                @method('PUT')

                <div class="card mb-4">
                    <div class="card-header">Basic Information</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Product Name</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="3" required>{{ old('description', $product->description) }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select name="category_id" id="category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @if($product->category_id == $category->id) selected @endif>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="Purchase_price">Buy Price</label>
                            <input type="number" step="0.01" name="Purchase_price" id="Purchase_price"
                                   class="form-control" value="{{ old('Purchase_price', $product->Purchase_price) }}" required>
                            @error('Purchase_price')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="regular_price">Regular Sell Price</label>
                            <input type="number" step="0.01" name="regular_price" id="regular_price" class="form-control" value="{{ old('regular_price', $product->regular_price) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="main_image">Main Product Image</label>
                            <input type="file" name="main_image" id="main_image" class="form-control-file">
                            @if($product->main_image)
                                <div class="mt-2">
                                    <img src="{{ asset('storage/'.$product->main_image) }}" width="100">
                                    <input type="hidden" name="existing_main_image" value="{{ $product->main_image }}">
                                </div>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="main_image_2">Secondary Product Image (Optional)</label>
                            <input type="file" name="main_image_2" id="main_image_2" class="form-control-file">
                            @if(isset($product) && $product->main_image_2)
                                <div class="mt-2">
                                    <img src="{{ asset('storage/'.$product->main_image_2) }}" width="100">
                                    <input type="hidden" name="existing_main_image_2" value="{{ $product->main_image_2 }}">
                                </div>
                            @endif
                        </div>


                        <div class="form-group form-check">
                            <input type="checkbox" name="featured" id="featured" class="form-check-input" value="1" {{ old('featured', $product->featured) ? 'checked' : '' }}>
                            <label class="form-check-label" for="featured">Featured Product</label>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">Variants</div>
                    <div class="card-body">
                        <div id="variants-container">
                            @foreach($product->variants as $index => $variant)
                                <div class="variant-card card mb-3">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <span>Variant #{{ $index + 1 }}</span>
                                        <button type="button" class="btn btn-sm btn-danger remove-variant">Remove</button>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label>Color</label>
                                            <select name="variants[{{ $index }}][color_id]" class="form-control" required>
                                                <option value="">Select Color</option>
                                                @foreach($colors as $color)
                                                    <option value="{{ $color->id }}" @if($variant->color_id == $color->id) selected @endif>
                                                        {{ $color->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Image</label>
                                            <input type="file" name="variants[{{ $index }}][image]" class="form-control-file">
                                            @if($variant->image)
                                                <div class="mt-2">
                                                    <img src="{{ asset('storage/' . $variant->image) }}" width="50">
                                                    <input type="hidden" name="variants[{{ $index }}][existing_image]" value="{{ $variant->image }}">
                                                </div>
                                            @endif
                                        </div>

                                        <div class="options-container">
                                            <label>Options</label>
                                            <div id="options-container-{{ $index }}" class="mb-3">
                                                @foreach($variant->options as $optionIndex => $option)
                                                    <div class="option-row row mb-2">
                                                        <div class="col-md-3">
                                                            <select name="variants[{{ $index }}][options][{{ $optionIndex }}][size_id]" class="form-control" required>
                                                                <option value="">Select Size</option>
                                                                @foreach($sizes as $size)
                                                                    <option value="{{ $size->id }}" @if($option->size_id == $size->id) selected @endif>
                                                                        {{ $size->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="number" step="0.01" name="variants[{{ $index }}][options][{{ $optionIndex }}][price]"
                                                                   class="form-control" placeholder="Price" required
                                                                   value="{{ old("variants.$index.options.$optionIndex.price", $option->price) }}">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="number" name="variants[{{ $index }}][options][{{ $optionIndex }}][stock]"
                                                                   class="form-control" placeholder="Stock" required
                                                                   value="{{ old("variants.$index.options.$optionIndex.stock", $option->stock) }}">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="hidden" name="variants[{{ $index }}][options][{{ $optionIndex }}][id]" value="{{ $option->id }}">
                                                            <button type="button" class="btn btn-sm btn-danger remove-option">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <button type="button" class="btn btn-sm btn-secondary add-option" data-variant="{{ $index }}">
                                                Add Option
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button type="button" class="btn btn-secondary mt-3" id="add-variant">Add Variant</button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Update Product</button>
            </form>
        </div>
    </div>
</div>
@endsection

@include('admin.pages.products.partials.__additionalScript')