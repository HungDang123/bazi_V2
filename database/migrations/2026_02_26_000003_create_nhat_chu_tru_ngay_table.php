<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Lưu nội dung Nhật Chủ Trụ Ngày (Thiên Can + Địa Chi ngày).
     * Mỗi bản ghi = 1 dòng nội dung (title, chapter, sub_title, content).
     */
    public function up(): void
    {
        Schema::create('nhat_chu_tru_ngay', function (Blueprint $table) {
            $table->id();
            $table->string('thien_can', 10)->comment('Thiên Can: Giáp, Ất, Bính, Đinh, Mậu, Kỷ, Canh, Tân, Nhâm, Quý');
            $table->string('dia_chi', 10)->comment('Địa Chi: Tý, Sửu, Dần, Mão, Thìn, Tỵ, Ngọ, Mùi, Thân, Dậu, Tuất, Hợi');
            $table->string('title', 500)->nullable()->comment('Tiêu đề chính (cột A)');
            $table->string('chapter', 200)->nullable()->comment('Chương: I. TỔNG QUAN, II. XU HƯỚNG... (cột B)');
            $table->string('sub_title', 500)->nullable()->comment('Tiêu đề phụ: 1. Ý nghĩa... (cột C)');
            $table->longText('content')->nullable()->comment('Nội dung (cột D)');
            $table->integer('sort_order')->default(0)->comment('Thứ tự hiển thị');
            $table->timestamps();

            $table->index(['thien_can', 'dia_chi']);
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nhat_chu_tru_ngay');
    }
};
