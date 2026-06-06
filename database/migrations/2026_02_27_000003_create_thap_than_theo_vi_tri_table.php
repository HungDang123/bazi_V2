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
        Schema::create('thap_than_theo_vi_tri', function (Blueprint $table) {
            $table->id();
            $table->string('thap_than', 32)->comment('Chính Ấn, Thiên Ấn, Tỷ Kiên, Kiếp Tài, Thương Quan, Thực Thần, Chính Tài, Thiên Tài, Chính Quan, Thất Sát');
            $table->string('vi_tri', 32)->nullable()->comment('Tổng Quan, Trụ Giờ, Trụ Ngày, Trụ Tháng, Trụ Năm');
            $table->string('loai_can', 32)->nullable()->comment('Thiên Can, Tàng Can');
            $table->string('khia_canh', 128)->nullable()->comment('Tính cách..., Phát triển bản thân, Tài Chính, Tình Cảm, Sự Nghiệp, Mối quan hệ xã hội');
            $table->string('huong', 16)->nullable()->comment('Tích Cực, Tiêu Cực');
            $table->longText('content')->nullable()->comment('Nội dung');
            $table->integer('sort_order')->default(0)->comment('Thứ tự hiển thị');
            $table->timestamps();

            $table->index(['thap_than', 'vi_tri'], 'ttvt_lookup_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thap_than_theo_vi_tri');
    }
};
