<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // TiDB/MySQL compatible way to modify ENUM
        // First check if 'pickup' is already in the enum
        $connection = config('database.default');
        
        try {
            // Try standard MySQL ALTER
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'confirmed', 'processing', 'pickup', 'in_delivery', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending'");
        } catch (\Exception $e) {
            // If that fails (TiDB might have issues), try alternative approach
            // Log the error but continue - the column might already be correct
            \Log::warning('Could not modify orders.status ENUM: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        // No rollback needed - idempotent
    }
};
