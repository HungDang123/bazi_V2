<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phan9b_giai_phap_can_bang', function (Blueprint $table) {
            $table->id();
            $table->string('loai', 32)->default('noi_dung')->comment('header | section | noi_dung');
            $table->string('than_trang_thai', 32)->nullable()->comment('than_vuong | than_nhuoc — null khi header/section');
            $table->string('muc', 64)->nullable()->comment('trang_thai_nang_luong_goc | chien_luoc_hanh_dong');
            $table->string('tieu_de', 512)->nullable()->comment('Tiêu đề mục từ cột B (carry-forward)');
            $table->longText('noi_dung');
            $table->string('bo_hy_than', 32)->nullable()->comment('tu_ton | the_tai | quan_quy | phu_mau | huynh_de');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['than_trang_thai', 'muc']);
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phan9b_giai_phap_can_bang');
    }
};
