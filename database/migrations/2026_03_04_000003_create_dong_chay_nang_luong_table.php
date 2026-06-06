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
        Schema::create('dong_chay_nang_luong', function (Blueprint $table) {
            $table->id();
            $table->string('thap_than', 32)->comment('Chính Ấn, Thiên Ấn, Tỷ Kiên...');
            $table->string('vi_tri_tuong_tac', 32)->comment('Thiên Can, Địa Chi');
            $table->string('loai_tuong_tac', 32)->comment('Hợp, Khắc, Xung');
            $table->string('tru_tuong_tac', 64)->comment('Trụ Năm - Trụ Tháng, Trụ Tháng - Trụ Ngày...');
            $table->string('huong', 32)->comment('Tích cực, Tiêu cực');
            $table->text('noi_dung');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['thap_than', 'vi_tri_tuong_tac', 'loai_tuong_tac'], 'dcnl_thap_vi_loai_idx');
            $table->index('tru_tuong_tac', 'dcnl_tru_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dong_chay_nang_luong');
    }
};
