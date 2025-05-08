@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3>Create New Product</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" style="width: 680px;">
                @csrf

                <div class="card mb-4">
                    <div class="card-header">Basic Information</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="sku">SKU</label>
                            <input type="text" name="sku" id="sku" class="form-control"
                                   value="{{ old('sku') }}">
                            @error('sku')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="name">Product Name</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="3" required>{{ old('description') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select name="category_id" id="category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="Purchase_price">Buy Price</label>
                            <input type="number" step="0.01" name="Purchase_price" id="Purchase_price"
                                   class="form-control" value="{{ old('Purchase_price') }}" required>
                            @error('Purchase_price')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="regular_price">Regular Selling Price</label>
                            <input type="number" step="0.01" name="regular_price" id="regular_price" class="form-control" value="{{ old('regular_price') }}" required>
                        </div>

                        <div class="form-group">
                            <label for="main_image">Main Product Image</label>
                            <input type="file" name="main_image" id="main_image" class="form-control-file" required>
                        </div>
                        <div class="form-group">
                            <label for="main_image_2">Secondary Product Image (Optional)</label>
                            <input type="file" name="main_image_2" id="main_image_2" class="form-control-file">
                        </div>

                        <div class="form-group form-check">
                            <input type="checkbox" name="featured" id="featured" class="form-check-input" value="1" {{ old('featured') ? 'checked' : '' }}>
                            <label class="form-check-label" for="featured">Featured Product</label>
                        </div>
                    </div>
                </div>

                <!-- Rest of your form remains the same -->
                <div class="card mb-4">
                    <div class="card-header">Variants</div>
                    <div class="card-body">
                        <div id="variants-container">
                            <!-- Default first variant -->
                            <div class="variant-card card mb-3">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span>Variant #1</span>
                                    <button type="button" class="btn btn-sm btn-danger remove-variant">Remove</button>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Color</label>
                                        <select name="variants[0][color_id]" class="form-control" required>
                                            <option value="">Select Color</option>
                                            @foreach($colors as $color)
                                                <option value="{{ $color->id }}">{{ $color->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Image</label>
                                        <input type="file" name="variants[0][image]" class="form-control-file" required>
                                    </div>

                                    <div class="options-container">
                                        <label>Options</label>
                                        <div id="options-container-0" class="mb-3">
                                            <!-- Default first option -->
                                            <div class="option-row row mb-2">
                                                <div class="col-md-3">
                                                    <select name="variants[0][options][0][size_id]" class="form-control" required>
                                                        <option value="">Select Size</option>
                                                        @foreach($sizes as $size)
                                                            <option value="{{ $size->id }}">{{ $size->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="number" step="0.01" name="variants[0][options][0][price]"
                                                           class="form-control" placeholder="Price" required>
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="number" name="variants[0][options][0][stock]"
                                                           class="form-control" placeholder="Stock" required>
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="text" step="0.01" name="variants[0][options][0][sku]"
                                                           class="form-control" placeholder="SKU" required>
                                                </div>
                                                <div class="col-md-3 trash-btn">
                                                    <button type="button" class="btn btn-sm btn-danger remove-option">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-secondary add-option mt-3" data-variant="0">
                                            Add Option
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-secondary mt-3" id="add-variant">Add Variant</button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Save Product</button>
            </form>
        </div>
    </div>
</div>
@endsection

@include('admin.pages.products.partials.__additionalScript')

