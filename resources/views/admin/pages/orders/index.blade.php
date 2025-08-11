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
                            <option value="hold" {{ $status == 'hold' ? 'selected' : '' }}>Hold</option>
                            <option value="processing" {{ $status == 'processing' ? 'selected' : '' }}>Order Confirmed</option>
                            <option value="shipped" {{ $status == 'shipped' ? 'selected' : '' }}>Ready to Shipped</option>
                            <option value="courier_delivered" {{ $status == 'courier_delivered' ? 'selected' : '' }}>Courier Delivered</option>
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

            <!-- Export Form -->
            <form id="exportForm" action="{{ route('admin.orders.export') }}" method="POST">
                @csrf
                <div class="d-flex justify-content-between mb-3">
                    <div>
                        {{-- <button type="button" class="btn btn-sm btn-success" id="selectAllBtn">
                            <i class="fas fa-check-circle"></i> Select All
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" id="deselectAllBtn">
                            <i class="fas fa-times-circle"></i> Deselect All
                        </button> --}}
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-file-excel"></i> Export Selected to Excel
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover table-head-bg-primary mt-4" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th width="30">
                                    {{-- <input type="checkbox" id="selectAllCheckbox "> --}}
                                </th>
                                <th>Order #</th>
                                <th>Image</th>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>IP Address</th>
                                @if($status === 'delivered')
                                    <th>Track Order</th>
                                @endif
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $index => $order)
                            <tr>
                                <td>
                                    @if(in_array($order->status, ['processing', 'shipped', 'courier_delivered', 'delivered']))
                                    <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" class="order-checkbox">
                                    @else
                                    <span class="text-muted text-danger">X</span>
                                    @endif
                                </td>
                                <td>{{ $order->order_number }}</td>
                                <td>
                                    @if($order->items->isNotEmpty() && $order->items[0]->product && $order->items[0]->product->main_image)
                                        <img src="{{ asset('public/storage/'.$order->items[0]->product->main_image) }}"
                                             alt="{{ $order->items[0]->product->name }}"
                                             width="50"
                                             class="img-thumbnail">
                                    @else
                                        <span class="text-muted">No image</span>
                                    @endif
                                </td>
                                <td>{{ $order->name }}</td>
                                <td>{{ $order->phone }}</td>
                                <td>{{ $order->created_at->format('M d, Y') }}</td>
                                <td>${{ number_format($order->total, 2) }}</td>
                                <td>
                                    <span class="badge
                                        @if($order->status == 'delivered') badge-success
                                        @elseif($order->status == 'cancelled') badge-danger
                                        @elseif($order->status == 'hold') badge-info
                                        @elseif($order->status == 'incomplete') badge-secondary
                                        @elseif($order->status == 'courier_delivered') badge-primary
                                        @else badge-warning
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                    </span>
                                </td>

                                <td>{{ $order->ip_address }}</td>
                                @if($status === 'delivered')
                                    <td>
                                        @if($order->tracking_code)
                                            <a href="https://steadfast.com.bd/t/{{ $order->tracking_code }}"
                                            target="_blank"
                                            class="btn btn-sm btn-info">
                                            Track
                                            </a>
                                        @else
                                            <span class="text-muted">No tracking</span>
                                        @endif
                                    </td>
                                @endif
                                <td>
                                    @include('admin.pages.orders.partials.__actions')
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center">No Order found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
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

<script>
    $(document).ready(function() {
        // Handle export form submission
        $('#exportForm').on('submit', function(e) {
            e.preventDefault();

            const orderIds = $('.order-checkbox:checked').map(function() {
                return $(this).val();
            }).get();

            if (orderIds.length === 0) {
                alert('Please select at least one order to export');
                return false;
            }

            // Create a hidden form and submit it
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = $(this).attr('action');

            // Add CSRF token
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = $('meta[name="csrf-token"]').attr('content');
            form.appendChild(csrfToken);

            // Add order IDs
            orderIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'order_ids[]';
                input.value = id;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        });
    });
</script>


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

    /* Style for checkboxes */
    .order-checkbox {
        cursor: pointer;
    }

    #selectAllCheckbox {
        cursor: pointer;
    }
</style>