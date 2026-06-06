<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Đơn giản hóa: chỉ lưu tru_loai + toàn bộ noi_dung.
     */
    public function up(): void
    {
        Schema::dropIfExists('dong_chay_gioi_thieu');

        Schema::create('dong_chay_gioi_thieu', function (Blueprint $table) {
            $table->id();
            $table->string('tru_loai', 32)->unique()->comment('tru_nam_tru_thang, tru_thang_tru_ngay, tru_ngay_tru_gio');
            $table->longText('noi_dung')->comment('Toàn bộ nội dung DOCX');
            $table->timestamps();

            $table->index('tru_loai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dong_chay_gioi_thieu');

        Schema::create('dong_chay_gioi_thieu', function (Blueprint $table) {
            $table->id();
            $table->string('tru_tuong_tac', 64);
            $table->string('loai', 32);
            $table->string('moi_quan_he', 32)->nullable();
            $table->text('noi_dung');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['tru_tuong_tac', 'loai', 'moi_quan_he']);
        });
    }
};
