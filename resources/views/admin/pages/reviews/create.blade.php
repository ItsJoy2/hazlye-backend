
@extends('admin.layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Create New Review</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary">Back to List</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.reviews.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="form-group">
                                <label for="product_id">Product</label>
                                <select name="product_id" id="product_id" class="form-control" required>
                                    <option value="">Select Product</option>
                                    @foreach($products as $id => $name)
                                        <option value="{{ $id }}" {{ old('product_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="user_id">User (Leave blank for guest)</label>
                                <select name="user_id" id="user_id" class="form-control">
                                    <option value="">Select User (optional)</option>
                                    @foreach($users as $id => $name)
                                        <option value="{{ $id }}" {{ old('user_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="guest_name">Guest Name (if no user selected)</label>
                                <input type="text" name="guest_name" id="guest_name" class="form-control" value="{{ old('guest_name') }}">
                            </div>

                            <div class="form-group">
                                <label for="guest_email">Guest Email</label>
                                <input type="email" name="guest_email" id="guest_email" class="form-control" value="{{ old('guest_email') }}">
                            </div>

                            <div class="form-group">
                                <label for="rating">Rating</label>
                                <select name="rating" id="rating" class="form-control" required>
                                    @for($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}" {{ old('rating') == $i ? 'selected' : '' }}>{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="comment">Comment</label>
                                <textarea name="comment" id="comment" rows="5" class="form-control">{{ old('comment') }}</textarea>
                            </div>

                            <div class="form-group">
                                <label for="images">Review Images (Multiple)</label>
                                <input type="file" name="images[]" id="images" class="form-control-file" multiple>
                                <small class="form-text text-muted">You can upload multiple images (max 2MB each)</small>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="is_approved" id="is_approved" class="custom-control-input" value="1" {{ old('is_approved') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_approved">Approved</label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Create Review</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection