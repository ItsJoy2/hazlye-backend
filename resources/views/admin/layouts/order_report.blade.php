<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<style>
body { font-family: solaimanlipi, sans-serif; font-size: 10pt; }
.header { text-align: center; margin-bottom: 20px; }
.header img { max-height: 80px; margin-bottom: 10px; }
.header h2 { margin: 0; }
.order { border: 1px solid #000; margin-bottom: 15px; padding: 5px; }
.order-header { background: #f0f0f0; font-weight: bold; padding: 5px; }
.items { width: 100%; border-collapse: collapse; margin-top: 5px; }
.items th, .items td { border: 1px solid #000; padding: 5px; text-align: left; vertical-align: middle; }
.product-image { width: 100px; height: 100px; object-fit: cover; }
.variant-color { display: inline-block; width: 20px; height: 20px; border-radius: 50%; border: 1px solid #000; }
.admin-comment { background-color: #ffcccc; color: #000; }
</style>
</head>
<body>

@php
    $settings = \App\Models\GeneralSetting::first();
@endphp

<div class="header">
    @if($settings && $settings->logo)
        <img src="{{ public_path('storage/'.$settings->logo) }}" alt="Logo" height="50px">
        @else
    <h2>{{ $settings->app_name ?? 'Orders Report' }}</h2>
    @endif
</div>
<hr>
@foreach($orders as $order)
<div class="order">
    <div class="order-header">
        Order #{{ $order->order_number }} | Date: {{ $order->created_at->format('d M, Y') }}
    </div>
    <table class="items">
        <thead>
            <tr>
                <th>Image</th>
                <th>Product Name (SKU)</th>
                <th>Size</th>
                <th>Variant Color</th>
                <th>Consignment ID</th>
                <th>Courier Name</th>
                <th class="admin-comment">Admin Comment</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>
                    @if($item->product && $item->product->main_image)
                        <img src="{{ public_path('storage/'.$item->product->main_image) }}" class="product-image" alt="" >
                    @else N/A @endif
                </td>
                <td>{{ $item->product?->name ?? 'N/A' }} ({{ $item->product?->sku ?? 'N/A' }})</td>

                {{-- Size: ProductVariantOption > Size, fallback to Product->size --}}
                <td>
                    @if($item->variantOption?->size)
                        {{ $item->variantOption->size->name ?? $item->variantOption->name ?? 'N/A' }}
                    @elseif($item->product?->size)
                        {{ $item->product->size->name ?? 'N/A' }}
                    @else
                        N/A
                    @endif
                </td>

                {{-- Color: ProductVariantOption > Color --}}
                <td>
                    @if($item->variantOption?->color)
                        <span class="variant-color" style="background-color: {{ $item->variantOption->color }}"></span>
                    @else
                        N/A
                    @endif
                </td>

                <td>{{ $order->tracking_code ?? 'N/A' }}</td>
                <td>{{ $order->deliveryOption?->courier_name ?? 'N/A' }}</td>
                <td class="admin-comment">{{ $order->admin_comment ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endforeach

</body>
</html>
