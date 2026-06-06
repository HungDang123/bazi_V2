<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phan5_trang', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 64)->unique()->comment('bia, tong_quan');
            $table->string('title')->nullable();
            $table->string('image', 512)->nullable()->comment('Đường dẫn ảnh trang (bìa / khung nội dung)');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phan5_trang');
    }
};
