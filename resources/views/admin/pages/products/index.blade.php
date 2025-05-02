@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Products Management</h3>
            <div class="card-tools">
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add New Product
                </a>
            </div>
        </div>
        <div class="card-body">
            @include('admin.layouts.partials.__alerts')

            <table class="table table-striped table-hover table-head-bg-primary mt-4">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Image</th>
                        <th>Regular Price</th>
                        <th>Category</th>
                        <th>Variants</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $index => $product)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $product->name }}</td>
                        <td>
                            @if($product->mainImage)
                                <img src="{{ asset('storage/'.$product->mainImage) }}" alt="{{ $product->name }}" width="50" class="img-thumbnail">
                            @else
                                <span class="text-muted">No image</span>
                            @endif
                        </td>
                        <td>{{ number_format($product->regular_price, 2) }}</td>
                        <td>{{ $product->category->name ?? 'N/A' }}</td>
                        <td>
                            @foreach($product->variants as $variant)
                                <span class="badge bg-secondary">{{ $variant->color->name }}</span>
                            @endforeach
                        </td>
                        <td>
                            @include('admin.pages.products.partials.__actions')
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $products->links('admin.layouts.partials.__pagination') }}
        </div>
    </div>
</div>
@endsection