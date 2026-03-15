<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = ['name', 'code', 'type', 'fee_flat', 'fee_percent', 'image', 'is_active'];
}
