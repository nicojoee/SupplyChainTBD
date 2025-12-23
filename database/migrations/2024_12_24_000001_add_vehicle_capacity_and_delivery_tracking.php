<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add vehicle capacity to couriers
        Schema::table('couriers', function (Blueprint $table) {
            $table->integer('vehicle_capacity')->nullable()->after('vehicle_type'); // in tons: 15, 20, or 30
        });

        // Add delivery tracking to orders
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('total_quantity', 12, 2)->default(0)->after('total_amount'); // total tons in order
            $table->decimal('delivered_quantity', 12, 2)->default(0)->after('total_quantity'); // already delivered
        });
    }

    public function down(): void
    {
        Schema::table('couriers', function (Blueprint $table) {
            $table->dropColumn('vehicle_capacity');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['total_quantity', 'delivered_quantity']);
        });
    }
};
