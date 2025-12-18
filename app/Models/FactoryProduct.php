<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FactoryProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'factory_id',
        'product_id',
        'price',
        'production_quantity',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'production_quantity' => 'integer',
    ];

    public function factory()
    {
        return $this->belongsTo(Factory::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
