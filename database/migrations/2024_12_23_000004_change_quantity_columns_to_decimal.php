<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Changes stock_quantity and production_quantity from integer to decimal
     * to support larger values and fractional tons.
     */
    public function up(): void
    {
        // Update supplier_products table
        Schema::table('supplier_products', function (Blueprint $table) {
            $table->decimal('stock_quantity', 15, 2)->default(0)->change();
        });

        // Update factory_products table
        Schema::table('factory_products', function (Blueprint $table) {
            $table->decimal('production_quantity', 15, 2)->default(0)->change();
        });

        // Update distributor_stocks table
        Schema::table('distributor_stocks', function (Blueprint $table) {
            $table->decimal('quantity', 15, 2)->default(0)->change();
            $table->decimal('min_stock_level', 15, 2)->default(0)->change();
        });

        // Update order_items table
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('quantity', 15, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_products', function (Blueprint $table) {
            $table->integer('stock_quantity')->default(0)->change();
        });

        Schema::table('factory_products', function (Blueprint $table) {
            $table->integer('production_quantity')->default(0)->change();
        });

        Schema::table('distributor_stocks', function (Blueprint $table) {
            $table->integer('quantity')->default(0)->change();
            $table->integer('min_stock_level')->default(0)->change();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });
    }
};
