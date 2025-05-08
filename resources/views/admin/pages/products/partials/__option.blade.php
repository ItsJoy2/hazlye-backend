<div class="option-row row mb-2 mt-4">
    <div class="col-md-3">
        <select name="variants[{{ $variantIndex }}][options][{{ $optionIndex }}][size_id]" class="form-control" required>
            <option value="">Select Size</option>
            @foreach($sizes as $size)
                <option value="{{ $size->id }}" @if(isset($option['size_id']) && $option['size_id'] == $size->id) selected @endif>
                    {{ $size->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <input type="number" step="0.01" name="variants[{{ $variantIndex }}][options][{{ $optionIndex }}][price]"
               class="form-control" placeholder="Price" required
               value="{{ $option['price'] ?? '' }}">
    </div>
    <div class="col-md-3">
        <input type="number" name="variants[{{ $variantIndex }}][options][{{ $optionIndex }}][stock]"
               class="form-control" placeholder="Stock" required
               value="{{ $option['stock'] ?? '' }}">
    </div>
    <div class="col-md-3">
        <input type="text" name="variants[{{ $variantIndex }}][options][{{ $optionIndex }}][sku]"
               class="form-control" placeholder="SKU"
               value="{{ $option['sku'] ?? '' }}" required>
    </div>
    <div class="col-md-3 trash-btn">
        <button type="button" class="btn btn-sm btn-danger remove-option">
            <i class="fas fa-trash"></i>
        </button>
    </div>
</div>