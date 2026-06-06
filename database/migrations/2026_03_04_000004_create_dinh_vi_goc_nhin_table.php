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
        Schema::create('dinh_vi_goc_nhin', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 64)->unique()->comment('phan3_dinh_vi_goc_nhin hoặc các mục con nếu có');
            $table->string('title')->nullable()->comment('Tiêu đề mục');
            $table->longText('content')->nullable()->comment('Nội dung');
            $table->integer('sort_order')->default(0)->comment('Thứ tự hiển thị');
            $table->timestamps();

            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dinh_vi_goc_nhin');
    }
};

