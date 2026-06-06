<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phan8a_thap_than', function (Blueprint $table) {
            $table->id();
            $table->string('thap_than', 32);
            $table->string('vi_tri', 16)->comment('Trụ Năm, Trụ Tháng, Trụ Ngày, Trụ Giờ');
            $table->string('vi_tri_tuong_tac', 16)->comment('Thiên Can, Địa Chi');
            $table->string('loai_tuong_tac', 16)->comment('Hợp, Khắc, Xung, Hình, Hại, Phá');
            $table->string('tieu_de', 255)->nullable();
            $table->text('su_kien_co_hoi')->nullable();
            $table->text('quan_tri_rui_ro')->nullable();
            $table->text('chien_luoc')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(
                ['thap_than', 'vi_tri', 'vi_tri_tuong_tac', 'loai_tuong_tac'],
                'p8a_tt_vi_loai_uniq'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phan8a_thap_than');
    }
};
