@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Customer List</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">

            <div class="card-tools">
                <form action="{{ route('admin.customers.index') }}" method="GET">
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <input type="text" name="search" class="form-control float-right"
                               placeholder="Search..." value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
                <table class="table table-striped table-hover table-head-bg-primary mt-4">
                    <thead class="thead-light">
                        <tr>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th class="text-center">Orders</th>
                            <th class="text-center">Products</th>
                            <th class="text-right">Total Spent</th>
                            <th class="text-center">Last Order</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                        <tr>
                            <td>{{ $customer['name'] }}</td>
                            <td>{{ $customer['phone'] }}</td>
                            <td>{{ Str::limit($customer['primary_address'], 30) }}</td>
                            <td class="text-center">{{ $customer['order_count'] }}</td>
                            <td class="text-center">{{ $customer['total_products'] }}</td>
                            <td class="text-right">&#2547;{{ $customer['total_spent'] }}</td>
                            <td class="text-center">{{ $customer['last_order_at'] }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.orders.index', ['phone' => $customer['phone']]) }}"
                                   class="btn btn-sm btn-info" title="View Orders">
                                    <i class="fas fa-list"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No customers found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer clearfix">
                <div class="mt-4">
                    {{ $customers->links('admin.layouts.partials.__pagination') }}
                </div>
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
        background: none !important;
        width: 100% !important;
        border: none !important;
        margin-bottom: -20px !important;
    }
    .card-tools form input{
        background: none !important;
        padding-right: -30px !important;
        border-radius: 6px !important;
    }
    .container form i{
        margin-left: -40px;
        margin-top: 6px;
    }
</style>
{{-- <div class="card-footer clearfix">
    <div class="mt-4">
        {{ $customers->links('admin.layouts.partials.__pagination') }}
    </div>
</div> --}}