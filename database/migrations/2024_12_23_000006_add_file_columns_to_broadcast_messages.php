<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add file_name and file_type columns for PDF support in broadcasts.
     */
    public function up(): void
    {
        Schema::table('broadcast_messages', function (Blueprint $table) {
            $table->string('file_name')->nullable()->after('image_path');
            $table->string('file_type')->nullable()->after('file_name'); // 'image' or 'pdf'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('broadcast_messages', function (Blueprint $table) {
            $table->dropColumn(['file_name', 'file_type']);
        });
    }
};
