@extends('admin.layouts.app')

@section('content')
<div class="container">
    <h1>Create New Coupon</h1>

    <form action="{{ route('admin.coupons.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="code">Coupon Code</label>
            <input type="text" name="code" id="code" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="type">Type</label>
            <select name="type" id="type" class="form-control" required>
                <option value="fixed">Fixed Amount</option>
                <option value="percentage">Percentage</option>
            </select>
        </div>

        <div class="form-group">
            <label for="amount">Amount</label>
            <input type="number" name="amount" id="amount" class="form-control" step="0.01" min="0" required>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" required>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="min_purchase">Minimum Purchase Amount (0 for no minimum)</label>
            <input type="number" name="min_purchase" id="min_purchase" class="form-control" step="0.01" min="0" value="0">
        </div>


        <div class="form-group form-check">
            <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" checked>
            <label for="is_active" class="form-check-label">Active</label>
        </div>

        <div class="form-group form-check">
            <input type="checkbox" name="apply_to_all" id="apply_to_all" class="form-check-input" value="1">
            <label for="apply_to_all" class="form-check-label">Apply to all products</label>
        </div>

        <div class="form-group" id="products-section">
            <label>Select Products (leave empty if applying to all)</label>

            <!-- Enhanced search box with clear button -->
            <div class="input-group mb-3">
                <input type="text" id="product-search" class="form-control" placeholder="Search products...">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="button" id="clear-search">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Product selection area with counters -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <span id="visible-count">{{ count($products) }}</span> of
                        <span id="total-count">{{ count($products) }}</span> products
                    </div>
                    {{-- <div class="form-check">
                        <input type="checkbox" id="select-all-products" class="form-check-input">
                        <label for="select-all-products" class="form-check-label mb-0"><small>Select visible</small></label>
                    </div> --}}
                </div>
                <div class="product-checkboxes-container card-body" style="max-height: 100px; overflow-y: auto;">
                    <div id="product-list">
                        @foreach($products as $product)
                        <div class="form-check product-item mb-2 d-flex" data-name="{{ strtolower($product->name) }}">
                            <input type="checkbox" name="products[]" id="product-{{ $product->id }}"
                                   value="{{ $product->id }}" class="form-check-input product-checkbox">
                            <label for="product-{{ $product->id }}" class="form-check-label">
                                <span class="product-name">{{ $product->name }}</span>
                                {{-- <small class="text-muted ml-2">SKU: {{ $product->sku }}</small> --}}
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

        <button type="submit" class="btn btn-primary mt-3">Create Coupon</button>
    </form>
</div>
@endsection


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const applyToAllCheckbox = document.getElementById('apply_to_all');
        const productsSection = document.getElementById('products-section');
        const selectAllCheckbox = document.getElementById('select-all-products');
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

        // Select all VISIBLE products
        selectAllCheckbox.addEventListener('change', function() {
            const visibleCheckboxes = document.querySelectorAll('.product-item:not([style*="display: none"]) .product-checkbox');
            visibleCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
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
                const itemText = item.dataset.name + ' ' + item.dataset.sku + ' ' + item.dataset.category;
                let matchesAllTerms = true;

                // Check if item matches ALL search terms
                for (const term of searchTerms) {
                    if (!itemText.includes(term)) {
                        matchesAllTerms = false;
                        break;
                    }
                }

                if (searchTerms.length === 0 || matchesAllTerms) {
                    item.style.display = 'block';
                    visibleItems++;

                    // Highlight matching text
                    if (searchTerms.length > 0) {
                        highlightMatches(item, searchTerms);
                    } else {
                        removeHighlights(item);
                    }
                } else {
                    item.style.display = 'none';
                    removeHighlights(item);
                }
            });

            // Update counters
            visibleCount.textContent = visibleItems;
            selectAllCheckbox.checked = false;
        }

        // Highlight matching text in product names
        function highlightMatches(item, terms) {
            const nameElement = item.querySelector('.product-name');
            let nameText = nameElement.textContent;

            terms.forEach(term => {
                const regex = new RegExp(term, 'gi');
                nameText = nameText.replace(regex, match =>
                    `<span class="bg-warning">${match}</span>`);
            });

            nameElement.innerHTML = nameText;
        }

        // Remove highlighting
        function removeHighlights(item) {
            const nameElement = item.querySelector('.product-name');
            nameElement.innerHTML = nameElement.textContent;
        }

        // If any product checkbox is unchecked, uncheck the "select all" checkbox
        document.getElementById('product-list').addEventListener('change', function(e) {
            if (e.target.classList.contains('product-checkbox')) {
                if (!e.target.checked) {
                    selectAllCheckbox.checked = false;
                } else {
                    // Check if all VISIBLE products are selected
                    const visibleCheckboxes = document.querySelectorAll('.product-item:not([style*="display: none"]) .product-checkbox');
                    const allChecked = visibleCheckboxes.length > 0 &&
                        Array.from(visibleCheckboxes).every(cb => cb.checked);
                    selectAllCheckbox.checked = allChecked;
                }
            }
        });

        // Initial filter (in case page loads with search term)
        filterProducts();
    });
    </script>


<style>
    .product-checkboxes-container {
        transition: all 0.3s ease;
    }
    .product-item {
        padding: 5px 0;
        border-bottom: 1px solid #eee;
    }
    .product-item:last-child {
        border-bottom: none;
    }
    #product-search {
        margin-bottom: 10px;
    }
    <style>
    .product-checkboxes-container {
        transition: all 0.3s ease;
    }
    .product-item {
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .product-item:last-child {
        border-bottom: none;
    }
    .bg-warning {
        background-color: #ffc107;
        padding: 0 2px;
        border-radius: 3px;
    }
    #clear-search {
        cursor: pointer;
    }
    .card-header {
        padding: 0.5rem 1rem;
    }
</style>