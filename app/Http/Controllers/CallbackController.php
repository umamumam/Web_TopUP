<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\DigiflazzService;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CallbackController extends Controller
{
    public function midtrans(Request $request, MidtransService $midtrans, DigiflazzService $digiflazz)
    {
        try {
            $notification = $midtrans->notification();
            $transaction = $notification->transaction_status;
            $type = $notification->payment_type;
            $orderId = $notification->order_id;
            $fraud = $notification->fraud_status;

            $order = Order::with('product')->where('reference_id', $orderId)->first();

            if (!$order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            if ($transaction == 'capture') {
                if ($type == 'credit_card') {
                    if ($fraud == 'challenge') {
                        $order->update(['payment_status' => 'pending']);
                    } else {
                        $this->processPaidOrder($order, $digiflazz);
                    }
                }
            } else if ($transaction == 'settlement') {
                $this->processPaidOrder($order, $digiflazz);
            } else if ($transaction == 'pending') {
                $order->update(['payment_status' => 'pending']);
            } else if ($transaction == 'deny') {
                $order->update(['payment_status' => 'failed', 'status' => 'canceled']);
            } else if ($transaction == 'expire') {
                $order->update(['payment_status' => 'expired', 'status' => 'canceled']);
            } else if ($transaction == 'cancel') {
                $order->update(['payment_status' => 'canceled', 'status' => 'canceled']);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Midtrans Callback Error: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function processPaidOrder($order, $digiflazz)
    {
        if ($order->payment_status === 'paid') return;

        $order->update([
            'payment_status' => 'paid',
            'status' => 'processing'
        ]);

        // Place Order to Digiflazz
        $response = $digiflazz->placeOrder(
            $order->product->sku,
            $order->target_id,
            $order->reference_id,
            $order->server_id
        );

        if (isset($response['data'])) {
            $data = $response['data'];
            $order->update([
                'status' => $data['status'], // pending, success, failure
                'sn' => $data['sn'] ?? null,
                'provider_payload' => json_encode($data)
            ]);
        } else {
            Log::error('Digiflazz Order Failed: ' . json_encode($response));
        }
    }

    public function digiflazz(Request $request)
    {
        $payload = $request->getContent();
        $data = json_decode($payload, true);
        
        if (!$data) {
            return response()->json(['success' => false, 'message' => 'Invalid JSON'], 400);
        }

        // Detect format: Official Digiflazz (nested in 'data') or Flat (from screenshot)
        if (isset($data['data'])) {
            $item = $data['data'];
            $refId = $item['ref_id'] ?? null;
            $status = $item['status'] ?? null;
            $sn = $item['sn'] ?? null;
        } else {
            // Flat format from screenshot
            $refId = $data['invoice_number'] ?? ($data['reference_id'] ?? null);
            $status = strtolower($data['status_code'] ?? '');
            $sn = $data['voucher'] ?? ($data['sn'] ?? null);
        }

        if ($refId) {
            $order = Order::where('reference_id', $refId)->first();
            
            if ($order) {
                $itemNickname = $data['nickname'] ?? null;
                
                // Map status constants if needed
                if ($status === 'success') $status = 'success';
                if ($status === 'failed' || $status === 'failure') $status = 'failed';

                $order->update([
                    'status' => $status,
                    'sn' => $sn ?? $order->sn,
                    'nickname' => $itemNickname ?? $order->nickname,
                    'provider_payload' => $payload
                ]);
            }
        }

        return response()->json(['success' => true]);
    }
}
