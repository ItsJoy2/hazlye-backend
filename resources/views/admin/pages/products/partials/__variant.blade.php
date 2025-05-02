<div class="variant-card card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Variant #{{ $variantIndex + 1 }}</span>
        <button type="button" class="btn btn-sm btn-danger remove-variant">Remove</button>
    </div>

    <div class="card-body">
        <div class="form-group">
            <label>Color</label>
            <select name="variants[{{ $variantIndex }}][color_id]" class="form-control" required>
                <option value="">Select Color</option>
                @foreach($colors as $color)
                    <option value="{{ $color->id }}">{{ $color->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Variant Image</label>
            <input type="file" name="variants[{{ $variantIndex }}][image]" class="form-control-file" required>
        </div>

        <div class="options-container" id="options-container-{{ $variantIndex }}">
            <!-- Options will be added here -->
            @include('admin.pages.products.partials.__option', [
                'variantIndex' => $variantIndex,
                'optionIndex' => 0
            ])
        </div>

        <button type="button" class="btn btn-sm btn-secondary add-option mt-2"
                data-variant="{{ $variantIndex }}">
            Add Option
        </button>
    </div>
</div>