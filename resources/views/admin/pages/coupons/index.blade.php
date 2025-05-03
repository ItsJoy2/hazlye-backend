@extends('admin.layouts.app')

@section('content')
<div class="container">
    <h1>Coupons</h1>

    <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary mb-3">Create New Coupon</a>

    <div class="table-responsive">
        <table class="table table-striped table-hover table-head-bg-primary mt-4">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Value</th>
                    <th>Min. Purchase</th>
                    <th>Valid From</th>
                    <th>Valid To</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($coupons as $coupon)
                <tr>
                    <td>{{ $coupon->code }}</td>
                    <td>{{ ucfirst($coupon->type) }}</td>
                    <td>{{ $coupon->type === 'percentage' ? $coupon->amount.'%' : $coupon->amount }}</td>
                    <td>{{ $coupon->min_purchase }}</td>
                    <td>{{ $coupon->start_date->format('M d, Y') }}</td>
                    <td>{{ $coupon->end_date->format('M d, Y') }}</td>
                    <td>
                        <span class="badge badge-{{ $coupon->is_active ? 'success' : 'danger' }}">
                            {{ $coupon->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                       @include('admin.pages.coupons.partials.__actions')
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-footer clearfix">
        <div class="mt-4">
            {{ $coupons->links('admin.layouts.partials.__pagination') }}
        </div>
    </div>

</div>
@endsection