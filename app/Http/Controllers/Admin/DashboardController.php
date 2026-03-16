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
    public function index(\App\Services\DigiflazzService $digiflazz)
    {
        $stats = [
            'total_sales' => Order::where('payment_status', 'paid')->sum('total_amount'),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'success_orders' => Order::where('status', 'success')->count(),
            'total_games' => Game::count(),
            'total_products' => Product::count(),
            'digiflazz_balance' => $digiflazz->getBalance()['data']['deposit'] ?? 0,
        ];

        // Monthly Data (Last 12 Months)
        $monthly_sales = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subYear())
            ->selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, SUM(total_amount) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Weekly Data (Last 8 Weeks)
        $weekly_sales = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subWeeks(8))
            ->selectRaw('WEEK(created_at) as week, YEAR(created_at) as year, SUM(total_amount) as total')
            ->groupBy('year', 'week')
            ->orderBy('year')
            ->orderBy('week')
            ->get();

        // Daily Data (Last 7 Days) for the side list
        $daily_sales = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        $recent_orders = Order::with(['game', 'product'])
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentOrders' => $recent_orders,
            'chartData' => [
                'monthly' => $monthly_sales,
                'weekly' => $weekly_sales,
                'daily' => $daily_sales
            ]
        ]);
    }
}
