<?php

namespace App\Models;

use App\Support\Geo\VenezuelaBounds;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MapPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'latitude',
        'longitude',
        'address',
        'description',
        'contact_phone',
        'notes',
        'capacity_total',
        'capacity_available',
        'accepts_children',
        'accepts_elderly',
        'accepts_pets',
        'has_water',
        'has_food',
        'has_medicine',
        'has_power_charging',
        'urgency_level',
        'emergency_available',
        'needs_supplies',
        'metadata',
        'city',
        'state',
        'source',
        'status',
        'last_verified_at',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'capacity_total' => 'integer',
        'capacity_available' => 'integer',
        'accepts_children' => 'boolean',
        'accepts_elderly' => 'boolean',
        'accepts_pets' => 'boolean',
        'has_water' => 'boolean',
        'has_food' => 'boolean',
        'has_medicine' => 'boolean',
        'has_power_charging' => 'boolean',
        'emergency_available' => 'boolean',
        'needs_supplies' => 'boolean',
        'metadata' => 'array',
    ];

    public function scopeWithinBounds(Builder $query, mixed $swLat, mixed $swLng, mixed $neLat, mixed $neLng): Builder
    {
        $south = max((float) $swLat, VenezuelaBounds::MIN_LAT);
        $west = max((float) $swLng, VenezuelaBounds::MIN_LNG);
        $north = min((float) $neLat, VenezuelaBounds::MAX_LAT);
        $east = min((float) $neLng, VenezuelaBounds::MAX_LNG);

        if ($south > $north || $west > $east) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->whereBetween('latitude', [$south, $north])
            ->whereBetween('longitude', [$west, $east]);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function hospitalNeeds()
    {
        return $this->hasMany(HospitalNeed::class, 'hospital_id');
    }
}
