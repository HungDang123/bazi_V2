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
        Schema::create('giai_phap_thap_than', function (Blueprint $table) {
            $table->id();
            $table->string('thap_than', 32)->comment('Tỷ Kiên, Kiếp Tài, Thương Quan, Thực Thần, Chính Tài, Thiên Tài, Chính Quan, Thất Sát, Chính Ấn, Thiên Ấn');
            $table->longText('content')->nullable()->comment('Giải pháp gia tăng năng lượng Thập Thần');
            $table->integer('sort_order')->default(0)->comment('Thứ tự hiển thị');
            $table->timestamps();

            $table->unique('thap_than');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('giai_phap_thap_than');
    }
};
