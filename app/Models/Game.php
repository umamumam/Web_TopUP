<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = ['category_id', 'name', 'slug', 'thumbnail', 'banner', 'description', 'input_label', 'has_server', 'is_popular', 'is_active'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
