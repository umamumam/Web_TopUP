<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DigiflazzService
{
    protected $username;
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->username = config('services.digiflazz.username');
        $this->apiKey = config('services.digiflazz.api_key');
        $this->baseUrl = config('services.digiflazz.mode') === 'production' 
            ? 'https://api.digiflazz.com/v1' 
            : 'https://api.digiflazz.com/v1'; // Digiflazz uses same URL, different credentials or behavior based on account
    }

    public function getBalance()
    {
        $sign = md5($this->username . $this->apiKey . 'depo');
        
        $response = Http::post($this->baseUrl . '/cek-saldo', [
            'username' => $this->username,
            'sign' => $sign
        ]);

        return $response->json();
    }

    public function getPriceList()
    {
        $sign = md5($this->username . $this->apiKey . 'pricelist');
        
        $response = Http::post($this->baseUrl . '/price-list', [
            'username' => $this->username,
            'sign' => $sign,
            'cmd' => 'prepaid'
        ]);

        return $response->json();
    }

    public function placeOrder($sku, $target, $refId, $server = null)
    {
        $customerNo = $server ? $target . $server : $target;
        $sign = md5($this->username . $this->apiKey . $refId);

        $response = Http::post($this->baseUrl . '/transaction', [
            'username' => $this->username,
            'buyer_sku_code' => $sku,
            'customer_no' => $customerNo,
            'ref_id' => $refId,
            'sign' => $sign
        ]);

        return $response->json();
    }

    public function checkId($target, $sku)
    {
        $refId = 'CHECK-' . time();
        $sign = md5($this->username . $this->apiKey . $refId);

        $payload = [
            'username' => $this->username,
            'buyer_sku_code' => $sku,
            'customer_no' => $target,
            'ref_id' => $refId,
            'sign' => $sign,
            'commands' => 'inquiry-game'
        ];

        Log::info('[Digiflazz] checkId REQUEST', [
            'url' => $this->baseUrl . '/transaction',
            'sku' => $sku,
            'customer_no' => $target,
            'ref_id' => $refId,
            'sign' => $sign,
        ]);

        try {
            $response = Http::timeout(15)->post($this->baseUrl . '/transaction', $payload);
            $json = $response->json();
            Log::info('[Digiflazz] checkId RESPONSE', [
                'http_status' => $response->status(),
                'body' => $json,
            ]);
            return $json;
        } catch (\Exception $e) {
            Log::error('[Digiflazz] checkId ERROR', ['message' => $e->getMessage()]);
            return null;
        }
    }
}
