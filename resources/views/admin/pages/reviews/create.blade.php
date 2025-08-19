@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Add New Review</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.reviews.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label for="name">User Name *</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="product_id">Product *</label>
                    <select class="form-control @error('product_id') is-invalid @enderror"
                            id="product_id" name="product_id" required>
                        <option value="">Select Product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}"
                                {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('product_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="rating">Rating *</label>
                    <select class="form-control @error('rating') is-invalid @enderror"
                            id="rating" name="rating" required>
                        @for($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}" {{ old('rating') == $i ? 'selected' : '' }}>
                                {{ $i }} Star{{ $i > 1 ? 's' : '' }}
                            </option>
                        @endfor
                    </select>
                    @error('rating')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description">Review *</label>
                    <textarea class="form-control @error('description') is-invalid @enderror"
                              id="description" name="description" rows="4" required>{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="images">Images (Max 5)</label>
                    <input type="file" class="form-control-file @error('images') is-invalid @enderror"
                           id="images" name="images[]" multiple accept="image/*">
                    @error('images')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">
                        Maximum file size: 2MB each. Allowed formats: jpeg, png, jpg, gif, webp
                    </small>
                </div>

                <div class="form-group form-check">
                    <input type="checkbox" class="form-check-input" id="is_approved" name="is_approved"
                           {{ old('is_approved') ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_approved">Approve this review</label>
                </div>

                <button type="submit" class="btn btn-primary">Submit Review</button>
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection