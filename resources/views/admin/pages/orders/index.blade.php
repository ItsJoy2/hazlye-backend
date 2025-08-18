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
            @include('admin.modal.confirmationmodal')
            @include('admin.modal.successmodal')
            
            <div id="alert-container">
                @include('admin.layouts.partials.__alerts')
            </div>

            <div class="mb-0">

                <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-3 mb-4" id="autoSubmitForm">
                    {{-- Date Filters --}}
                    <div class="col-md-2">
                        <input type="date" name="date_from" class="form-control auto-submit" value="{{ $dateFrom }}">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_to" class="form-control auto-submit" value="{{ $dateTo }}">
                    </div>

                    {{-- Extra Filters for delivered status --}}
                    @if($status === 'delivered')
                        <div class="col-md-2">
                            <input type="text" name="district" class="form-control auto-submit" placeholder="District" value="{{ $district }}">
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="thana" class="form-control auto-submit" placeholder="Thana" value="{{ $thana }}">
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="product_search" class="form-control auto-submit" placeholder="Product Name / SKU" value="{{ $productSearch }}">
                        </div>
                    @endif

                    {{-- Status Dropdown --}}
                    <div class="col-md-2">
                        <select name="status" class="form-control auto-submit">
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
                        <button type="submit" class="btn btn-primary d-none">Filter</button>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>

            <!-- Bulk Actions Form -->
            <form id="bulkActionForm" method="POST">
                @csrf
                <div class="d-flex justify-content-between mb-3">
                    <div>
                        {{-- <button type="button" class="btn btn-sm btn-success" id="selectAllBtn">
                            <i class="fas fa-check-circle"></i> Select All
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" id="deselectAllBtn">
                            <i class="fas fa-times-circle"></i> Deselect All
                        </button> --}}
                        @if($status === 'cancelled')
                        <button type="button" class="btn btn-sm btn-danger" id="bulkDeleteBtn">
                            <i class="fas fa-trash"></i> Delete Selected
                        </button>
                        @endif
                    </div>
                    @if(in_array($status, ['processing', 'shipped', 'courier_delivered', 'delivered']))
                    <button type="button" class="btn btn-sm btn-primary" id="exportBtn">
                        <i class="fas fa-file-excel"></i> Export Selected
                    </button>
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover table-head-bg-primary mt-4" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th width="30">
                                    <input type="checkbox" id="selectAllCheckbox">
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
                                @if($order->status === 'incomplete')
                                    @continue
                                @endif
                            <tr>
                                <td>
                                    @if(in_array($order->status, ['processing', 'shipped', 'courier_delivered', 'delivered']) || $order->status === 'cancelled')
                                        <input type="checkbox" name="order_ids[]" value="{{ $order->id }}"
                                               class="order-checkbox"
                                               data-status="{{ $order->status }}"
                                               data-date="{{ $order->created_at->format('Y-m-d') }}">
                                    @else
                                        <input type="checkbox" disabled class="order-checkbox">
                                    @endif
                                </td>
                                <td>{{ $order->order_number }}</td>
                                <td>
                                    @if($order->items->isNotEmpty() && $order->items[0]->product && $order->items[0]->product->main_image)
                                        <img src="{{ asset('storage/'.$order->items[0]->product->main_image) }}"
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
                                <td>&#2547;{{ number_format($order->total, 2) }}</td>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
        $('#selectAllCheckbox').on('change', function() {
            $('.order-checkbox:not(:disabled)').prop('checked', $(this).prop('checked'));
        });

        $('#selectAllBtn').click(function() {
            $('.order-checkbox:not(:disabled)').prop('checked', true);
            $('#selectAllCheckbox').prop('checked', true);
        });

        $('#deselectAllBtn').click(function() {
            $('.order-checkbox:not(:disabled)').prop('checked', false);
            $('#selectAllCheckbox').prop('checked', false);
        });

        $('.order-checkbox').on('change', function() {
            const allChecked = $('.order-checkbox:not(:disabled)').length === $('.order-checkbox:checked:not(:disabled)').length;
            $('#selectAllCheckbox').prop('checked', allChecked);
        });

        $('#exportBtn').click(function() {
            const selectedOrders = $('.order-checkbox:checked:not(:disabled)');

            if(selectedOrders.length === 0) {
                alert('Please select at least one order to export');
                return;
            }

            const orderIds = selectedOrders.map(function() {
                return $(this).val();
            }).get();

            const form = $('<form>', {
                'method': 'POST',
                'action': "{{ route('admin.orders.export') }}"
            });

            form.append($('<input>', {
                'type': 'hidden',
                'name': '_token',
                'value': "{{ csrf_token() }}"
            }));

            orderIds.forEach(function(id) {
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'order_ids[]',
                    'value': id
                }));
            });

            form.append($('<input>', {
                'type': 'hidden',
                'name': 'status',
                'value': "{{ request('status', 'all') }}"
            }));

            form.append($('<input>', {
                'type': 'hidden',
                'name': 'date_from',
                'value': "{{ request('date_from') }}"
            }));

            form.append($('<input>', {
                'type': 'hidden',
                'name': 'date_to',
                'value': "{{ request('date_to') }}"
            }));

            // Submit the form
            $('body').append(form);
            form.submit();
        });

        $('#bulkDeleteBtn').click(function() {
            const selectedOrders = $('.order-checkbox:checked:not(:disabled)');

            if(selectedOrders.length === 0) {
                alert('Please select at least one order to delete');
                return;
            }

            $('#confirmModal').modal('show');

            // Set up modal content
            $('#confirmModal .modal-title').text('Are You Sure?');
            $('#confirmModal .modal-body p').html(`Do you really want to delete <strong>${selectedOrders.length}</strong> orders? This process cannot be undone.`);

            $('#confirmButton').off('click');

            $('#confirmButton').on('click', function() {
                const orderIds = selectedOrders.map(function() {
                    return $(this).val();
                }).get();

                $.ajax({
                    url: "{{ route('admin.orders.bulk-delete') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        order_ids: orderIds
                    },
                    beforeSend: function() {
                        $('#confirmButton').html('<i class="fas fa-spinner fa-spin"></i> Deleting...');
                    },
                    success: function(response) {
                        $('#confirmModal').modal('hide');

                        // Show success modal
                        $('#successModal').modal('show');
                        $('#successModal .modal-title').text('Success');
                        $('#successModal .modal-body p').html(`
                            <strong>${selectedOrders.length}</strong> orders have been deleted successfully.
                        `);

                        // Optional: Reload the page after a delay
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    },
                    error: function(xhr) {
                        $('#confirmModal').modal('hide');

                        // Show error in success modal (reusing the same modal)
                        $('#successModal').modal('show');
                        $('#successModal .modal-title').text('Error');
                        $('#successModal .modal-body p').html(`
                            <div class="alert alert-danger">
                                ${xhr.responseJSON?.message || 'Something went wrong while deleting orders.'}
                            </div>
                        `);

                        $('#confirmButton').html('Delete');
                    }
                });
            });
        });

        $('.auto-submit').on('change', function() {
            setTimeout(function() {
                $('#filterForm').submit();
            }, 300);
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

    .order-checkbox {
        cursor: pointer;
    }

    #selectAllCheckbox {
        cursor: pointer;
    }

    #selectAllCheckbox {
        margin: 0;
        vertical-align: middle;
    }

    .order-checkbox {
        margin: 0;
        vertical-align: middle;
    }

    td:first-child {
        text-align: center;
    }
</style>


<script>
    document.addEventListener('DOMContentLoaded', function() {

        function autoSubmit() {
            clearTimeout(window.autoSubmitTimeout);
            window.autoSubmitTimeout = setTimeout(function() {
                document.getElementById('autoSubmitForm').submit();
            }, 500);
        }

        document.querySelectorAll('.auto-submit').forEach(function(element) {

            if (element.tagName === 'SELECT') {
                element.addEventListener('change', autoSubmit);
            } else {
                element.addEventListener('change', autoSubmit);
                element.addEventListener('keyup', function(e) {
                    if (e.key === 'Enter') {
                        autoSubmit();
                    }
                });
            }
        });
    });
    </script>



