<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'reference_id', 'game_id', 'product_id', 'payment_method_id', 
        'target_id', 'server_id', 'nickname', 'whatsapp_number', 
        'amount', 'fee', 'total_amount', 
        'status', 'payment_status', 'sn', 'provider_payload'
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
