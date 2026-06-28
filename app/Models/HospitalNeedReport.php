<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HospitalNeedReport extends Model
{
    use HasFactory;
    protected $table = 'hospital_needs_reports';
    protected $fillable = [
        'hospital_id',
        'hospital_name',
        'needs',
        'description',
        'reporter_name',
        'reporter_contact',
    ];
    protected $casts = ['needs' => 'array'];

    public function hospital()
    {
        return $this->belongsTo(MapPoint::class, 'hospital_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
