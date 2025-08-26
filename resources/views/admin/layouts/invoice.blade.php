<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sohoj Kroy Invoice - Order #{{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            padding: 20px;
            display: flex;
            justify-content: center;
        }
        
        .invoice-container {
            width: 100%;
            max-width: 400px;
            background-color: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .invoice-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .invoice-header h1 {
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .order-info {
            margin-bottom: 10px;
            text-align: center;
            font-size: 14px;
        }
        
        .courier-info {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .courier-info p {
            margin: 3px 0;
        }
        
        .invoice-to {
            margin-bottom: 15px;
        }
        
        .invoice-to h2 {
            font-size: 16px;
            margin-bottom: 5px;
            text-decoration: underline;
        }
        
        .invoice-to p {
            margin: 3px 0;
        }
        
        .address {
            margin-top: 10px;
            font-style: italic;
        }
        
        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .product-table th, .product-table td {
            padding: 8px 5px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .product-table th {
            font-weight: bold;
            border-bottom: 2px solid #333;
        }
        
        .product-table td:last-child, 
        .product-table th:last-child {
            text-align: right;
        }
        
        .totals {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 2px solid #333;
        }
        
        .totals p {
            margin: 5px 0;
            display: flex;
            justify-content: space-between;
        }
        
        .due-amount {
            margin-top: 20px;
            font-weight: bold;
            font-size: 18px;
            border-top: 2px dashed #333;
            padding-top: 10px;
        }
        
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        @media print {
            body {
                padding: 0;
                background-color: white;
            }
            
            .invoice-container {
                box-shadow: none;
                max-width: 100%;
                padding: 15px;
            }
            
            .invoice-header h1 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <h1>Sohoj kroy</h1>
            <div class="order-info">Order #{{ $order->order_number }}</div>
        </div>
        
        <div class="courier-info">
            <p><strong>Courier:</strong> {{ $order->courier->name ?? 'mohasagor' }}</p>
            <p><strong>Delivery ID:</strong> {{ $order->tracking_code ?? $order->consignment_id ?? 'N/A' }}</p>
        </div>
        
        <div class="invoice-to">
            <h2>Invoice To:</h2>
            <p><strong>{{ $order->name }}</strong></p>
            <p>{{ $order->phone }}</p>
            
            <div class="address">
                <p>- <strong>Address:</strong> {{ $order->address }},</p>
                <p>{{ $order->thana }}, {{ $order->district }}</p>
            </div>
        </div>
        
        <table class="product-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->price, 2) }}৳</td>
                    <td>{{ number_format($item->price * $item->quantity, 2) }}৳</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="totals">
            <p><span>Sub Total</span> <span>{{ number_format($order->subtotal, 2) }}৳</span></p>
            <p><span>Delivery Fee</span> <span>{{ number_format($order->delivery_charge, 2) }}৳</span></p>
            @if($order->discount > 0)
            <p><span>Discount</span> <span>-{{ number_format($order->discount, 2) }}৳</span></p>
            @endif
        </div>
        
        <div class="due-amount">
            <p><strong>Due Amount</strong></p>
            <p>{{ number_format($order->total, 2) }}৳</p>
        </div>
        
        <div class="footer">
            <p>Printed: {{ now()->format('d M Y H:i') }}</p>
            <p>Thank you for your business!</p>
        </div>
    </div>
</body>
</html>