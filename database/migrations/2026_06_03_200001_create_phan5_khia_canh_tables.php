<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phan5_khia_canh', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 64)->unique()->comment('su_nghiep, tai_chinh, tinh_duyen, phat_trien_ban_than, ket_noi_xa_hoi');
            $table->string('section_code', 8)->comment('II, III, IV, VI, VII');
            $table->string('title')->comment('VD: II. SỰ NGHIỆP');
            $table->longText('tong_quan')->nullable()->comment('Nội dung mục 1. Tổng quan');
            $table->string('image_vi_tri', 512)->nullable()->comment('Đường dẫn ảnh vị trí trên La Bàn');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('sort_order');
        });

        Schema::create('phan5_thap_than_hinh_anh', function (Blueprint $table) {
            $table->id();
            $table->string('thap_than', 32)->unique()->comment('Tỷ Kiên, Kiếp Tài, ...');
            $table->string('image', 512)->nullable()->comment('Đường dẫn ảnh minh họa Thập Thần');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phan5_thap_than_hinh_anh');
        Schema::dropIfExists('phan5_khia_canh');
    }
};
