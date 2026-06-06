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
        Schema::create('hanh_noi_dung', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hanh_id')->constrained('hanh')->cascadeOnDelete();
            $table->string('slug')->comment('khuyet_0, duoi_30, 30_60, 60_80, tren_80');
            $table->string('title')->nullable()->comment('Tiêu đề');
            $table->longText('content')->nullable()->comment('Nội dung text dài');
            $table->integer('sort_order')->default(0)->comment('Thứ tự hiển thị');
            $table->timestamps();

            $table->index(['hanh_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hanh_noi_dung');
    }
};
