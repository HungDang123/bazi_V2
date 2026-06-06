<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phan8_du_bao_khia_canh', function (Blueprint $table) {
            $table->id();
            $table->string('khia_canh', 100)->comment('Sự nghiệp, Tài chính, ...');
            $table->string('gioi_tinh', 10)->nullable()->comment('NAM/NỮ cho mục tình duyên');
            $table->string('dieu_kien', 255)->comment('Điều kiện so sánh năng lượng');
            $table->longText('noi_dung')->nullable()->comment('Nội dung dự báo đã ghép block');
            $table->unsignedInteger('thu_tu')->default(0)->comment('Thứ tự trong từng sheet');
            $table->string('sheet_name', 120)->nullable();
            $table->timestamps();

            $table->index(['khia_canh', 'gioi_tinh']);
            $table->index('thu_tu');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phan8_du_bao_khia_canh');
    }
};

