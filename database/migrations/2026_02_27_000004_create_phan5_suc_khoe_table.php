<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('phan5_suc_khoe', function (Blueprint $table) {
            $table->id();
            // Ví dụ: "THÂN RẤT YẾU", "THÂN YẾU", "THÂN TRUNG BÌNH", ...
            $table->string('than_trang_thai', 191);
            // Slug không dấu: "than-rat-yeu", "than-yeu", ...
            $table->string('than_trang_thai_slug', 191)->index();
            // Nhãn ở cột C: "TỔNG QUAN", "TỶ KIÊN (Đại diện cho...)", ...
            $table->string('nhom', 255);
            // Nội dung đầy đủ ở cột D (bao gồm tổng quan / tích cực / tiêu cực)
            $table->longText('content');
            // Thứ tự hiển thị trong từng trạng thái thân
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phan5_suc_khoe');
    }
};

