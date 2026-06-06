<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phan9a_ngoai_luc', function (Blueprint $table) {
            $table->id();
            $table->string('tieu_de', 255)->nullable();
            $table->longText('noi_dung')->comment('Toàn bộ Mã 2 — DOCX II. NGOẠI LỰC');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phan9a_ngoai_luc');
    }
};
