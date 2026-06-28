<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HospitalNeed extends Model
{
    use HasFactory;
    protected $fillable = [
        'hospital_id',
        'need_type',
        'description',
        'priority',
        'quantity',
    ];
}
