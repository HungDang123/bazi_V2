<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('than_sat_rule_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('than_sat_rule_id')->constrained()->onDelete('cascade');
            $table->enum('loai_tra_cuu', [
                'can_nam',
                'can_ngay',
                'chi_thang',
                'chi_nam',
                'chi_ngay',
                'nap_am',
                'tru_ngay',
                'mua_sinh',
                'gioi_tinh',
                'tru_gio',
                'tru_nam',
                'tru_thang'
            ]);
            $table->string('gia_tri_tra_cuu', 50);
            $table->string('vi_tri_tim_thay', 100);
            $table->enum('vi_tri_xuat_hien', ['nam', 'thang', 'ngay', 'gio', 'multiple']);
            $table->integer('thu_tu_uu_tien')->default(1);
            $table->timestamps();

            $table->index(['than_sat_rule_id', 'loai_tra_cuu']);
            $table->index('thu_tu_uu_tien');
        });
    }

    public function down()
    {
        Schema::dropIfExists('than_sat_rule_details');
    }
};
