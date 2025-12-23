<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This ensures products exist in production without running seeders.
     */
    public function up(): void
    {
        $products = [
            ['name' => 'Raw Palm Oil', 'category' => 'Raw Materials', 'unit' => 'liters', 'base_price' => 15.00, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Refined Cooking Oil', 'category' => 'Finished Goods', 'unit' => 'liters', 'base_price' => 25.00, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Premium Olive Oil', 'category' => 'Premium', 'unit' => 'liters', 'base_price' => 50.00, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Coconut Oil', 'category' => 'Specialty', 'unit' => 'liters', 'base_price' => 30.00, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sunflower Oil', 'category' => 'Standard', 'unit' => 'liters', 'base_price' => 20.00, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Soybean Oil', 'category' => 'Raw Materials', 'unit' => 'liters', 'base_price' => 18.00, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Corn Oil', 'category' => 'Raw Materials', 'unit' => 'liters', 'base_price' => 22.00, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Canola Oil', 'category' => 'Raw Materials', 'unit' => 'liters', 'base_price' => 19.00, 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($products as $product) {
            // Use insertOrIgnore to avoid duplicates
            DB::table('products')->insertOrIgnore($product);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't delete products on rollback to preserve data integrity
    }
};
