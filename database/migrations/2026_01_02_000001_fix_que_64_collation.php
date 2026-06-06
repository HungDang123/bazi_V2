<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Đổi collation của cột name sang utf8mb4_bin để phân biệt dấu thanh
        DB::statement('ALTER TABLE que_64 MODIFY name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Đổi lại collation về utf8mb4_unicode_ci (mặc định)
        DB::statement('ALTER TABLE que_64 MODIFY name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL');
    }
};
