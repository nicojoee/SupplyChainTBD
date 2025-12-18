<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'unit',
        'base_price',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
    ];

    public function supplierProducts()
    {
        return $this->hasMany(SupplierProduct::class);
    }

    public function factoryProducts()
    {
        return $this->hasMany(FactoryProduct::class);
    }

    public function distributorStocks()
    {
        return $this->hasMany(DistributorStock::class);
    }
}
