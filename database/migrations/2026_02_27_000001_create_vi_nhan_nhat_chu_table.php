<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Lưu danh sách vĩ nhân theo Thiên Can (Nhật Chủ).
     * Import từ PHẦN 4 - V- VÍ DỤ VỀ VỸ NHÂN.xlsx
     */
    public function up(): void
    {
        Schema::create('vi_nhan_nhat_chu', function (Blueprint $table) {
            $table->id();
            $table->string('thien_can', 10)->comment('Thiên Can: Giáp, Ất, Bính, Đinh, Mậu, Kỷ, Canh, Tân, Nhâm, Quý');
            $table->string('ten_nguoi', 255)->comment('Tên vĩ nhân');
            $table->integer('sort_order')->default(0)->comment('Thứ tự hiển thị');
            $table->timestamps();

            $table->index('thien_can');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vi_nhan_nhat_chu');
    }
};
