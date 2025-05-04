@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3>Product Details: {{ $product->name }}</h3>
            <div>
                <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-primary mr-2">
                    <i class="fas fa-edit"></i> Edit Product
                </a>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Basic Information Column -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Basic Information</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-hover">
                                <tbody>
                                    <tr>
                                        <th width="30%">Product ID</th>
                                        <td>{{ $product->productId }}</td>
                                    </tr>
                                    <tr>
                                        <th>Name</th>
                                        <td>{{ $product->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Slug</th>
                                        <td>{{ $product->slug }}</td>
                                    </tr>
                                    <tr>
                                        <th>Description</th>
                                        <td>{!! nl2br(e($product->description)) !!}</td>
                                    </tr>
                                    <tr>
                                        <th>Category</th>
                                        <td>{{ $product->category->name ?? 'No category assigned' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Regular Price</th>
                                        <td>{{ number_format($product->regular_price, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Featured</th>
                                        <td>
                                            <span class="badge badge-{{ $product->featured ? 'success' : 'secondary' }}">
                                                {{ $product->featured ? 'Yes' : 'No' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Created At</th>
                                        <td>{{ $product->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Updated At</th>
                                        <td>{{ $product->updated_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Main Images Column -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Product Images</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Required Main Image -->
                                <div class="col-md-6 mb-3">
                                    <h6 class="text-center">Main Image (Required)</h6>
                                    @if($product->main_image)
                                        <img src="{{ asset('storage/' . $product->main_image) }}"
                                             alt="Main Product Image"
                                             class="img-fluid rounded border d-block mx-auto"
                                             style="max-height: 200px;">
                                    @else
                                        <div class="alert alert-warning p-2 text-center">
                                            <i class="fas fa-exclamation-circle"></i> No main image uploaded
                                        </div>
                                    @endif
                                </div>

                                <!-- Optional Secondary Image -->
                                <div class="col-md-6 mb-3">
                                    <h6 class="text-center">Secondary Image (Optional)</h6>
                                    @if($product->main_image_2)
                                        <img src="{{ asset('storage/' . $product->main_image_2) }}"
                                             alt="Secondary Product Image"
                                             class="img-fluid rounded border d-block mx-auto"
                                             style="max-height: 200px;">
                                    @else
                                        <div class="alert alert-info p-2 text-center">
                                            <i class="fas fa-info-circle"></i> No secondary image uploaded
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rest of your existing code for variants and inventory summary -->
            <!-- Variants Section -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Product Variants</h5>
                </div>
                <div class="card-body">
                    @if($product->variants->count() > 0)
                        <div class="row">
                            @foreach($product->variants as $variant)
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0">
                                                <i class="fas fa-palette"></i>
                                                Color: {{ $variant->color->name ?? 'N/A' }}
                                            </h6>
                                            <span class="badge badge-primary">
                                                {{ $variant->options->count() }} Options
                                            </span>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <!-- Variant Image -->
                                                <div class="col-md-4 mb-3 mb-md-0">
                                                    @if($variant->image)
                                                        <img src="{{ asset('storage/' . $variant->image) }}"
                                                             alt="Variant Image"
                                                             class="img-fluid rounded border"
                                                             style="max-height: 150px;">
                                                    @else
                                                        <div class="alert alert-warning p-2">
                                                            <i class="fas fa-exclamation-circle"></i> No image
                                                        </div>
                                                    @endif
                                                </div>

                                                <!-- Variant Options -->
                                                <div class="col-md-8">
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-bordered table-hover">
                                                            <thead class="bg-light">
                                                                <tr>
                                                                    <th>Size</th>
                                                                    <th>Price</th>
                                                                    <th>Stock</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @forelse($variant->options as $option)
                                                                    <tr class="{{ $option->stock <= 0 ? 'table-danger' : '' }}">
                                                                        <td>{{ $option->size->name ?? 'N/A' }}</td>
                                                                        <td>{{ number_format($option->price, 2) }}</td>
                                                                        <td>
                                                                            <span class="{{ $option->stock <= 0 ? 'text-danger' : '' }}">
                                                                                {{ $option->stock }}
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                @empty
                                                                    <tr>
                                                                        <td colspan="3" class="text-center text-muted">
                                                                            No options available
                                                                        </td>
                                                                    </tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-transparent">
                                            <small class="text-muted">
                                                Last updated: {{ $variant->updated_at->format('M d, Y H:i') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No variants available for this product
                        </div>
                    @endif
                </div>
            </div>

            <!-- Inventory Summary -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Inventory Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-bordered">
                                <tr>
                                    <th>Total Variants</th>
                                    <td>{{ $product->variants->count() }}</td>
                                </tr>
                                <tr>
                                    <th>Total Options</th>
                                    <td>{{ $product->variants->sum(function($variant) { return $variant->options->count(); }) }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-bordered">
                                <tr>
                                    <th>Total Stock</th>
                                    <td>{{ $product->variants->sum(function($variant) { return $variant->options->sum('stock'); }) }}</td>
                                </tr>
                                <tr>
                                    <th>Out of Stock Options</th>
                                    <td>{{ $product->variants->sum(function($variant) { return $variant->options->where('stock', '<=', 0)->count(); }) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<style>
    .card-header {
        font-weight: 600;
        background-color: #f8f9fa !important;
    }
    th {
        background-color: #f8f9fa;
    }
    .img-thumbnail {
        max-width: 100%;
        height: auto;
        transition: transform 0.3s;
    }
    .img-thumbnail:hover {
        transform: scale(1.05);
    }
    .badge {
        font-size: 0.85rem;
        padding: 0.35em 0.65em;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.03);
    }
</style>