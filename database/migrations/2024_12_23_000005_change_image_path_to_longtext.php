<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Change image_path to LONGTEXT to support Base64 encoded images.
     */
    public function up(): void
    {
        Schema::table('broadcast_messages', function (Blueprint $table) {
            $table->longText('image_path')->nullable()->change();
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->longText('image_path')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('broadcast_messages', function (Blueprint $table) {
            $table->string('image_path')->nullable()->change();
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->string('image_path')->nullable()->change();
        });
    }
};
