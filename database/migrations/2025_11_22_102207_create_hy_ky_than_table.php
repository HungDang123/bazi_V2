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
        Schema::create('hy_ky_than', function (Blueprint $table) {
            $table->id();
            $table->string('thien_can_ngay', 10);
            $table->string('dia_chi_thang', 10);
            $table->enum('than_nhuoc_than_vuong', ['Thân Vượng', 'Thân Nhược']);
            $table->string('hy_than_ngu_hanh');
            $table->string('hy_than_can');
            $table->string('ky_than_ngu_hanh');
            $table->string('ky_than_can');
            $table->timestamps();
            $table->index(['thien_can_ngay', 'dia_chi_thang']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hy_ky_than');
    }
};
