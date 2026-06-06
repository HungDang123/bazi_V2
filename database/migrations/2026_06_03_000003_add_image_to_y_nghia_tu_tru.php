<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('y_nghia_tu_tru', function (Blueprint $table) {
            $table->string('image', 255)
                ->nullable()
                ->after('content')
                ->comment('Đường dẫn ảnh khi nội dung có [image]');
        });
    }

    public function down(): void
    {
        Schema::table('y_nghia_tu_tru', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
