<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Updates all products to use Rupiah per ton pricing instead of USD per liter.
     */
    public function up(): void
    {
        // Update all product units to 'ton'
        DB::table('products')->update(['unit' => 'ton']);
        
        // Update base prices to Rupiah per ton (realistic prices)
        // Raw Palm Oil: ~Rp 12,000,000/ton
        // Refined Cooking Oil: ~Rp 18,000,000/ton
        // Premium Olive Oil: ~Rp 45,000,000/ton
        // etc.
        
        $priceUpdates = [
            'Raw Palm Oil' => 12000000,
            'Refined Cooking Oil' => 18000000,
            'Premium Olive Oil' => 45000000,
            'Coconut Oil' => 25000000,
            'Sunflower Oil' => 16000000,
            'Soybean Oil' => 14000000,
            'Corn Oil' => 17000000,
            'Canola Oil' => 15000000,
        ];
        
        foreach ($priceUpdates as $name => $price) {
            DB::table('products')
                ->where('name', $name)
                ->update(['base_price' => $price]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to liters and USD
        DB::table('products')->update(['unit' => 'liters']);
        
        $priceUpdates = [
            'Raw Palm Oil' => 15.00,
            'Refined Cooking Oil' => 25.00,
            'Premium Olive Oil' => 50.00,
            'Coconut Oil' => 30.00,
            'Sunflower Oil' => 20.00,
            'Soybean Oil' => 18.00,
            'Corn Oil' => 22.00,
            'Canola Oil' => 19.00,
        ];
        
        foreach ($priceUpdates as $name => $price) {
            DB::table('products')
                ->where('name', $name)
                ->update(['base_price' => $price]);
        }
    }
};
