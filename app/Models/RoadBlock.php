<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoadBlock extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
