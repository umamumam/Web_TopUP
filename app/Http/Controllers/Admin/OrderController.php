<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['game', 'product'])
            ->when($request->search, function($query, $search) {
                $query->where('reference_id', 'LIKE', "%{$search}%")
                      ->orWhere('target_id', 'LIKE', "%{$search}%")
                      ->orWhere('whatsapp_number', 'LIKE', "%{$search}%");
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/orders/index', [
            'orders' => $orders,
            'filters' => $request->only(['search'])
        ]);
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return redirect()->back()->with('success', 'Pesanan berhasil dihapus');
    }
}
