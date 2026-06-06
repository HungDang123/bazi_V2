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
        Schema::create('nap_giap', function (Blueprint $table) {
            $table->id();
            $table->string('nap_giap_nam');
            $table->string('nap_giap_thang');
            $table->date('thoi_diem_bat_dau_ngay');
            $table->time('thoi_diem_bat_dau_gio');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nap_giap');
    }
};
