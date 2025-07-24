@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">All Orders</h3>
                <button class="btn btn-sm btn-secondary text-white"><a href="{{ route('admin.orders.create') }}" class=" text-white flex-grow-1">Create New Order</a></button>
            </div>
        </div>
        <div class="card-body">
            @include('admin.layouts.partials.__alerts')

            <div class="mb-0">
                <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-3 mb-4">
                    {{-- Date Filters (Always Visible) --}}
                    <div class="col-md-2">
                        <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                    </div>

                    {{-- Extra Filters: Only visible when status == delivered --}}
                    @if($status === 'delivered')
                        <div class="col-md-2">
                            <input type="text" name="district" class="form-control" placeholder="District" value="{{ $district }}">
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="thana" class="form-control" placeholder="Thana" value="{{ $thana }}">
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="product_search" class="form-control" placeholder="Product Name / SKU" value="{{ $productSearch }}">
                        </div>
                    @endif

                    {{-- Status Dropdown --}}
                    <div class="col-md-2">
                        <select name="status" class="form-control">
                            <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All Status</option>
                            <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="processing" {{ $status == 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="shipped" {{ $status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="delivered" {{ $status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>


            <div class="table-responsive">
                <table class="table table-striped table-hover table-head-bg-primary mt-4"  width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $index => $order)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $order->name }}</td>
                            <td>{{ $order->phone }}</td>
                            <td>{{ $order->created_at->format('M d, Y') }}</td>
                            <td>${{ number_format($order->total, 2) }}</td>
                            <td>
                                <span class="badge badge-{{ $order->status == 'delivered' ? 'success' : ($order->status == 'cancelled' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td>
                                @include('admin.pages.orders.partials.__actions')
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No Order found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer clearfix">
            <div class="mt-4">
                {{ $orders->appends([
                    'status' => $status,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo
                ])->links('admin.layouts.partials.__pagination') }}
            </div>
        </div>
    </div>
</div>
@endsection

<style>
    .color-preview {
        display: inline-block;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: 1px solid #ddd;
    }
    .container form{
        width: 100% !important;
        background: none !important;
        border: none !important;
        margin-bottom: 0 !important;
    }
        @media (max-width: 576px) {
        .form-label {
            font-size: 0.75rem;
        }
        .btn-sm i {
            display: none;
        }
    }

    .align-items-end {
        align-items: flex-end;
    }
</style>