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
        'total_quantity',
        'delivered_quantity',
        'courier_id',
        'courier_accepted_at',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'total_quantity' => 'decimal:2',
        'delivered_quantity' => 'decimal:2',
        'courier_accepted_at' => 'datetime',
    ];

    // Get remaining quantity to be delivered
    public function getRemainingQuantity(): float
    {
        return max(0, $this->total_quantity - $this->delivered_quantity);
    }

    // Check if order is fully delivered
    public function isFullyDelivered(): bool
    {
        return $this->delivered_quantity >= $this->total_quantity;
    }

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

    // Specific relations for eager loading
    public function buyerFactory()
    {
        return $this->belongsTo(Factory::class, 'buyer_id');
    }

    public function buyerDistributor()
    {
        return $this->belongsTo(Distributor::class, 'buyer_id');
    }

    public function sellerSupplier()
    {
        return $this->belongsTo(Supplier::class, 'seller_id');
    }

    public function sellerFactory()
    {
        return $this->belongsTo(Factory::class, 'seller_id');
    }
}
