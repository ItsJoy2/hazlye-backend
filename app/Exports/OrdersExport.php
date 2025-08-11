<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrdersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    public function collection()
    {
        return $this->orders->load(['items.product', 'courier']);
    }

    public function headings(): array
    {
        return [
            'Order Number',
            'Customer Name',
            'Phone',
            'Address',
            'District',
            'Thana',
            'Order Date',
            'Status',
            'Total Amount',
            'Delivery Charge',
            'Discount',
            'Courier Name',
            'Tracking ID',
            'Products (Name, Qty, Price)',
            // 'Payment Method',
            // 'IP Address'
        ];
    }

    public function map($order): array
    {
        // Format products information
        $productsInfo = [];
        foreach ($order->items as $item) {
            $productLine = $item->product_name;
            $productLine .= ' (Qty: '.$item->quantity;
            $productLine .= ', Price: '.$item->price.')';

            if ($item->size_name || $item->color_name) {
                $productLine .= ' [';
                if ($item->size_name) $productLine .= 'Size: '.$item->size_name;
                if ($item->size_name && $item->color_name) $productLine .= ', ';
                if ($item->color_name) $productLine .= 'Color: '.$item->color_name;
                $productLine .= ']';
            }

            $productsInfo[] = $productLine;
        }

        return [
            $order->order_number,
            $order->name,
            $order->phone,
            $order->address,
            $order->district,
            $order->thana,
            $order->created_at->format('Y-m-d H:i:s'),
            ucfirst(str_replace('_', ' ', $order->status)),
            $order->total,
            $order->delivery_charge,
            $order->discount,
            $order->courier->name ?? 'N/A',
            $order->tracking_code ?? 'N/A',
            implode("\n", $productsInfo),
            // $order->payment_method,
            // $order->ip_address
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A:Z' => [
                'alignment' => [
                    'wrapText' => true,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP
                ]
            ]
        ];
    }
}