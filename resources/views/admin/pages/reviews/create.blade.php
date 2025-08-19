@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Create New Review</h4>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('admin.reviews.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group row">
                            <label for="product_id" class="col-md-3 col-form-label text-md-right">Product</label>
                            <div class="col-md-6">
                                <select class="form-control @error('product_id') is-invalid @enderror" id="product_id" name="product_id" required>
                                    <option value="">Select Product</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('product_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="name" class="col-md-3 col-form-label text-md-right">User Name</label>
                            <div class="col-md-6">
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="rating" class="col-md-3 col-form-label text-md-right">Rating</label>
                            <div class="col-md-6">
                                <select class="form-control @error('rating') is-invalid @enderror" id="rating" name="rating" required>
                                    <option value="1" {{ old('rating') == 1 ? 'selected' : '' }}>1 Star</option>
                                    <option value="2" {{ old('rating') == 2 ? 'selected' : '' }}>2 Stars</option>
                                    <option value="3" {{ old('rating') == 3 ? 'selected' : '' }}>3 Stars</option>
                                    <option value="4" {{ old('rating') == 4 ? 'selected' : '' }}>4 Stars</option>
                                    <option value="5" {{ old('rating', 5) == 5 ? 'selected' : '' }}>5 Stars</option>
                                </select>
                                @error('rating')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="description" class="col-md-3 col-form-label text-md-right">Review Description</label>
                            <div class="col-md-6">
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5" required>{{ old('description') }}</textarea>
                                @error('description')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6 offset-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_approved" name="is_approved" value="1" {{ old('is_approved', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_approved">
                                        Approve this review immediately
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="images" class="col-md-3 col-form-label text-md-right">Review Images</label>
                            <div class="col-md-6">
                                <input type="file" class="form-control-file @error('images.*') is-invalid @enderror" id="images" name="images[]" multiple accept="image/*">
                                <small class="form-text text-muted">
                                    You can upload up to 5 images (JPEG, PNG, JPG, GIF, WEBP) with max 2MB each
                                </small>
                                @error('images.*')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Submit Review
                                </button>
                                <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection