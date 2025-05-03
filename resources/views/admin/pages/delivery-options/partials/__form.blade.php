@props(['deliveryOption' => null])

<div class="form-group">
    <label for="name">Delivery Option Name</label>
    <input type="text" class="form-control" id="name" name="name"
           value="{{ old('name', $deliveryOption->name ?? '') }}" required>
    @error('name')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="charge">Delivery Charge</label>
    <input type="number" step="0.01" class="form-control" id="charge" name="charge"
           value="{{ old('charge', $deliveryOption->charge ?? '') }}" required>
    @error('charge')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <div class="form-check">
        <input type="checkbox" class="form-check-input" id="is_active" name="is_active"
               value="1" {{ old('is_active', $deliveryOption->is_active ?? true) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_active">Active</label>
    </div>
</div>