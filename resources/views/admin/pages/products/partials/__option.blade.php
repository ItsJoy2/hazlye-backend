<div class="option-row mb-3">
    <div class="row">
        <div class="col-md-4">
            <label>Size</label>
            <select name="variants[{{ $variantIndex }}][options][{{ $optionIndex }}][size_id]" class="form-control" required>
                <option value="">Select Size</option>
                @foreach($sizes as $size)
                    <option value="{{ $size->id }}">{{ $size->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label>Price</label>
            <input type="number" step="0.01"
                   name="variants[{{ $variantIndex }}][options][{{ $optionIndex }}][price]"
                   class="form-control" required>
        </div>
        <div class="col-md-3">
            <label>Stock</label>
            <input type="number"
                   name="variants[{{ $variantIndex }}][options][{{ $optionIndex }}][stock]"
                   class="form-control" required>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="button" class="btn btn-sm btn-danger remove-option">Remove</button>
        </div>
    </div>
</div>