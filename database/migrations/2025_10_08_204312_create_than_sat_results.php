<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('than_sat_results', function (Blueprint $table) {
            $table->id();
            $table->json('tu_tru_data'); // Lưu can chi các trụ
            $table->json('ket_qua'); // Lưu kết quả thần sát
            $table->string('gioi_tinh', 10)->nullable(); // nam, nữ
            $table->string('am_duong', 10)->nullable(); // duong, am
            $table->timestamps();
            
            $table->index(['gioi_tinh', 'am_duong']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('than_sat_results');
    }
};