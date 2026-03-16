<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Game;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Banner;
use App\Models\Setting;
use App\Services\MidtransService;
use App\Services\DigiflazzService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Support\Str;

class TopupController extends Controller
{
    public function index()
    {
        $categories = Category::with(['games' => function($q) {
            $q->where('is_active', true);
        }])->get();

        $paymentMethods = PaymentMethod::where('is_active', true)->get();
        $banners = Banner::where('is_active', true)->orderBy('order')->get();
        $popularGames = Game::where('is_active', true)->where('is_popular', true)->get();
        
        $settings = Setting::whereIn('key', [
            'social_facebook', 
            'social_instagram', 
            'social_youtube', 
            'social_whatsapp',
            'footer_description',
            'site_name'
        ])->get()->pluck('value', 'key');

        return Inertia::render('Welcome', [
            'categories' => $categories,
            'paymentMethods' => $paymentMethods,
            'banners' => $banners,
            'popularGames' => $popularGames,
            'settings' => $settings
        ]);
    }

    public function show($slug)
    {
        $game = Game::with(['products' => function($q) {
            $q->where('is_active', true)->orderBy('price', 'asc');
        }])->where('slug', $slug)->firstOrFail();

        $paymentMethods = PaymentMethod::where('is_active', true)->get();

        return Inertia::render('topup/show', [
            'game' => $game,
            'paymentMethods' => $paymentMethods
        ]);
    }

    public function store(Request $request, MidtransService $midtransService)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'target_id' => 'required|string',
            'server_id' => 'nullable|string',
            'nickname' => 'nullable|string',
            'whatsapp_number' => 'required|string',
        ]);

        $product = Product::with('game')->find($request->product_id);
        $paymentMethod = PaymentMethod::find($request->payment_method_id);

        $amount = $product->price;
        $fee = $paymentMethod->fee_flat + ($amount * ($paymentMethod->fee_percent / 100));
        $totalAmount = $amount + $fee;

        $referenceId = 'TRX-' . strtoupper(Str::random(10));

        $order = Order::create([
            'reference_id' => $referenceId,
            'game_id' => $product->game_id,
            'product_id' => $product->id,
            'payment_method_id' => $paymentMethod->id,
            'target_id' => $request->target_id,
            'server_id' => $request->server_id,
            'nickname' => $request->nickname,
            'whatsapp_number' => $request->whatsapp_number,
            'amount' => $amount,
            'fee' => $fee,
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        // Create Midtrans Snap Token
        $params = [
            'transaction_details' => [
                'order_id' => $referenceId,
                'gross_amount' => (int) $totalAmount,
            ],
            'customer_details' => [
                'first_name' => $request->target_id,
                'phone' => $request->whatsapp_number,
            ],
            'item_details' => [
                [
                    'id' => $product->sku,
                    'price' => (int) $totalAmount,
                    'quantity' => 1,
                    'name' => $product->game->name . ' - ' . $product->name,
                ]
            ],
        ];

        try {
            $snapToken = $midtransService->createSnapToken($params);
            return response()->json([
                'success' => true,
                'snap_token' => $snapToken,
                'reference_id' => $referenceId
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function checkStatus($reference_id)
    {
        $order = Order::with(['game', 'product', 'paymentMethod'])->where('reference_id', $reference_id)->firstOrFail();

        return Inertia::render('topup/status', [
            'order' => $order
        ]);
    }

    public function refreshStatus($reference_id, MidtransService $midtrans, DigiflazzService $digiflazz)
    {
        $this->syncOrder($reference_id, $midtrans, $digiflazz);
        return back();
    }

    public function getAjaxStatus($reference_id, MidtransService $midtrans, DigiflazzService $digiflazz)
    {
        $order = $this->syncOrder($reference_id, $midtrans, $digiflazz);
        return response()->json([
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'sn' => $order->sn,
            'nickname' => $order->nickname
        ]);
    }

    private function syncOrder($reference_id, $midtrans, $digiflazz)
    {
        $order = Order::with(['product'])->where('reference_id', $reference_id)->firstOrFail();
        $justPaid = false;

        // 1. Check Midtrans if not paid
        if ($order->payment_status !== 'paid') {
            try {
                $status = (object) $midtrans->getTransactionStatus($reference_id);
                $transaction = $status->transaction_status;
                if ($transaction == 'settlement' || $transaction == 'capture') {
                    // This will trigger the FIRST Digiflazz order
                    app(CallbackController::class)->processPaidOrder($order, $digiflazz);
                    $order->refresh();
                    $justPaid = true;
                }
            } catch (\Exception $e) {
                Log::error('Sync Midtrans Error: ' . $e->getMessage());
            }
        }

        // 2. Check Digiflazz ONLY IF it was already paid before this sync cycle
        // and status is still not final (success/failed)
        if (!$justPaid && $order->payment_status === 'paid' && !in_array($order->status, ['success', 'failed'])) {
            try {
                $response = $digiflazz->checkStatus(
                    $order->product->sku,
                    $order->target_id,
                    $order->reference_id,
                    $order->server_id
                );

                if (isset($response['data'])) {
                    $item = $response['data'];
                    // Map status
                    $newStatus = strtolower($item['status'] ?? 'pending');
                    if ($newStatus === 'sukses' || $newStatus === 'success') $newStatus = 'success';
                    if ($newStatus === 'gagal' || $newStatus === 'failed') $newStatus = 'failed';

                    $order->update([
                        'status' => $newStatus,
                        'sn' => $item['sn'] ?? $order->sn,
                        'provider_payload' => json_encode($item)
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Sync Digiflazz Error: ' . $e->getMessage());
            }
        }

        return $order;
    }

    public function validateId(Request $request, DigiflazzService $digiflazz)
    {
        $request->validate([
            'game_slug' => 'required',
            'target_id' => 'required',
            'server_id' => 'nullable',
        ]);

        $game = Game::where('slug', $request->game_slug)->first();
        if (!$game) return response()->json(['success' => false, 'message' => 'Game tidak ditemukan']);

        $product = Product::where('game_id', $game->id)->first();
        if (!$product) return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan']);

        // Gabungkan target_id dan server_id
        $customerNo = $request->server_id 
            ? $request->target_id . $request->server_id 
            : $request->target_id;

        Log::info('[ValidateId] Checking', [
            'game' => $game->slug,
            'sku' => $product->sku,
            'customer_no' => $customerNo,
        ]);

        $response = $digiflazz->checkId($customerNo, $product->sku);

        // Handle null (timeout/error)
        if (is_null($response)) {
            return response()->json(['success' => false, 'message' => 'Koneksi ke provider gagal, coba lagi.']);
        }

        if (isset($response['data'])) {
            $data = $response['data'];
            Log::info('[ValidateId] Data', $data);

            // rc '00' = sukses
            if (isset($data['rc']) && $data['rc'] === '00') {
                return response()->json([
                    'success' => true,
                    'username' => $data['customer_name'] ?? 'Username ditemukan'
                ]);
            }

            // rc lain = error
            $msg = $data['message'] ?? ($data['msg'] ?? 'ID tidak valid atau layanan sedang gangguan');
            return response()->json(['success' => false, 'message' => $msg]);
        }

        // Response tidak ada 'data' key
        Log::warning('[ValidateId] Unexpected response', $response ?? []);
        return response()->json(['success' => false, 'message' => 'Response tidak dikenali dari provider']);
    }
}
