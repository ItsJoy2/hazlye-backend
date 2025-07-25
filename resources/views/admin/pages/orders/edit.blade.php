<!-- resources/views/admin/orders/show.blade.php -->

@extends('admin.layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Order #{{ $order->order_number }}</h1>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Back to Orders</a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <form id="form-order-items" style="width: 100% !important" action="{{ route('admin.orders.update.items', $order->id) }}" method="POST">

                @csrf
                @method('PUT')

                <div class="card mb-12">
                    <div class="card-header"><h5>Order Items</h5></div>
                    <div class="card-body">
                        <table style="" class="table table-responsive" id="order-items-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $index => $item)
                                    <tr>
                                        <td>
                                            @if($order->items->isNotEmpty() && $order->items[0]->product && $order->items[0]->product->main_image)
                                                <img src="{{ asset('public/storage/'.$order->items[0]->product->main_image) }}"
                                                     alt="{{ $order->items[0]->product->name }}"
                                                     width="50"
                                                     class="img-thumbnail">
                                            @else
                                                <span class="text-muted">No image</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $item->product_name }}
                                            <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">

                                            @if($item->product && $item->product->has_variants)
                                                <!-- Variant product - show dropdowns for size and color -->
                                                <div class="variant-options mt-2">
                                                    <div class="">
                                                        <div class="">
                                                            <label>Size:</label>
                                                            <select name="items[{{ $index }}][size]" class="form-control form-control-sm">
                                                                <option value="">Select Size</option>
                                                                @foreach($item->product->variants->flatMap->options as $option)
                                                                    @if($option->size)
                                                                        <option value="{{ $option->size->name }}"
                                                                            {{ $item->size_name == $option->size->name ? 'selected' : '' }}>
                                                                            {{ $option->size->name }}
                                                                        </option>
                                                                    @endif
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="">
                                                            <label>Color:</label>
                                                            <select name="items[{{ $index }}][color]" class="form-control form-control-sm">
                                                                <option value="">Select Color</option>
                                                                @foreach($item->product->variants as $variant)
                                                                    @if($variant->color)
                                                                        <option value="{{ $variant->color->name }}"
                                                                            {{ $item->color_name == $variant->color->name ? 'selected' : '' }}
                                                                            data-color-code="{{ $variant->color->code }}">
                                                                            {{ $variant->color->name }}
                                                                        </option>
                                                                    @endif
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <!-- Non-variant product - simple text inputs -->
                                                <div class="">
                                                    <div class="">
                                                        <label>Size:</label>
                                                        <input type="text" name="items[{{ $index }}][size]"
                                                               value="{{ $item->size_name }}"
                                                               class="form-control form-control-sm">
                                                    </div>
                                                    <div class="">
                                                        <label>Color:</label>
                                                        <input type="text" name="items[{{ $index }}][color]"
                                                               value="{{ $item->color_name }}"
                                                               class="form-control form-control-sm">
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->product && $item->product->has_variants)
                                                {{ $item->variant->sku ?? 'N/A' }}
                                            @else
                                                {{ $item->product->sku ?? 'N/A' }}
                                            @endif
                                        </td>
                                        <td>${{ number_format($item->price, 2) }}</td>
                                        <td>
                                            <input type="number" name="items[{{ $index }}][quantity]"
                                                   value="{{ $item->quantity }}"
                                                   class="form-control form-control-sm"
                                                   style="width:60px;">
                                        </td>
                                        <td>${{ number_format($item->price * $item->quantity, 2) }}</td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm remove-item"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <hr>
                        <h6>Add Product by SKU:</h6>
                        <div class="input-group mb-3 position-relative">
                            <input type="text" name="new_sku" class="form-control" placeholder="Enter Product SKU" autocomplete="off">
                            <input type="number" name="new_quantity" class="form-control" placeholder="Qty" value="1" style="max-width:100px;">
                            <div id="sku-suggestions"></div>
                        </div>

                        <button type="submit" class="btn btn-primary">Update Order Items</button>
                    </div>
                </div>
            </form>

        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Subtotal:</strong> ${{ number_format($order->subtotal, 2) }}
                    </div>

                    @if($order->discount > 0)
                    <div class="mb-3">
                        <strong>Discount:</strong> -${{ number_format($order->discount, 2) }}
                        @if($order->coupon)
                            <br><small>Coupon: {{ $order->coupon->code }}</small>
                        @endif
                    </div>
                    @endif

                    <div class="mb-3">
                        <strong>Delivery Method:</strong>
                        {{ optional($order->deliveryOption)->name ?? 'Not Set' }}
                    </div>


                    <form style="background: none; border: none; margin:0;" action="{{ route('admin.orders.update.delivery', $order->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Delivery Charge --}}
                        <div class="delivery">
                            <label for="delivery_charge"><strong>Delivery Charge:</strong></label>
                            <input type="number" name="delivery_charge" id="delivery_charge" value="{{ $order->delivery_charge }}" step="0.01" class="form-control w-50 d-inline-block mx-1" style="max-width: 100px;" required>
                        </div>

                        {{-- New Discount Field --}}
                      <div class="admin-discount mt-3">
                        <label for="admin_discount" class="ms-3"><strong>Discount:</strong></label>
                        <input type="number" name="admin_discount" id="admin_discount" value="{{ $order->admin_discount ?? 0 }}" step="0.01" class="form-control w-50 d-inline-block mx-1" style="max-width: 100px;" required>
                      </div>

                        <button type="submit" class="btn btn-sm btn-primary">Update</button>
                    </form>

                    <div class="mb-3">
                        <strong>Total:</strong> ${{ number_format($order->total, 2) }}
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5>Customer Details</h5>
                </div>
                <form style="width: 100% !important; background: none; border: none; margin:0;" action="{{ route('admin.orders.update', $order) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group mb-2">
                        <label for="name"><strong>Name</strong></label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $order->name) }}" required>
                    </div>

                    <div class="form-group mb-2">
                        <label for="phone"><strong>Phone</strong></label>
                        <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $order->phone) }}" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="address"><strong>Address</strong></label>
                        <textarea name="address" id="address" class="form-control" rows="2" required>{{ old('address', $order->address) }}</textarea>
                    </div>
                    @php
                    $districts = config('bd_location');
                    $selDist = old('district', $order->district);
                    $selThana = old('thana', $order->thana);
                    @endphp

                    <div class="form-group mb-3">
                      <label><strong>District</strong></label>
                      <select id="district" name="district" class="form-control" required>
                        <option value="">Select District</option>
                        @foreach($districts as $d => $thanas)
                          <option value="{{ $d }}" {{ $selDist == $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                      </select>
                    </div>

                    <div class="form-group mb-3">
                      <label><strong>Thana</strong></label>
                      <select id="thana" name="thana" class="form-control" required>
                        <option value="">Select Thana</option>
                      </select>
                    </div>

                    <button type="submit" class="btn btn-sm btn-primary">Update Customer</button>
                </form>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>Update Order Status</h5>
                </div>
                <div class="card-body">
                    <form style="width: 100% !important; background: none; border: none; margin:0;" action="{{ route('admin.orders.update-status', $order) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" class="form-control">
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="hold" {{ old('status', $order->status ?? '') == 'hold' ? 'selected' : '' }}>Hold</option>
                                <option value="processing" {{ old('status', $order->status ?? '') == 'processing' ? 'selected' : '' }}>Order confirmed</option>
                                <option value="shipped" {{ old('status', $order->status ?? '') == 'shipped' ? 'selected' : '' }}>Ready to Shipped</option>
                                <option value="courier_delivered" {{ old('status', $order->status ?? '') == 'courier_delivered' ? 'selected' : '' }}>Courier Delivered</option>
                                <option value="delivered" {{ old('status', $order->status ?? '') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="cancelled" {{ old('status', $order->status ?? '') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                {{-- <option value="incomplete" {{ old('status', $order->status ?? '') == 'incomplete' ? 'selected' : '' }}>Incomplete</option> --}}

                            </select>
                        </div>

                        <div class="form-group">
                            <label for="comment">Comment (Optional)</label>
                            <textarea name="comment" id="comment" class="form-control" rows="3">{{ old('comment', $order->comment) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  const bdLocations = @json(config('bd_location'));
  const selDist = "{{ $selDist }}";
  const selThana = "{{ $selThana }}";

  function loadThanas(district) {
    const list = bdLocations[district] || [];
    const $thana = $('#thana').empty().append('<option value="">Select Thana</option>');
    list.forEach(th => {
      const selected = th === selThana ? 'selected' : '';
      $thana.append(`<option value="${th}" ${selected}>${th}</option>`);
    });
  }

  $(function() {
    $('#district').on('change', function() {
      loadThanas(this.value);
    });

    if (selDist) {
      loadThanas(selDist);
    }
  });
</script>

<script>
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-item')) {
            const row = e.target.closest('tr');
            const idInput = row.querySelector('input[name*="[id]"]');

            if (idInput) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'removed_ids[]';
                hiddenInput.value = idInput.value;
                document.getElementById('form-order-items').appendChild(hiddenInput);
            }

            row.remove();
        }
    });

</script>

<script>
    $(document).ready(function () {
        const suggestionBox = $('#sku-suggestions');

        $('#new_sku_input').on('input', function () {
            const query = $(this).val();

            if (query.length < 2) {
                suggestionBox.hide();
                return;
            }

            $.get("{{ route('admin.orders.sku-search') }}", { query: query }, function (data) {
                suggestionBox.empty().show();
                data.forEach(item => {
                    suggestionBox.append(`
                        <a class="list-group-item list-group-item-action"
                        data-type="${item.type}"
                        data-id="${item.id}"
                        data-sku="${item.sku}"
                        data-name="${item.name}"
                        data-price="${item.price}"
                        data-product-id="${item.product_id ?? item.id}"
                        data-size="${item.size ?? ''}"
                        data-color="${item.color ?? ''}">
                            ${item.sku} - ${item.name}
                        </a>
                    `);
                });

                if (suggestionBox.children().length === 0) {
                    suggestionBox.hide();
                }
            });
        });

        suggestionBox.on('click', '.list-group-item', function () {
            const index = $('#order-items-table tbody tr').length;
            const type = $(this).data('type');
            const productId = $(this).data('product-id');
            const name = $(this).data('name');
            const price = parseFloat($(this).data('price')).toFixed(2);
            const size = $(this).data('size');
            const color = $(this).data('color');
            const qty = $('#new_quantity_input').val() || 1;

            $('#order-items-table tbody').append(`
                <tr>
                    <td>
                        ${name}
                        <input type="hidden" name="items[${index}][product_id]" value="${productId}">
                        <input type="hidden" name="items[${index}][product_name]" value="${name}">
                        <input type="hidden" name="items[${index}][price]" value="${price}">
                        <input type="hidden" name="items[${index}][size]" value="${size}">
                        <input type="hidden" name="items[${index}][color]" value="${color}">
                    </td>
                    <td>$${price}</td>
                    <td>
                        <input type="number" name="items[${index}][quantity]" value="${qty}" class="form-control form-control-sm" style="width:60px;">
                    </td>
                    <td>$${(price * qty).toFixed(2)}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-item">Remove</button>
                    </td>
                </tr>
            `);

            suggestionBox.hide();
            $('#new_sku_input').val('');
            $('#new_quantity_input').val(1);
        });
    });

</script>


<style>
    .color-badge {
    display: inline-block;
    width: 15px;
    height: 15px;
    border-radius: 3px;
    vertical-align: middle;
    border: 1px solid #ddd;
}

#sku-suggestions {
    position: absolute;
    z-index: 1000;
    width: 100%;
    max-width: 500px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: none;
}

.list-group-item {
    cursor: pointer;
}
</style>