<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Invoice - {{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: #333;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #2d3748;
        }
        .header p {
            margin: 5px 0;
            color: #718096;
        }
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .info-box {
            flex: 1;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 4px;
            margin: 0 10px;
        }
        .info-box h3 {
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            color: #2d3748;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background-color: #f8f9fa;
            text-align: left;
            padding: 10px;
            border: 1px solid #ddd;
        }
        .items-table td {
            padding: 10px;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        .items-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .variant-details {
            margin-top: 5px;
            font-size: 12px;
            color: #666;
        }
        .color-preview {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 5px;
            border: 1px solid #ddd;
            vertical-align: middle;
        }
        .totals-section {
            float: right;
            width: 300px;
            margin-top: 20px;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .totals-table tr:last-child {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #718096;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-processing {
            background-color: #cce5ff;
            color: #004085;
        }
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Order Invoice</h1>
            <p>Order #{{ $order->order_number }}</p>
            <p>Date: {{ $order->created_at->format('F j, Y') }}</p>
            <span class="status-badge status-{{ $order->status }}">
                {{ strtoupper($order->status) }}
            </span>
        </div>

        <div class="info-section">
            <div class="info-box">
                <h3>Customer Information</h3>
                <p><strong>Name:</strong> {{ $order->name }}</p>
                <p><strong>Phone:</strong> {{ $order->phone }}</p>
                <p><strong>Address:</strong> {{ $order->address }}</p>
            </div>

            <div class="info-box">
                <h3>Order Details</h3>
                <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
                <p><strong>Order Date:</strong> {{ $order->created_at->format('F j, Y') }}</p>
                <p><strong>Status:</strong>
                    <span class="status-badge status-{{ $order->status }}">
                        {{ strtoupper($order->status) }}
                    </span>
                </p>
                @if($order->coupon)
                    <p><strong>Coupon:</strong> {{ $order->coupon_code }} ({{ $order->coupon->discount }}% off)</p>
                @endif
            </div>
        </div>

        <h3>Order Items</h3>
        <table class="items-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product Details</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        {{ $item->product_name }}
                        @if($item->size_name)
                            <br><small>Size: {{ $item->size_name }}</small>
                        @endif
                        @if($item->color_name)
                            <br><small>Color: {{ $item->color_name }}</small>
                        @endif
                    </td>
                    <td>{{ number_format($item->price, 2) }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->price * $item->quantity, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td>Subtotal:</td>
                    <td>{{ number_format($order->subtotal, 2) }}</td>
                </tr>
                @if($order->discount > 0)
                <tr>
                    <td>Discount:</td>
                    <td>-{{ number_format($order->discount, 2) }}</td>
                </tr>
                @endif
                <tr>
                    <td>Delivery Charge:</td>
                    <td>{{ number_format($order->delivery_charge, 2) }}</td>
                </tr>
                <tr>
                    <td>Total:</td>
                    <td>{{ number_format($order->total, 2) }}</td>
                </tr>
            </table>
        </div>

        @if($order->comment)
        <div style="margin-top: 20px; clear: both;">
            <h3>Order Notes</h3>
            <p>{{ $order->comment }}</p>
        </div>
        @endif

        <div class="footer">
            <p>Thank you for your order!</p>
            <p>{{ config('app.name') }} - {{ now()->format('Y') }}</p>
        </div>
    </div>
</body>
</html>