<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdmittedPerson extends Model
{
    use HasFactory;
    protected $fillable = [
        'full_name',
        'cedula',
        'alias',
        'approx_age',
        'sex',
        'hospital_id',
        'hospital_name_snapshot',
        'status_general',
        'admitted_at',
        'public_notes',
        'source',
        'reporter_name',
        'reporter_contact',
    ];

    public function hospital()
    {
        return $this->belongsTo(MapPoint::class, 'hospital_id');
    }
}
