<?php

namespace App\Http\Controllers\Admin;

use id;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Models\CourierService;
use App\Models\DeliveryOption;
use Illuminate\Support\Carbon;
use App\Models\BlockedCustomer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\ProductVariantOption;
use Illuminate\Support\Facades\Http;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['items', 'coupon', 'items.variantOption',  'deliveryOption' ])
            ->latest();


        // Status filter
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by district
        if ($request->filled('district')) {
            $query->where('district', 'like', '%' . $request->district . '%');
        }

        // Filter by thana
        if ($request->filled('thana')) {
            $query->where('thana', 'like', '%' . $request->thana . '%');
        }

        // Filter by product name or SKU from order items
        if ($request->filled('product_search')) {
            $searchTerm = $request->product_search;

            $query->whereHas('items.product', function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')  // Assuming product name is in `products.name`
                  ->orWhere('sku', 'like', '%' . $searchTerm . '%'); // `products.sku`
            });
        }

        $orders = $query->paginate(10);

        // Preserve filter values
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
            'status' => 'required|string|in:pending,processing,shipped,delivered,cancelled,hold,courier_delivered',
            'courier_service_id' => 'nullable|required_if:status,shipped|exists:courier_services,id',
            'delivery_note' => 'nullable|string|max:255',
            'comment' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // Prevent double shipping
            if ($validated['status'] === 'shipped' && $order->status === 'shipped') {
                throw new \Exception('This order is already marked as shipped.');
            }

            // Return stock if cancelled
            if ($validated['status'] === 'cancelled' && $order->status !== 'cancelled') {
                $order->returnStock();
            }

            // Update status and comment
            $order->status = $validated['status'];
            if (isset($validated['comment'])) {
                $order->comment = $validated['comment'];
            }

            // If shipped, call courier API
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

                // Save courier tracking info
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


        return view('admin.pages.orders.edit', compact('order', 'couriers'));
    }

    public function destroy($id)
{
    $order = Order::findOrFail($id);

    // Allow only if status is cancelled
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
    ->join('order_items', 'orders.id', '=', 'order_items.order_id')->where('orders.status', 'delivered')->groupBy('name', 'phone');


    if ($request->filled('phone')) {
        $query->where('phone', 'like', '%' . $request->phone . '%');
    }

    if ($request->filled('district')) {
        $query->where('district', 'like', '%' . $request->district . '%');
    }

    if ($request->filled('thana')) {
        $query->where('thana', 'like', '%' . $request->thana . '%');
    }

    $customers = $query
        ->orderBy('total_spent', 'desc')
        ->paginate(20);

    // Format output
    $customers->getCollection()->transform(function ($customer) {
        return [
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
    });

    return view('admin.pages.customers.index', [
        'customers' => $customers,
        'phone' => $request->phone,
        'district' => $request->district,
        'thana' => $request->thana,
    ]);
}



    public function download(Order $order)
{
     $order->load([
        'items.product',
        'coupon'
    ]);

    $pdf = Pdf::loadView('admin.layouts.invoice', compact('order'));

    return $pdf->download('invoice-'.$order->order_number.'.pdf');
}
public function update(Request $request, Order $order)
{
    // Validate input
    $request->validate([
        'name' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'address' => 'required|string|max:1000',
        'district' => 'required|string',
        'thana'    => 'required|string',
    ]);

    // Update order details
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
        'delivery_charge' => 'required|numeric|min:0',
        'admin_discount' => 'required|numeric|min:0',
    ]);

    $order->delivery_charge = $request->delivery_charge;
    $order->admin_discount = $request->admin_discount;

    $order->total = max(0, $order->subtotal - $order->discount - $order->admin_discount + $order->delivery_charge);

    $order->save();

    return back()->with('success', 'Delivery charge & discount updated successfully.');
}
public function updateItems(Request $request, Order $order)
{
    // Remove deleted items
    if ($request->has('removed_ids')) {
        OrderItem::whereIn('id', $request->removed_ids)
                 ->where('order_id', $order->id)
                 ->delete();
    }


    // Update existing items
    if ($request->has('items')) {
        foreach ($request->items as $itemData) {
            if (!empty($itemData['id'])) {
                $item = OrderItem::find($itemData['id']);
                if ($item) {
                    // Update quantity
                    $item->quantity = $itemData['quantity'];

                    // Update size if changed
                    if (isset($itemData['size'])) {
                        $item->size_name = $itemData['size'];
                    }

                    // Update color if changed
                    if (isset($itemData['color'])) {
                        $item->color_name = $itemData['color'];
                    }

                    $item->save();
                }
            }
        }
    }

    // Add new product by SKU (either product SKU or variant option SKU)
    if ($request->filled('new_sku')) {
        $sku = $request->new_sku;
        $quantity = $request->new_quantity ?? 1;

        // First try to find by product SKU
        $product = Product::where('sku', $sku)->first();

        if ($product) {
            // Product found by main SKU
            $order->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price' => $product->discount_price ?? $product->regular_price,
                'quantity' => $quantity,
                'size_name' => $product->size->name ?? null,
                'color_name' => null,
            ]);
        } else {
            // If not found as product, try as variant option SKU
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

    // Recalculate order totals
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

    // Non-variant products
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

    // Variant options
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

            // stock decrement
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

    // First check if variant SKU matches
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

    // If no variant SKU found, search product by name or SKU
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

    // Date range filter
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

    // Preserve filter values
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


}