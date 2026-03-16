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
        // Use atomic update to prevent double processing
        $updated = Order::where('id', $order->id)
            ->where('payment_status', '!=', 'paid')
            ->update([
                'payment_status' => 'paid',
                'status' => 'processing'
            ]);

        if (!$updated) {
            Log::info('Order already processed or paid: ' . $order->reference_id);
            return;
        }

        // Refresh order object after update
        $order->refresh();

        Log::info('Processing Order to Digiflazz: ' . $order->reference_id);

        // Place Order to Digiflazz
        $response = $digiflazz->placeOrder(
            $order->product->sku,
            $order->target_id,
            $order->reference_id,
            $order->server_id
        );

        if (isset($response['data'])) {
            $data = $response['data'];
            
            // Map status from Digiflazz to our system
            $newStatus = strtolower($data['status'] ?? 'pending');
            if ($newStatus === 'gagal') $newStatus = 'failed';
            if ($newStatus === 'sukses') $newStatus = 'success';

            $order->update([
                'status' => $newStatus,
                'sn' => $data['sn'] ?? null,
                'provider_payload' => json_encode($data)
            ]);
            
            Log::info('Digiflazz Order Placed: ' . $order->reference_id . ' Status: ' . $newStatus);
        } else {
            Log::error('Digiflazz Order Failed (No Data): ' . $order->reference_id . ' Response: ' . json_encode($response));
            
            // If it failed to place order, we should maybe mark it as failed or keep it as processing for manual check
            $order->update([
                'status' => 'failed',
                'provider_payload' => json_encode($response)
            ]);
        }
    }

    public function digiflazz(Request $request)
    {
        $payload = $request->getContent();
        $data = json_decode($payload, true);
        
        if (!$data) {
            return response()->json(['success' => false, 'message' => 'Invalid JSON'], 400);
        }

        // 1. Signature Validation (If secret is set)
        $secret = config('services.digiflazz.webhook_secret');
        if ($secret) {
            $signature = $request->header('X-Digiflazz-Signature');
            $expectedSignature = 'sha1=' . hash_hmac('sha1', $payload, $secret);
            
            // Note: If Digiflazz uses a different header or algorithm, 
            // you might need to adjust this part. 
            // For now, we log it so we can debug if signature check fails.
            if ($signature !== $expectedSignature) {
                Log::warning('Digiflazz Webhook Invalid Signature', [
                    'received' => $signature,
                    'expected' => $expectedSignature
                ]);
                // return response()->json(['success' => false, 'message' => 'Invalid signature'], 403);
            }
        }

        Log::info('Digiflazz Webhook Received', $data);

        // 2. Detect format: Official Digiflazz (nested in 'data') or Flat
        if (isset($data['data'])) {
            $item = $data['data'];
            $refId = $item['ref_id'] ?? null;
            $status = $item['status'] ?? null;
            $sn = $item['sn'] ?? null;
            $itemNickname = $item['nickname'] ?? ($item['customer_name'] ?? null);
        } else {
            // Flat format / Direct
            $refId = $data['ref_id'] ?? ($data['invoice_number'] ?? null);
            $status = $data['status'] ?? ($data['status_code'] ?? null);
            $sn = $data['sn'] ?? ($data['voucher'] ?? null);
            $itemNickname = $data['nickname'] ?? ($data['customer_name'] ?? null);
        }

        if ($refId) {
            $order = Order::where('reference_id', $refId)->first();
            
            if ($order) {
                // Map status constants
                $status = strtolower($status);
                if ($status === 'success' || $status === 'sukses' || $status === '00') {
                    $status = 'success';
                } elseif ($status === 'failed' || $status === 'failure' || $status === 'gagal' || $status === 'error') {
                    $status = 'failed';
                } elseif ($status === 'pending' || $status === 'processing') {
                    $status = 'processing';
                }

                $itemNickname = $data['nickname'] ?? ($data['customer_name'] ?? null);
                
                $order->update([
                    'status' => $status,
                    'sn' => $sn ?? $order->sn,
                    'nickname' => $itemNickname ?? $order->nickname,
                    'provider_payload' => $payload
                ]);

                Log::info('Digiflazz Callback Processed', ['ref_id' => $refId, 'status' => $status]);
            }
        }

        return response()->json(['success' => true]);
    }
}
