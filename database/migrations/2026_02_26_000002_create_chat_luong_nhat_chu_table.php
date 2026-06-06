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
        Schema::create('chat_luong_nhat_chu', function (Blueprint $table) {
            $table->id();
            $table->string('thien_can', 10)->comment('Thiên Can: Giáp, Ất, Bính, Đinh, Mậu, Kỷ, Canh, Tân, Nhâm, Quý');
            $table->string('mua_sinh', 30)->comment('Mùa sinh: Mùa Xuân, Mùa Hè, Mùa Thu, Mùa Đông');
            $table->string('trang_thai', 30)->nullable()->comment('Trạng thái: Thân Vượng, Thân Nhược');
            $table->string('title')->nullable()->comment('Tiêu đề mục con');
            $table->longText('content')->nullable()->comment('Nội dung');
            $table->integer('sort_order')->default(0)->comment('Thứ tự hiển thị');
            $table->timestamps();

            $table->index(['thien_can', 'mua_sinh']);
            $table->index('trang_thai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_luong_nhat_chu');
    }
};
