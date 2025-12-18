<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributorStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'distributor_id',
        'product_id',
        'quantity',
        'min_stock_level',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'min_stock_level' => 'integer',
    ];

    public function distributor()
    {
        return $this->belongsTo(Distributor::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
