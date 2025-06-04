@extends('admin.layouts.app')

@section('content')
<div class="container">
    <h1>Edit Coupon</h1>

    <form action="{{ route('admin.coupons.update', $coupon->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="code">Coupon Code</label>
            <input type="text" name="code" id="code" class="form-control" value="{{ old('code', $coupon->code) }}" required>
        </div>

        <div class="form-group">
            <label for="type">Type</label>
            <select name="type" id="type" class="form-control" required>
                <option value="fixed" {{ old('type', $coupon->type) == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                <option value="percentage" {{ old('type', $coupon->type) == 'percentage' ? 'selected' : '' }}>Percentage</option>
            </select>
        </div>

        <div class="form-group">
            <label for="amount">Amount</label>
            <input type="number" name="amount" id="amount" class="form-control"
                   step="0.01" min="0" value="{{ old('amount', $coupon->amount) }}" required>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" name="start_date" id="start_date"
                           class="form-control" value="{{ old('start_date', $coupon->start_date->format('Y-m-d')) }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" name="end_date" id="end_date"
                           class="form-control" value="{{ old('end_date', $coupon->end_date->format('Y-m-d')) }}" required>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="min_purchase">Minimum Purchase Amount (0 for no minimum)</label>
            <input type="number" name="min_purchase" id="min_purchase"
                   class="form-control" step="0.01" min="0"
                   value="{{ old('min_purchase', $coupon->min_purchase) }}">
        </div>

        <div class="form-group form-check">
            <input type="checkbox" name="is_active" id="is_active"
                   class="form-check-input" value="1"
                   {{ old('is_active', $coupon->is_active) ? 'checked' : '' }}>
            <label for="is_active" class="form-check-label">Active</label>
        </div>

        <div class="form-group form-check">
            <input type="checkbox" name="apply_to_all" id="apply_to_all"
                   class="form-check-input" value="1"
                   {{ old('apply_to_all', $coupon->apply_to_all) ? 'checked' : '' }}>
            <label for="apply_to_all" class="form-check-label">Apply to all products</label>
        </div>

        <div class="form-group" id="products-section"
             style="{{ $coupon->apply_to_all ? 'display: none;' : '' }}">
            <label>Select Products (leave empty if applying to all)</label>

            <div class="input-group mb-3">
                <input type="text" id="product-search" class="form-control" placeholder="Search products...">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="button" id="clear-search">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <span id="visible-count">{{ count($products) }}</span> of
                        <span id="total-count">{{ count($products) }}</span> products
                    </div>
                </div>
                <div class="product-checkboxes-container card-body" style="max-height: 200px; overflow-y: auto;">
                    <div id="product-list">
                        @foreach($products as $product)
                        <div class="form-check product-item mb-2 d-flex" data-name="{{ strtolower($product->name) }}">
                            <input type="checkbox" name="products[]" id="product-{{ $product->id }}"
                                   value="{{ $product->id }}" class="form-check-input product-checkbox"
                                   {{ in_array($product->id, old('products', $selectedProducts)) ? 'checked' : '' }}>
                            <label for="product-{{ $product->id }}" class="form-check-label">
                                <span class="product-name">{{ $product->name }}</span>
                                @if($product->category)
                                <span class="badge badge-info ml-2">{{ $product->category->name }}</span>
                                @endif
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Update Coupon</button>
        <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary mt-3">Cancel</a>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const applyToAllCheckbox = document.getElementById('apply_to_all');
    const productsSection = document.getElementById('products-section');
    const productSearch = document.getElementById('product-search');
    const clearSearch = document.getElementById('clear-search');
    const productItems = document.querySelectorAll('.product-item');
    const visibleCount = document.getElementById('visible-count');
    const totalCount = document.getElementById('total-count');

    // Toggle products section visibility
    applyToAllCheckbox.addEventListener('change', function() {
        productsSection.style.display = this.checked ? 'none' : 'block';
    });

    // Clear search field
    clearSearch.addEventListener('click', function() {
        productSearch.value = '';
        filterProducts();
    });

    // Product search functionality with debounce
    let searchTimeout;
    productSearch.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(filterProducts, 300);
    });

    // Filter products based on search terms
    function filterProducts() {
        const searchTerms = productSearch.value.toLowerCase().split(' ').filter(term => term.length > 0);
        let visibleItems = 0;

        productItems.forEach(item => {
            const productName = item.querySelector('.product-name').textContent.toLowerCase();
            let matchesAllTerms = true;

            for (const term of searchTerms) {
                if (!productName.includes(term)) {
                    matchesAllTerms = false;
                    break;
                }
            }

            if (searchTerms.length === 0 || matchesAllTerms) {
                item.style.display = 'flex';
                visibleItems++;
            } else {
                item.style.display = 'none';
            }
        });

        visibleCount.textContent = visibleItems;
    }

    // Initial filter
    filterProducts();
});
</script>

<style>
.product-checkboxes-container {
    transition: all 0.3s ease;
}
.product-item {
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
}
.product-item:last-child {
    border-bottom: none;
}
#clear-search {
    cursor: pointer;
}
.card-header {
    padding: 0.5rem 1rem;
}
</style>
@endsection