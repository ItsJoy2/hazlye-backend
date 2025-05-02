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
                                    <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="regular_price">Regular Price</label>
                            <input type="number" step="0.01" name="regular_price" id="regular_price" class="form-control" value="{{ old('regular_price', $product->regular_price) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="main_image">Main Product Image</label>
                            <input type="file" name="main_image" id="main_image" class="form-control-file">
                            @if($product->main_image)
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $product->main_image) }}" alt="Main Product Image" style="max-height: 100px;">
                                    <input type="hidden" name="existing_main_image" value="{{ $product->main_image }}">
                                </div>
                            @endif
                        </div>

                        <div class="form-group form-check">
                            <input type="checkbox" name="featured" id="featured" class="form-check-input" value="1" {{ $product->featured ? 'checked' : '' }}>
                            <label class="form-check-label" for="featured">Featured Product</label>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">Variants</div>
                    <div class="card-body">

                        @include('admin.pages.products.partials.__variant')

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
