<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'organization',
        'position',
        'city',
        'state',
        'motivation',
        'coverage_area',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // Accessor for backward compatibility with is_active
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'approved';
    }

    // Roles
    const ROLE_ADMIN    = 'admin';
    const ROLE_OPERATOR = 'operator';

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isOperator(): bool
    {
        return $this->role === self::ROLE_OPERATOR || $this->isAdmin();
    }

    // Relaciones
    public function mapPointsCreated()
    {
        return $this->hasMany(MapPoint::class, 'created_by');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}
