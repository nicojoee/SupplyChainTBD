<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'buyer_type',
        'buyer_id',
        'seller_type',
        'seller_id',
        'status',
        'total_amount',
        'courier_id',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function courier()
    {
        return $this->belongsTo(Courier::class);
    }

    public function buyer()
    {
        if ($this->buyer_type === 'factory') {
            return $this->belongsTo(Factory::class, 'buyer_id');
        }
        return $this->belongsTo(Distributor::class, 'buyer_id');
    }

    public function seller()
    {
        if ($this->seller_type === 'supplier') {
            return $this->belongsTo(Supplier::class, 'seller_id');
        }
        return $this->belongsTo(Factory::class, 'seller_id');
    }

    public static function generateOrderNumber(): string
    {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }
}
