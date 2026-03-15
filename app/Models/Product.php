<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['game_id', 'name', 'sku', 'price', 'original_price', 'is_active'];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
