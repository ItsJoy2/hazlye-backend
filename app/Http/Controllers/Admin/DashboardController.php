<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;


class DashboardController extends Controller
{

 public function index(){

     $dashboardData = [
         'todayOrders' => Order::whereDate('created_at', today())->count(),
         'totalCustomers' => User::count(),
         'totalProducts' => Product::count(),
         'totalCategories' => Category::count(),

         'yesterdayOrder' => Order::where('created_at', today()->subDays(1))->count(),
         'lastWeekOrder' => Order::where('created_at', today()->subDays(7))->count(),

     ];

    return view('admin.dashboard',compact('dashboardData'));
 }

}
