<?php

namespace App\Http\Controllers\Admin;

use view;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;


class DashboardController extends Controller
{
    public function index(Request $request)
{
    // Today's date
    $today = Carbon::today();

    // Initialize filter variables
    $dateRange = $request->input('date_range', null);
    $status = $request->input('status', null);

    // Base queries
    $orderQuery = Order::query();
    $productQuery = Product::query();

    // Apply date filter if provided
    if ($dateRange && strpos($dateRange, ' - ') !== false) {
        $dates = explode(' - ', $dateRange);
        try {
            $startDate = Carbon::createFromFormat('Y-m-d', trim($dates[0]))->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', trim($dates[1] ?? $dates[0]))->endOfDay();
            $orderQuery->whereBetween('created_at', [$startDate, $endDate]);
        } catch (\Exception $e) {
            // Handle invalid date format
        }
    }

    // Apply status filter if provided
    if ($status && is_string($status)) {
        $orderQuery->where('status', $status);
    }

    // Calculate stats with proper null checks
    $todayOrders = Order::whereDate('created_at', $today)->count();
    $totalProducts = Product::count();

    $todayProfit = Order::whereDate('created_at', $today)
        ->where('status', '!=', 'cancelled')
        ->sum('total') ?? 0;

    $totalProfit = $orderQuery->clone()
        ->where('status', '!=', 'cancelled')
        ->sum('total') ?? 0;

    $totalOrders = $orderQuery->clone()->count();

    return view('admin.dashboard', [
        'todayOrders' => $todayOrders,
        'totalProducts' => $totalProducts,
        'todayProfit' => $todayProfit,
        'totalProfit' => $totalProfit,
        'totalOrders' => $totalOrders,
        'selectedStatus' => $status,
        'selectedDateRange' => $dateRange,
    ]);
}
}