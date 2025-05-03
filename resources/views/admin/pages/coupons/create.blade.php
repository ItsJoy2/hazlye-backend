@extends('admin.layouts.app')

@section('content')
<div class="container">
    <h1>Create New Coupon</h1>

    <form action="{{ route('admin.coupons.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="code">Coupon Code (leave blank to auto-generate)</label>
            <input type="text" class="form-control" id="code" name="code" placeholder="e.g. SUMMER20">
        </div>

        <div class="form-group">
            <label for="type">Discount Type</label>
            <select class="form-control" id="type" name="type">
                <option value="fixed">Fixed Amount</option>
                <option value="percentage">Percentage</option>
            </select>
        </div>

        <div class="form-group">
            <label for="amount">Discount Value</label>
            <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
        </div>

        <div class="form-group">
            <label for="min_purchase">Minimum Purchase Amount</label>
            <input type="number" step="0.01" class="form-control" id="min_purchase" name="min_purchase" required>
        </div>

        <div class="form-group">
            <label for="start_date">Start Date</label>
            <input type="date" class="form-control" id="start_date" name="start_date" required>
        </div>

        <div class="form-group">
            <label for="end_date">End Date</label>
            <input type="date" class="form-control" id="end_date" name="end_date" required>
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" checked>
            <label class="form-check-label" for="is_active">Active</label>
        </div>

        <button type="submit" class="btn btn-primary">Create Coupon</button>
    </form>
</div>
@endsection