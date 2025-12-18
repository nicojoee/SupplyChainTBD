<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First, update existing 'available' and 'offline' to 'busy' temporarily (valid ENUM value)
        DB::table('couriers')->whereIn('status', ['available', 'offline'])->update(['status' => 'busy']);
        
        // Modify the ENUM column to use 'idle' and 'busy' only
        DB::statement("ALTER TABLE couriers MODIFY COLUMN status ENUM('idle', 'busy') DEFAULT 'idle'");
        
        // Now update 'busy' records to 'idle' if they have no active orders
        $couriers = DB::table('couriers')->get();
        foreach ($couriers as $courier) {
            $hasActiveOrders = DB::table('orders')
                ->where('courier_id', $courier->id)
                ->whereIn('status', ['processing', 'shipped'])
                ->exists();
            
            if (!$hasActiveOrders) {
                DB::table('couriers')->where('id', $courier->id)->update(['status' => 'idle']);
            }
        }
    }

    public function down(): void
    {
        // Revert back to original ENUM values
        DB::statement("ALTER TABLE couriers MODIFY COLUMN status ENUM('available', 'busy', 'offline') DEFAULT 'offline'");
        
        // Convert 'idle' back to 'available'
        DB::table('couriers')->where('status', 'idle')->update(['status' => 'available']);
    }
};
