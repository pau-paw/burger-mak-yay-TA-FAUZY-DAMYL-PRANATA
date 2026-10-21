<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('admin.dashboard', [
            'revenue' => Order::where('status', 'completed')->where('payment_status', 'paid')->sum('total'),
            'openOrders' => Order::whereIn('status', ['pending', 'processing', 'ready'])->count(),
            'productCount' => Product::count(),
            'customerCount' => User::where('role', 'customer')->count(),
            'orders' => Order::latest()->limit(8)->get(),
            'lowStocks' => Product::where('is_active', true)->where('stock', '<=', 5)->orderBy('stock')->limit(5)->get(),
        ]);
    }
}
