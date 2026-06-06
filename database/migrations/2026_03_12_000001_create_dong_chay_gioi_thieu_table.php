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
        Schema::create('dong_chay_gioi_thieu', function (Blueprint $table) {
            $table->id();
            $table->string('tru_tuong_tac', 64)->comment('Trụ Năm - Trụ Tháng, Trụ Tháng - Trụ Ngày, Trụ Ngày - Trụ Giờ');
            $table->string('loai', 32)->comment('intro, Thiên Can, Địa Chi');
            $table->string('moi_quan_he', 32)->nullable()->comment('Hợp, Khắc, Xung, Hình, Hại, Phá');
            $table->text('noi_dung');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['tru_tuong_tac', 'loai', 'moi_quan_he'], 'dcgt_tru_loai_mqh_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dong_chay_gioi_thieu');
    }
};
