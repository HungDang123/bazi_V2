<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * PHẦN 7 - BÀI HỌC CUỘC SỐNG - Sheets 2-7 (II đến VII).
     */
    public function up(): void
    {
        Schema::create('phan7_bai_hoc', function (Blueprint $table) {
            $table->id();
            $table->string('phan', 128)->comment('II. XÁC ĐỊNH SỰ NGHIỆP, III. TÀI CHÍNH, ...');
            $table->string('loai', 64)->comment('Tổng Quan, Nguyên tắc cốt lõi, Các trường hợp');
            $table->string('thap_than', 32)->nullable()->comment('HUYNH ĐỆ, TỬ TÔN, QUAN QUỶ, THÊ TÀI, PHỤ MẪU');
            $table->string('gioi_tinh', 8)->nullable()->comment('NAM, NỮ - cho mục Tình duyên');
            $table->string('ten_truong_hop', 512)->nullable();
            $table->text('noi_dung');
            $table->unsignedInteger('thu_tu')->default(0)->comment('Thứ tự trong sheet');
            $table->timestamps();

            $table->index(['phan', 'loai']);
            $table->index(['phan', 'thap_than']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phan7_bai_hoc');
    }
};
