@extends('admin.layouts.app')

@section('content')
<div class="container">
    <h1>Edit Coupon With : {{$coupon->code}}</h1>

    <form action="{{ route('admin.coupons.update', $coupon->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="code">Coupon Code</label>
            <input type="text" class="form-control" id="code" name="code"
                   value="{{ old('code', $coupon->code) }}" required>
            @error('code')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="type">Discount Type</label>
            <select class="form-control" id="type" name="type">
                <option value="fixed" {{ old('type', $coupon->type) == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                <option value="percentage" {{ old('type', $coupon->type) == 'percentage' ? 'selected' : '' }}>Percentage</option>
            </select>
        </div>

        <div class="form-group">
            <label for="amount">Discount Value</label>
            <input type="number" step="0.01" class="form-control" id="amount" name="amount"
                   value="{{ old('amount', $coupon->amount) }}" required>
            @error('amount')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="min_purchase">Minimum Purchase Amount</label>
            <input type="number" step="0.01" class="form-control" id="min_purchase" name="min_purchase"
                   value="{{ old('min_purchase', $coupon->min_purchase) }}" required>
            @error('min_purchase')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="start_date">Start Date</label>
            <input type="date" class="form-control" id="start_date" name="start_date"
                   value="{{ old('start_date', $coupon->start_date->format('Y-m-d')) }}" required>
            @error('start_date')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="end_date">End Date</label>
            <input type="date" class="form-control" id="end_date" name="end_date"
                   value="{{ old('end_date', $coupon->end_date->format('Y-m-d')) }}" required>
            @error('end_date')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                   {{ old('is_active', $coupon->is_active) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Active</label>
        </div>

        <button type="submit" class="btn btn-primary">Update Coupon</button>
        <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection