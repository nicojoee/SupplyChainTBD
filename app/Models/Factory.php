<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'address',
        'latitude',
        'longitude',
        'phone',
        'email',
        'production_capacity',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'production_capacity' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(FactoryProduct::class);
    }

    public function ordersAsBuyer()
    {
        return $this->morphMany(Order::class, 'buyer');
    }

    public function ordersAsSeller()
    {
        return $this->morphMany(Order::class, 'seller');
    }
}
