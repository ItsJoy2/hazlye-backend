@extends('admin.layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Product Reviews</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.reviews.create') }}" class="btn btn-primary">Add Review</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="mb-4">
                            <div class="row">
                                <div class="col-md-4">
                                    <label>Approval Status</label>
                                    <select name="is_approved" class="form-control">
                                        <option value="all">All</option>
                                        <option value="1" {{ request('is_approved') == '1' ? 'selected' : '' }}>Approved</option>
                                        <option value="0" {{ request('is_approved') == '0' ? 'selected' : '' }}>Pending</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label>Product</label>
                                    <select name="product_id" class="form-control">
                                        <option value="">All Products</option>
                                        @foreach($products as $id => $name)
                                            <option value="{{ $id }}" {{ request('product_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                </div>
                            </div>
                        </form>

                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Product</th>
                                    <th>Reviewer</th>
                                    <th>Rating</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reviews as $review)
                                    <tr>
                                        <td>{{ $review->id }}</td>
                                        <td>{{ $review->product->name }}</td>
                                        <td>
                                            @if($review->user_id)
                                                {{ $review->user->name }}
                                            @else
                                                {{ $review->guest_name }}
                                            @endif
                                        </td>
                                        <td>
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $review->rating)
                                                    <i class="fas fa-star text-warning"></i>
                                                @else
                                                    <i class="far fa-star text-warning"></i>
                                                @endif
                                            @endfor
                                        </td>
                                        <td>
                                            @if($review->is_approved)
                                                <span class="badge badge-success">Approved</span>
                                            @else
                                                <span class="badge badge-warning">Pending</span>
                                            @endif
                                        </td>
                                        <td>{{ $review->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('admin.reviews.show', $review) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.reviews.edit', $review) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if(!$review->is_approved)
                                                <a href="{{ route('admin.reviews.approve', $review) }}" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            @endif
                                            <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="d-flex justify-content-center">
                            {{ $reviews->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection