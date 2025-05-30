@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3>Create New Product</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" style="width: 680px;" id="product-form">
                @csrf

                <div class="card mb-4">
                    <div class="card-header">Basic Information</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="sku">SKU</label>
                            <input type="text" name="sku" id="sku" class="form-control @error('sku') is-invalid @enderror"
                                   value="{{ old('sku') }}">
                            @error('sku')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="name">Product Name</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                            @error('name')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                        </div>

                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                        </div>

                        <div class="form-group">
                            <label for="size_id">Product Size (Optional)</label>
                            <select name="size" id="size_id" class="form-control">
                                <option value="">-- No specific size --</option>
                                @foreach($sizes as $size)
                                    <option value="{{ $size->id }}" {{ old('size', $product->size ?? '') == $size->id ? 'selected' : '' }}>
                                        {{ $size->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Leave unselected if product doesn't have a default size</small>
                        </div>
                        <div class="form-group">
                            <label for="total_stock">Total Stock</label>
                            <input type="number" step="0.01" name="total_stock" id="total_stock" name="total_stock" id="total_stock" class="form-control @error('Purchase_price') is-invalid @enderror" value="{{ old('total_stock') }}>
                            @error('Purchase_price')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="Purchase_price">Buy Price</label>
                            <input type="number" step="0.01" name="Purchase_price" id="Purchase_price"
                                   class="form-control @error('Purchase_price') is-invalid @enderror" value="{{ old('Purchase_price') }}" >
                            @error('Purchase_price')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="regular_price">Regular Selling Price</label>
                            <input type="number" step="0.01" name="regular_price" id="regular_price" class="form-control @error('regular_price') is-invalid @enderror" value="{{ old('regular_price') }}">
                            @error('regular_price')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                        </div>
                        <div class="form-group">
                            <label for="regular_price">Discount Price</label>
                            <input type="number" step="0.01" name="discount_price" id="discount_price" class="form-control @error('discount_price') is-invalid @enderror" value="{{ old('discount_price') }}">
                            @error('discount_price')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                        </div>

                        <div class="form-group">
                            <label for="main_image">Main Product Image</label>
                            <input type="file" name="main_image" id="main_image" class="form-control-file @error('main_image') is-invalid @enderror">
                            @error('main_image')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                        </div>

                        <div class="form-group">
                            <div class="card mb-4">
                                <div class="card-header">Gallery Images</div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Gallery Images *</label>
                                        <input type="file" id="gallery_images" name="gallery_images[]" multiple style="visibility: hidden;">
                                        <button type="button" id="add-more-images" class="btn btn-secondary">
                                            <i class="fas fa-plus"></i> Add Images
                                        </button>
                                        <div class="row mt-2" id="gallery-preview"></div>
                                        @error('gallery_images')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Image Preview Container -->
                                    <div class="row mt-3" id="gallery-preview">
                                        @if(isset($product) && $product->galleryImages->count() > 0)
                                            @foreach($product->galleryImages as $image)
                                                <div class="col-md-3 mb-3 existing-image" data-id="{{ $image->id }}">
                                                    <div class="position-relative">
                                                        <img src="{{ asset('storage/' . $image->image_path) }}"
                                                             class="img-thumbnail" style="height: 150px; width: 100%; object-fit: cover;">
                                                        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 remove-image"
                                                                onclick="removeExistingImage(this, {{ $image->id }})">
                                                            ×
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    @error('gallery_images')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
                                </div>
                            </div>

                            <input type="hidden" name="existing_images" id="existing_images" value="">
                            <input type="hidden" name="removed_images" id="removed_images" value="">
                        </div>

                        <div class="d-flex">
                            <div class="form-group form-check">
                                <input type="checkbox" name="featured" id="featured" class="form-check-input" value="1" {{ old('featured') ? 'checked' : '' }}>
                                <label class="form-check-label" for="featured">Featured Product</label>
                            </div>
                            <div class="form-group form-check">
                                <input type="checkbox" name="Offer" id="offer" class="form-check-input" value="1" {{ old('offer') ? 'checked' : '' }}>
                                <label class="form-check-label" for="featured">Offer Product</label>
                            </div>
                            <div class="form-group form-check">
                                <input type="checkbox" name="campaign" id="campaign" class="form-check-input" value="1" {{ old('campaign') ? 'checked' : '' }}>
                                <label class="form-check-label" for="campaign">Campaign Product</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rest of your form remains the same -->
                <div class="card mb-4">
                    <div class="card-header">Variants (Optional)</div> <!-- Changed header to indicate optional -->
                    <div class="card-body">
                        <div id="variants-container">
                            <!-- No default variant - empty container -->
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

