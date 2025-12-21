<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Courier extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'vehicle_type',
        'license_plate',
        'phone',
        'current_latitude',
        'current_longitude',
        'is_gps_active',
        'location_updated_at',
        'status',
    ];

    protected $casts = [
        'current_latitude' => 'decimal:8',
        'current_longitude' => 'decimal:8',
        'is_gps_active' => 'boolean',
        'location_updated_at' => 'datetime',
    ];

    // Check if location is expired (more than 4 hours old)
    public function isLocationExpired(): bool
    {
        if (!$this->location_updated_at) {
            return true;
        }
        return $this->location_updated_at->diffInHours(now()) >= 4;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
