<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Game;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_sales' => Order::where('payment_status', 'paid')->sum('total_amount'),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'success_orders' => Order::where('status', 'success')->count(),
            'total_games' => Game::count(),
            'total_products' => Product::count(),
        ];

        $recent_orders = Order::with(['game', 'product'])
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentOrders' => $recent_orders
        ]);
    }
}
