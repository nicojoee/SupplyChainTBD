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

    // Finished product names (only Factory can sell these)
    public static $finishedProducts = [
        'Refined Cooking Oil',
        'Premium Olive Oil',
    ];

    // Scope for raw materials (Supplier can sell these)
    public function scopeRawMaterials($query)
    {
        return $query->whereNotIn('name', self::$finishedProducts);
    }

    // Scope for finished products (Factory can sell these)
    public function scopeFinishedProducts($query)
    {
        return $query->whereIn('name', self::$finishedProducts);
    }

    // Check if product is a finished product
    public function isFinishedProduct()
    {
        return in_array($this->name, self::$finishedProducts);
    }

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
