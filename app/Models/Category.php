<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
