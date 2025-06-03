@extends('admin.layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Dashboard</h1>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.dashboard') }}">
                <div class="row">
                    <div class="col-md-4">
                        <label for="date_range" class="form-label">Date Range</label>
                        <input type="text" name="date_range" id="date_range" class="form-control date-range-picker"
                               value="{{ $selectedDateRange }}" placeholder="Select date range">
                    </div>
                    <div class="col-md-4">
                        <label for="status" class="form-label">Order Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ $selectedStatus == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="processing" {{ $selectedStatus == 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="completed" {{ $selectedStatus == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ $selectedStatus == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Filter</button>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row">
        <!-- Today's Orders -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Today's Orders</h5>
                    <h2 class="mb-0">{{ $todayOrders }}</h2>
                </div>
            </div>
        </div>

        <!-- Total Products -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Products</h5>
                    <h2 class="mb-0">{{ $totalProducts }}</h2>
                </div>
            </div>
        </div>

        <!-- Today's Profit -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Today's Profit</h5>
                    <h2 class="mb-0">{{ number_format($todayProfit, 2) }}</h2>
                </div>
            </div>
        </div>

        <!-- Total Profit -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">Total Profit</h5>
                    <h2 class="mb-0">{{ number_format($totalProfit, 2) }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtered Stats -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Filtered Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                                <div>
                                    <h6 class="mb-0">Total Orders</h6>
                                    <h3 class="mb-0">{{ $totalOrders }}</h3>
                                </div>
                                <i class="fas fa-shopping-cart fa-2x text-primary"></i>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                                <div>
                                    <h6 class="mb-0">Total Products</h6>
                                    <h3 class="mb-0">{{ $totalProducts }}</h3>
                                </div>
                                <i class="fas fa-boxes fa-2x text-success"></i>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                                <div>
                                    <h6 class="mb-0">Total Profit</h6>
                                    <h3 class="mb-0">{{ number_format($totalProfit, 2) }}</h3>
                                </div>
                                <i class="fas fa-money-bill-wave fa-2x text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Date Range Picker -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
    $(document).ready(function() {
        $('.date-range-picker').daterangepicker({
            opens: 'left',
            autoUpdateInput: false,
            locale: {
                format: 'YYYY-MM-DD',
                cancelLabel: 'Clear'
            }
        });

        $('.date-range-picker').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
        });

        $('.date-range-picker').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
    });
</script>
@endpush