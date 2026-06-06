<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * PHẦN 7 - BÀI HỌC CUỘC SỐNG - Sheet 1: I. XÁC ĐỊNH TÂM THẾ
     */
    public function up(): void
    {
        Schema::create('phan7_tam_the', function (Blueprint $table) {
            $table->id();
            $table->string('loai', 64)->comment('Tổng Quan, Nguyên tắc cốt lõi, Trường hợp đặc biệt');
            $table->string('thap_than', 32)->nullable()->comment('VD: HUYNH ĐỆ');
            $table->string('ten_truong_hop', 255)->nullable()->comment('VD: Lá số bị khuyết bộ Huynh Đệ (0%)');
            $table->text('noi_dung');
            $table->unsignedSmallInteger('thu_tu')->default(0);
            $table->timestamps();

            $table->index('loai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phan7_tam_the');
    }
};
