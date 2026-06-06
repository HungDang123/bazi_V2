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
        Schema::create('dia_chi_thang', function (Blueprint $table) {
            $table->id();
            $table->string('dia_chi', 10)->unique()->comment('Địa Chi trụ tháng: Dần, Mão, Thìn, Tỵ, Ngọ, Mùi, Thân, Dậu, Tuất, Hợi, Tý, Sửu');
            $table->string('ngu_hanh', 20)->comment('Ngũ Hành: Mộc, Hỏa, Thổ, Kim, Thủy');
            $table->string('am_duong', 5)->comment('Âm Dương: + (Dương) hoặc - (Âm)');
            $table->string('menh_full', 30)->nullable()->comment('Ngũ Hành và dấu âm dương: + Mộc, - Mộc, ...');
            $table->string('mua_sinh', 30)->comment('Mùa sinh: Mùa Xuân, Mùa Hè, Mùa Thu, Mùa Đông');
            $table->string('ngu_hanh_mua_sinh', 20)->comment('Ngũ Hành mùa sinh: Mộc, Hỏa, Kim, Thủy');
            $table->integer('sort_order')->default(0)->comment('Thứ tự hiển thị 1-12');
            $table->timestamps();

            $table->index('mua_sinh');
            $table->index('ngu_hanh_mua_sinh');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dia_chi_thang');
    }
};
