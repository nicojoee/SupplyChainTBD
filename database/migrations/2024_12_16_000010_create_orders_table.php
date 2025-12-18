<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->enum('buyer_type', ['factory', 'distributor']);
            $table->unsignedBigInteger('buyer_id');
            $table->enum('seller_type', ['supplier', 'factory']);
            $table->unsignedBigInteger('seller_id');
            $table->enum('status', ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->foreignId('courier_id')->nullable()->constrained()->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['buyer_type', 'buyer_id']);
            $table->index(['seller_type', 'seller_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
