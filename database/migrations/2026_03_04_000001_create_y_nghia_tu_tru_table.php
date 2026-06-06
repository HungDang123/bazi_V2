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
        Schema::create('y_nghia_tu_tru', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 64)->unique()->comment('intro, co_che_van_hanh, nhan_dien_diem_nghen, lam_chu_ban_do');
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
        Schema::dropIfExists('y_nghia_tu_tru');
    }
};
