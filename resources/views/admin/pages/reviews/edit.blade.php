@extends('admin.layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Edit Review</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary">Back to List</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.reviews.update', $review) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="form-group">
                                <label for="product_id">Product</label>
                                <select name="product_id" id="product_id" class="form-control" required>
                                    @foreach($products as $id => $name)
                                        <option value="{{ $id }}" {{ $review->product_id == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="guest_name">Reviewer Name</label>
                                <input type="text" name="guest_name" id="guest_name" class="form-control" value="{{ $review->guest_name }}" required>
                            </div>

                            <div class="form-group">
                                <label for="guest_email">Reviewer Email</label>
                                <input type="email" name="guest_email" id="guest_email" class="form-control" value="{{ $review->guest_email }}">
                            </div>

                            <div class="form-group">
                                <label for="rating">Rating</label>
                                <select name="rating" id="rating" class="form-control" required>
                                    @for($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}" {{ $review->rating == $i ? 'selected' : '' }}>{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="comment">Comment</label>
                                <textarea name="comment" id="comment" rows="5" class="form-control">{{ $review->comment }}</textarea>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="is_approved" id="is_approved" class="custom-control-input" value="1" {{ $review->is_approved ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_approved">Approved</label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Update Review</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection