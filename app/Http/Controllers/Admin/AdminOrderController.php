<?php

namespace App\Http\Controllers\Admin;

use id;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Exports\OrdersExport;
use App\Models\CourierService;
use App\Models\DeliveryOption;
use Illuminate\Support\Carbon;
use App\Models\BlockedCustomer;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\CustomersExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\ProductVariantOption;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['items', 'coupon', 'items.variantOption',  'deliveryOption' ])
            ->latest();


        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }


        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('district')) {
            $query->where('district', 'like', '%' . $request->district . '%');
        }

        if ($request->filled('thana')) {
            $query->where('thana', 'like', '%' . $request->thana . '%');
        }

        if ($request->filled('product_search')) {
            $searchTerm = $request->product_search;

            $query->whereHas('items.product', function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('sku', 'like', '%' . $searchTerm . '%');
            });
        }

        $orders = $query->paginate(10);

        $status = $request->status ?? 'all';
        $dateFrom = $request->date_from ?? '';
        $dateTo = $request->date_to ?? '';
        $district = $request->district ?? '';
        $thana = $request->thana ?? '';
        $productSearch = $request->product_search ?? '';


        $couriers = CourierService::all();
        return view('admin.pages.orders.index', compact(
            'orders', 'status', 'dateFrom', 'dateTo', 'district', 'thana', 'productSearch', 'couriers'
        ));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,hold,processing,shipped,courier_delivered,delivered,cancelled',
            'courier_service_id' => 'nullable|required_if:status,shipped|exists:courier_services,id',
            'delivery_note' => 'nullable|string|max:255',
            'comment' => 'nullable|string',
        ]);

        $allowedTransitions = [
            'pending' => ['hold', 'processing', 'cancelled'],
            'hold' => ['processing', 'cancelled'],
            'processing' => ['shipped','courier_delivered', 'cancelled'],
            'shipped' => ['courier_delivered', 'cancelled'],
            'courier_delivered' => ['delivered'],
            'delivered' => [],
            'cancelled' => [],
        ];


        $currentStatus = $order->status;
        $newStatus = $validated['status'];

        if (!in_array($newStatus, $allowedTransitions[$currentStatus])) {
            return back()->with('error', 'Invalid status transition from '.$currentStatus.' to '.$newStatus);
        }

        DB::beginTransaction();

        try {
            if ($validated['status'] === 'shipped' && $order->status === 'shipped') {
                throw new \Exception('This order is already marked as shipped.');
            }

            if ($validated['status'] === 'cancelled' && $order->status !== 'cancelled') {
                $order->returnStock();
            }

            $order->status = $validated['status'];
            if (isset($validated['comment'])) {
                $order->comment = $validated['comment'];
            }

            if ($validated['status'] === 'shipped') {
                $courier = CourierService::findOrFail($validated['courier_service_id']);

                $payload = [
                    'invoice' => $order->order_number,
                    'recipient_name' => $order->name,
                    'recipient_phone' => $order->phone,
                    'recipient_address' => $order->address . ', ' . $order->thana . ', ' . $order->district,
                    'cod_amount' => $order->total,
                    'note' => $validated['delivery_note'] ?? 'Handle with care',
                    'item_description' => 'N/A',
                    'delivery_type' => 0,
                ];

                $response = Http::withHeaders([
                    'Api-Key' => $courier->api_key,
                    'Secret-Key' => $courier->secret_key,
                    'Content-Type' => 'application/json',
                ])->post($courier->base_url . '/' . $courier->create_order_endpoint, $payload);

                $data = $response->json();

                if (!$response->successful() || !isset($data['consignment']['tracking_code'])) {
                    throw new \Exception('Courier API Error: ' . ($data['message'] ?? 'Unknown error'));
                }

                $order->courier_service_id = $courier->id;
                $order->tracking_code = $data['consignment']['tracking_code'];
                $order->consignment_id = $data['consignment']['consignment_id'];
                $order->courier_response = $data;
            }

            $order->save();
            DB::commit();

            return back()->with('success', 'Order updated successfully.' .
                ($order->tracking_code ? ' Tracking: ' . $order->tracking_code : ''));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order status update failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Failed to update order: ' . $e->getMessage());
        }
    }




    public function edit(Order $order)
    {
        $order->load(['items.product', 'coupon', 'deliveryOption', 'items.variantOption']);
        $couriers = CourierService::all();
        $deliveryOptions = DeliveryOption::where('is_active', true)->get();


        return view('admin.pages.orders.edit', compact('order', 'couriers', 'deliveryOptions'));
    }

    public function destroy($id)
{
    $order = Order::findOrFail($id);

    if ($order->status !== 'cancelled') {
        return redirect()->back()->with('error', 'Only cancelled orders can be deleted.');
    }

    $order->delete();

    return redirect()->route('admin.orders.index')->with('success', 'Order deleted successfully.');
}

    public function show(Order $order)
    {
        $order->load(['items.product', 'coupon']);
        $couriers = CourierService::where('is_active', true)->get();

        return view('admin.pages.orders.show', compact('order', 'couriers'));
    }

    public function customerList(Request $request)
    {
        $customerBase = Order::select([
                'phone',
                DB::raw('MIN(name) as name'),
                DB::raw('MIN(created_at) as first_order_at')
            ])
            ->where('status', 'delivered')
            ->groupBy('phone')
            ->orderBy('first_order_at')
            ->get();

        $customerIdMap = [];
        $startingId = 101;

        foreach ($customerBase as $customer) {
            $customerIdMap[$customer->phone] = $startingId++;
        }

        $query = Order::select([
                'name',
                'phone',
                DB::raw('MIN(address) as primary_address'),
                DB::raw('MIN(district) as district'),
                DB::raw('MIN(thana) as thana'),
                DB::raw('COUNT(DISTINCT orders.id) as order_count'),
                DB::raw('SUM(order_items.quantity) as total_products'),
                DB::raw('SUM(orders.total) as total_spent'),
                DB::raw('MAX(orders.created_at) as last_order_at')
            ])
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'delivered')
            ->groupBy('name', 'phone');

        if ($request->filled('phone')) {
            $query->where('phone', 'like', '%' . $request->phone . '%');
        }

        if ($request->filled('district')) {
            $query->where('district', 'like', '%' . $request->district . '%');
        }

        if ($request->filled('thana')) {
            $query->where('thana', 'like', '%' . $request->thana . '%');
        }

        $allCustomers = $query->orderBy('total_spent', 'desc')->get();

        $customersWithIds = $allCustomers->map(function ($customer) use ($customerIdMap) {
            return [
                'customer_id' => $customerIdMap[$customer->phone] ?? null,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'district' => $customer->district,
                'thana' => $customer->thana,
                'primary_address' => $customer->primary_address,
                'order_count' => $customer->order_count,
                'total_products' => $customer->total_products,
                'total_spent' => number_format($customer->total_spent, 2),
                'last_order_at' => Carbon::parse($customer->last_order_at)->format('M d, Y'),
            ];
        })->filter(function ($customer) {
            return !is_null($customer['customer_id']);
        });


        if ($request->filled('search')) {
            $searchTerm = strtolower($request->search);
            $customersWithIds = $customersWithIds->filter(function ($customer) use ($searchTerm) {
                return str_contains(strtolower($customer['name']), $searchTerm) ||
                       str_contains(strtolower($customer['phone']), $searchTerm) ||
                       $customer['customer_id'] == $searchTerm;
            });
        }


        $customersWithIds = $customersWithIds->sortBy('customer_id')->values();


        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20;
        $currentPageItems = $customersWithIds->slice(($page - 1) * $perPage, $perPage)->values();

        $customers = new LengthAwarePaginator(
            $currentPageItems,
            $customersWithIds->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        return view('admin.pages.customers.index', [
            'customers' => $customers,
            'phone' => $request->phone,
            'district' => $request->district,
            'thana' => $request->thana,
            'search' => $request->search,
        ]);
    }



public function download(Order $order)
{
    $order->load([
        'items.product',
        'coupon',
        'courier'
    ]);

    foreach ($order->items as $item) {
        if ($item->product && $item->product->thumbnail) {
            $item->product->thumbnail_path = storage_path('app/public/' . $item->product->thumbnail);
        }
    }

    $pdf = Pdf::loadView('admin.layouts.invoice', compact('order'))
        ->setPaper([0, 0, 144, 9999], 'portrait')
        ->setOption('enable-local-file-access', true)
        ->setOption('defaultFont', 'Noto Sans Bengali');

    return $pdf->download('order-'.$order->order_number.'.pdf');
}

public function update(Request $request, Order $order)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'address' => 'required|string|max:1000',
        'district' => 'required|string',
        'thana'    => 'required|string',
    ]);

    $order->update([
        'name' => $request->name,
        'phone' => $request->phone,
        'address' => $request->address,
        'district' => $request->district,
        'thana'    => $request->thana,
    ]);

    return redirect()
        ->route('admin.orders.edit', $order)
        ->with('success', 'Customer information updated successfully.');
}

public function updateDeliveryCharge(Request $request, Order $order)
{
    $request->validate([
        'delivery_option_id' => 'required|exists:delivery_options,id',
        'delivery_charge' => 'required|numeric|min:0',
        'admin_discount' => 'required|numeric|min:0',
    ]);

    $order->update([
        'delivery_option_id' => $request->delivery_option_id,
        'delivery_charge' => $request->delivery_charge,
        'admin_discount' => $request->admin_discount,
        'total' => max(0, $order->subtotal - $order->discount - $request->admin_discount + $request->delivery_charge)
    ]);

    return back()->with('success', 'Delivery information updated successfully.');
}

public function updateItems(Request $request, Order $order)
{
    if ($request->has('removed_ids')) {
        OrderItem::whereIn('id', $request->removed_ids)
                 ->where('order_id', $order->id)
                 ->delete();
    }

    if ($request->has('items')) {
        foreach ($request->items as $itemData) {
            if (!empty($itemData['id'])) {
                $item = OrderItem::find($itemData['id']);
                if ($item) {
                    $item->quantity = $itemData['quantity'];

                    if (isset($itemData['size'])) {
                        $item->size_name = $itemData['size'];
                    }

                    if (isset($itemData['color'])) {
                        $item->color_name = $itemData['color'];
                    }

                    $item->save();
                }
            }
        }
    }

    if ($request->filled('new_sku')) {
        $sku = $request->new_sku;
        $quantity = $request->new_quantity ?? 1;

        $product = Product::where('sku', $sku)->first();

        if ($product) {
            $order->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price' => $product->discount_price ?? $product->regular_price,
                'quantity' => $quantity,
                'size_name' => $product->size->name ?? null,
                'color_name' => null,
            ]);
        } else {
            $variantOption = ProductVariantOption::where('sku', $sku)->first();

            if ($variantOption) {
                $variant = $variantOption->variant;
                $product = $variant->product;

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $variantOption->price,
                    'quantity' => $quantity,
                    'size_name' => $variantOption->size->name ?? null,
                    'color_name' => $variant->color->name ?? null,
                    'variant_option_id' => $variantOption->id,
                ]);
            }
        }

        if (!$product && !$variantOption) {
            return back()->with('error', 'No product or variant found with SKU: ' . $sku);
        }
    }

    $this->recalculateOrderTotals($order);

    return back()->with('success', 'Order items updated successfully.');
}

protected function recalculateOrderTotals(Order $order)
{
    $subtotal = $order->items()->sum(DB::raw('price * quantity'));
    $order->subtotal = $subtotal;
    $order->total = ($subtotal - $order->discount) + $order->delivery_charge;
    $order->save();
}

public function skuSearch(Request $request)
{
    $query = $request->input('query');

    $products = Product::where('sku', 'like', "%$query%")
        ->select('id', 'sku', 'name', 'regular_price', 'discount_price')
        ->limit(5)
        ->get()
        ->map(function ($product) {
            return [
                'type' => 'product',
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'price' => $product->discount_price ?? $product->regular_price,
                'has_variants' => $product->has_variants
            ];
        });

    $variants = ProductVariantOption::where('sku', 'like', "%$query%")
        ->with(['variant.product', 'variant.color', 'size'])
        ->select('id', 'sku', 'variant_id', 'price', 'size_id')
        ->limit(5)
        ->get()
        ->map(function ($variant) {
            return [
                'type' => 'variant',
                'id' => $variant->id,
                'sku' => $variant->sku,
                'name' => $variant->variant->product->name,
                'product_id' => $variant->variant->product->id,
                'price' => $variant->price,
                'color' => $variant->variant->color->name ?? null,
                'color_code' => $variant->variant->color->code ?? null,
                'size' => $variant->size->name ?? null,
                'has_variants' => true
            ];
        });

    return response()->json([...$products, ...$variants]);
}


public function create()
{
    $deliveryOptions = DeliveryOption::where('is_active', true)->get();
    return view('admin.pages.orders.create', compact('deliveryOptions'));
}

public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string',
        'phone' => 'required|string',
        'district' => 'required|string',
        'thana' => 'required|string',
        'address' => 'nullable|string',
        'delivery_option_id' => 'required|exists:delivery_options,id',
        'products' => 'required|array',
    ]);

    DB::beginTransaction();

    try {
        $delivery = DeliveryOption::find($request->delivery_option_id);
        $subtotal = 0;

        $order = Order::create([
            'order_number' => 'ORD-' . strtoupper(uniqid()),
            'name' => $request->name,
            'phone' => $request->phone,
            'district' => $request->district,
            'thana' => $request->thana,
            'address' => $request->address,
            'delivery_charge' => $delivery->charge,
            'subtotal' => 0,
            'total' => 0,
            'status' => 'pending',
        ]);

        foreach ($request->products as $item) {
            $product = Product::find($item['product_id']);
            $variant = isset($item['variant_option_id']) ? ProductVariantOption::find($item['variant_option_id']) : null;

            $price = $variant ? $variant->price : $product->discount_price ?? $product->regular_price;
            $subtotal += $price * $item['quantity'];

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price' => $price,
                'quantity' => $item['quantity'],
                'size_name' => $variant?->size?->name,
                'color_name' => $variant?->variant?->color?->name,
                'variant_option_id' => $variant?->id,
            ]);

            if ($variant) {
                $variant->decrement('stock', $item['quantity']);
                $product->updateStock();
            } else {
                $product->decrement('total_stock', $item['quantity']);
            }
        }

        $order->subtotal = $subtotal;
        $order->total = $subtotal + $delivery->charge;
        $order->save();

        DB::commit();
        return redirect()->route('admin.orders.index')->with('success', 'Order created successfully.');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Failed: ' . $e->getMessage());
    }
}


public function search(Request $request)
{
    $keyword = $request->q;

    if (!$keyword) {
        return response()->json([]);
    }

    $variantMatch = ProductVariantOption::where('sku', $keyword)->with('variant.product')->first();

    if ($variantMatch) {
        $product = $variantMatch->variant->product;
        return response()->json([
            [
                'type' => 'variant',
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $variantMatch->sku,
                'price' => $variantMatch->price,
                'has_variants' => true,
            ]
        ]);
    }

    $products = Product::where('name', 'LIKE', "%$keyword%")
        ->orWhere('sku', 'LIKE', "%$keyword%")
        ->limit(10)
        ->get()
        ->map(function ($product) {
            return [
                'type' => 'product',
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->discount_price ?? $product->regular_price,
                'has_variants' => $product->has_variants,
            ];
        });

    return response()->json($products);
}


public function getVariants(Product $product)
{
    $variants = $product->variants()
        ->with(['color', 'options.size'])
        ->get()
        ->flatMap(function ($variant) {
            return $variant->options->map(function ($option) use ($variant) {
                return [
                    'id' => $option->id,
                    'sku' => $option->sku,
                    'price' => $option->price,
                    'color' => $variant->color->name ?? null,
                    'size' => $option->size->name ?? null,
                ];
            });
        });

    return response()->json($variants);
}

public function shippedOrders(Request $request)
{
    $query = Order::with(['items', 'coupon', 'items.variantOption', 'deliveryOption', 'courier'])
        ->whereIn('status', ['shipped', 'courier_delivered', 'delivered'])
        ->latest();

    if ($request->filled('date_from')) {
        $query->whereDate('created_at', '>=', $request->date_from);
    }

    if ($request->filled('date_to')) {
        $query->whereDate('created_at', '<=', $request->date_to);
    }

    // Filter by courier
    if ($request->filled('courier_service_id')) {
        $query->where('courier_service_id', $request->courier_service_id);
    }

    // Filter by tracking code
    if ($request->filled('tracking_code')) {
        $query->where('tracking_code', 'like', '%' . $request->tracking_code . '%');
    }

    $orders = $query->paginate(10);

    $dateFrom = $request->date_from ?? '';
    $dateTo = $request->date_to ?? '';
    $courierServiceId = $request->courier_service_id ?? '';
    $trackingCode = $request->tracking_code ?? '';

    $couriers = CourierService::all();

    return view('admin.pages.orders.courier_order', compact(
        'orders',
        'dateFrom',
        'dateTo',
        'courierServiceId',
        'trackingCode',
        'couriers'
    ));
}


public function blockedCustomers(Request $request)
{
    $query = BlockedCustomer::query()->latest();

    if ($request->filled('search')) {
        $searchTerm = $request->search;
        $query->where(function($q) use ($searchTerm) {
            $q->where('phone', 'like', '%' . $searchTerm . '%')
              ->orWhere('ip_address', 'like', '%' . $searchTerm . '%');
        });
    }

    $blockedCustomers = $query->paginate(10)
        ->appends(['search' => $request->search]);

    return view('admin.pages.customers.customer_block', [
        'blockedCustomers' => $blockedCustomers,
        'search' => $request->search
    ]);
}
public function blockCustomer(Request $request)
{
    $request->validate([
        'phone' => 'required_without:ip_address',
        'ip_address' => 'required_without:phone',
        'reason' => 'nullable|string'
    ]);

    BlockedCustomer::create([
        'phone' => $request->phone,
        'ip_address' => $request->ip_address,
        'reason' => $request->reason,
    ]);

    return back()->with('success', 'Customer blocked successfully');
}

public function unblockCustomer(Request $request)
{
    $request->validate([
        'id' => 'required|exists:blocked_customers'
    ]);

    BlockedCustomer::find($request->id)->delete();

    return back()->with('success', 'Customer unblocked successfully');
}
public function export(Request $request)
{
    $request->validate([
        'order_ids' => 'required|array',
        'order_ids.*' => 'exists:orders,id',
    ]);

    $orders = Order::whereIn('id', $request->order_ids)
        ->whereIn('status', ['processing', 'shipped', 'courier_delivered', 'delivered'])
        ->when($request->status !== 'all', function($query) use ($request) {
            $query->where('status', $request->status);
        })
        ->when($request->date_from, function($query) use ($request) {
            $query->whereDate('created_at', '>=', $request->date_from);
        })
        ->when($request->date_to, function($query) use ($request) {
            $query->whereDate('created_at', '<=', $request->date_to);
        })
        ->with(['items.product', 'courier'])
        ->orderBy('created_at', 'desc')
        ->get();

    if ($orders->isEmpty()) {
        return back()->with('error', 'No valid orders selected for export.');
    }

    $fileName = 'orders-export-' . now()->format('Y-m-d-H-i-s') . '.xlsx';

    return Excel::download(
        new OrdersExport($orders, $request->status, $request->date_from, $request->date_to),
        $fileName
    );
}

public function bulkDelete(Request $request)
{
    $request->validate([
        'order_ids' => 'required|array|min:1',
        'order_ids.*' => 'exists:orders,id',
    ]);

    // cancelled এবং incomplete অর্ডার ডিলিট করার জন্য
    $orders = Order::whereIn('id', $request->order_ids)
        ->whereIn('status', ['cancelled', 'incomplete'])
        ->get();

    if ($orders->isEmpty()) {
        return response()->json([
            'status' => false,
            'message' => 'Only cancelled or incomplete orders can be deleted.'
        ], 400);
    }

    $deletedCount = 0;
    foreach ($orders as $order) {
        try {
            $order->delete();
            $deletedCount++;
        } catch (\Exception $e) {
            continue;
        }
    }

    return response()->json([
        'status' => true,
        'message' => "Successfully deleted $deletedCount order(s)."
    ]);
}


public function exportCustomers(Request $request)
{
    $exportType = $request->export_type;
    $filters = [
        'phone' => $request->phone_filter,
        'district' => $request->district_filter,
        'thana' => $request->thana_filter,
        'search' => $request->search_filter,
    ];

    if ($exportType === 'selected') {
        $selectedPhones = $request->selected_customers ?? [];
        return Excel::download(new CustomersExport($selectedPhones, $filters), 'selected_customers.xlsx');
    }

    return Excel::download(new CustomersExport(null, $filters), 'all_customers.xlsx');
}

public function incompleteOrders(Request $request)
    {
        $orders = Order::where('status', 'incomplete')
            ->with('items', 'deliveryOption')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.pages.orders.incomplete_order', compact('orders'));
    }


}