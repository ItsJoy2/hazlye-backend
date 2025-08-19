@extends('admin.layouts.app')

@section('content')
<div class="container">
    <h1>Reviews</h1>
    <a href="{{ route('admin.reviews.create') }}" class="btn btn-primary mb-3">Add Review</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>User Name</th>
                <th>Mobile</th>
                <th>Product</th>
                <th>Rating</th>
                <th>Description</th>
                <th>Approved</th>
                <th>Images</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reviews as $review)
            <tr>
                <td>{{ $review->id }}</td>
                <td>{{ $review->user->name }}</td>
                <td>{{ $review->user->mobile }}</td>
                <td>{{ $review->product->name }}</td>
                <td>{{ $review->rating }}</td>
                <td>{{ $review->description }}</td>
                <td>{{ $review->is_approved ? 'Yes' : 'No' }}</td>
                <td>
                    @foreach($review->images as $img)
                        <img src="{{ $img->image_path }}" width="50" alt="Review Image">
                    @endforeach
                </td>
                <td>
                    <a href="{{ route('admin.reviews.edit', $review->id) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
