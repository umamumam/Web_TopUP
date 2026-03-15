<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = config('services.midtrans.is_sanitized');
        Config::$is3ds = config('services.midtrans.is_3ds');
    }

    public function createSnapToken($params)
    {
        return Snap::getSnapToken($params);
    }

    public function notification()
    {
        return new Notification();
    }

    public function getTransactionStatus($orderId)
    {
        return \Midtrans\Transaction::status($orderId);
    }
}
