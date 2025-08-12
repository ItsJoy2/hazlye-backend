

@extends('admin.layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Order #{{ $order->order_number }}</h1>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Back to Orders</a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Order Items</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>
                                    @if($order->items->isNotEmpty() && $order->items[0]->product && $order->items[0]->product->main_image)
                                        <img src="{{ asset('storage/'.$order->items[0]->product->main_image) }}"
                                             alt="{{ $order->items[0]->product->name }}"
                                             width="50"
                                             class="img-thumbnail">
                                    @else
                                        <span class="text-muted">No image</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $item->product_name }}
                                    @if($item->size_name)
                                        <br><small>Size: {{ $item->size_name }}</small>
                                    @endif
                                    @if($item->color_name)
                                        <br><small>Color: {{ $item->color_name }}</small>
                                    @endif
                                </td>
                                <td>&#2547;{{ number_format($item->price, 2) }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>&#2547;{{ number_format($item->price * $item->quantity, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Subtotal:</strong> &#2547;{{ number_format($order->subtotal, 2) }}
                    </div>
                    <div class="mb-3">
                        <strong>Delivery Method:</strong>
                        {{ optional($order->deliveryOption)->name ?? 'Not Set' }}
                    </div>
                    @if($order->discount > 0)
                    <div class="mb-3">
                        <strong>Discount:</strong> -&#2547;{{ number_format($order->discount, 2) }}
                        @if($order->coupon)
                            <br><small>Coupon: {{ $order->coupon->code }}</small>
                        @endif
                    </div>
                    @endif

                    <div class="mb-3">
                        <strong>Delivery Charge:</strong> &#2547;{{ number_format($order->delivery_charge, 2) }}
                    </div>

                    <div class="mb-3">
                        <strong>Total:</strong> &#2547;{{ number_format($order->total, 2) }}
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5>Customer Details</h5>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> {{ $order->name }}</p>
                    <p><strong>Phone:</strong> {{ $order->phone }}</p>
                    <p><strong>Address:</strong> {{ $order->address }}</p>
                    <p><strong>District:</strong> {{ $order->district }}</p>
                    <p><strong>Thana:</strong> {{ $order->thana }}</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>Update Order Status</h5>
                </div>
                <div class="card-body">


                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control" @readonly(true)>
                                <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="courier_delivered" {{ $order->status == 'courier_delivered' ? 'selected' : '' }}>Courier Delivered</option>
                                <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="comment">Comment (Optional)</label>
                            <textarea name="comment" id="comment" class="form-control" rows="3" readonly>{{ old('comment', $order->comment) }}</textarea>
                        </div>

                        @if($order->status == 'shipped' && $order->tracking_code)
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6>Courier Information</h6>
                        <p><strong>Courier:</strong> {{ $order->courier->name ?? 'N/A' }}</p>
                        <p><strong>Tracking Code:</strong> {{ $order->tracking_code }}</p>
                        <p><strong>Consignment ID:</strong> {{ $order->consignment_id ?? 'N/A' }}</p>
                        <a href="https://steadfast.com.bd/t/{{ $order->tracking_code }}"target="_blank"class="btn btn-sm btn-info">
                           Track Order
                        </a>
                    </div>
                    @endif

                        {{-- <button type="submit" class="btn btn-primary">Update Status</button> --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection