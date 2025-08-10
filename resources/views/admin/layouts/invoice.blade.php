<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order #{{ $order->order_number }}</title>

    <style>
        @font-face {
            font-family: 'Noto Sans Bengali';
            font-style: normal;
            font-weight: 400;
            src: url(data:font/truetype;charset=utf-8;base64,{{ base64_encode(file_get_contents(public_path('assets/admin/fonts/NotoSansBengali/TiroBangla-Regular.ttf'))) }}) format('truetype');
        }

        @page {
            size: 2in auto;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Noto Sans Bengali', Arial, sans-serif;
            width: 2in;
            margin: 0;
            padding: 2px;
            font-size: 8px;
            line-height: 1.2;
        }
        * {
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }
        .header {
            text-align: center;
            margin-bottom: 3px;
            padding-bottom: 3px;
            border-bottom: 1px dashed #ccc;
        }
        .header h3 {
            margin: 2px 0;
            font-size: 9px;
            font-weight: 700;
        }
        .info-section {
            margin-bottom: 3px;
            padding-bottom: 3px;
            border-bottom: 1px dashed #ccc;
        }
        .info-section p {
            margin: 2px 0;
            word-break: break-word;
        }
        .products {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3px;
        }
        .products th {
            font-size: 7px;
            padding: 1px;
            border-bottom: 1px solid #000;
            text-align: left;
        }
        .products td {
            padding: 1px;
            vertical-align: top;
            font-size: 8px;
        }
        .product-name {
            word-break: break-word;
            max-width: 0.8in;
        }
        .footer {
            text-align: center;
            font-size: 7px;
            margin-top: 3px;
            padding-top: 3px;
            border-top: 1px dashed #ccc;
        }
        .text-right {
            text-align: right;
        }
        .tfoot-row {
            border-top: 1px dashed #000;
        }
        .logo img {
            max-height: 30px;
            width: auto;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            @if(isset($generalSettings->logo))
                <img src="{{ Storage::url($generalSettings->logo) }}" alt="Hazlye" height="50px">
            @endif
        </div>
        <h3>Order #{{ $order->order_number }}</h3>
    </div>

    <div class="info-section customer-info">
        <p><strong>CUSTOMER:</strong> {{ $order->name }}</p>
        <p><strong>PHONE:</strong> {{ $order->phone }}</p>
        <p><strong>ADDRESS:</strong> {{ $order->address }}, {{ $order->thana }}, {{ $order->district }}</p>
    </div>


    <table class="products">
        <thead>
            <tr>
                <th>ITEM</th>
                <th>SIZE</th>
                <th>QTY</th>
                <th>PRICE</th>
                {{-- <th class="product-img-cell">IMG</th> --}}
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td class="product-name">{{ $item->product_name }}</td>
                <td>{{ $item->size_name ?? '-' }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->price, 2) }}৳</td>
                {{-- <td class="product-img-cell">
                    @if($item->product && $item->product->image)
                        @php
                            $imagePath = storage_path('public/storage/' . $item->product->image);
                        @endphp
                        @if(file_exists($imagePath))
                            <img class="product-img" src="{{ $imagePath }}" alt="{{ $item->product_name }}">
                        @else
                            <span>-</span>
                        @endif
                    @else
                        -
                    @endif
                </td> --}}
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="tfoot-row">
                <td colspan="2"><strong>TOTAL ITEMS</strong></td>
                <td><strong>{{ $order->items->sum('quantity') }}</strong></td>
                <td colspan="2"></td>
            </tr>
            {{-- <tr>
                <td colspan="2"><strong>SUBTOTAL</strong></td>
                <td colspan="2" class="text-right">{{ number_format($order->subtotal, 2) }}৳</td>
            </tr>
            @if($order->discount > 0)
            <tr>
                <td colspan="2"><strong>DISCOUNT</strong></td>
                <td colspan="2" class="text-right">-{{ number_format($order->discount, 2) }}৳</td>
            </tr>
            @endif
            <tr>
                <td colspan="2"><strong>DELIVERY CHARGE</strong></td>
                <td colspan="2" class="text-right">{{ number_format($order->delivery_charge, 2) }}৳</td>
            </tr> --}}
            <tr>
                <td colspan="2"><strong>TOTAL PAYABLE</strong></td>
                <td colspan="2" class="text-right" style="padding-right:10px;">{{ number_format($order->total, 2) }}৳</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Printed: {{ now()->format('d M Y H:i') }}
    </div>
</body>
</html>