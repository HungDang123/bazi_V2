<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dong_chay_gioi_thieu', function (Blueprint $table) {
            $table->string('image', 255)
                ->nullable()
                ->after('noi_dung')
                ->comment('Đường dẫn public/images/... khi DOCX có [image]');
        });
    }

    public function down(): void
    {
        Schema::table('dong_chay_gioi_thieu', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
