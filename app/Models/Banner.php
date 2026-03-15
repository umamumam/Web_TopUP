<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = ['title', 'image', 'image_url', 'link', 'order', 'is_active'];
}
