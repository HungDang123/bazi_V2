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
        Schema::create('tong_quan_khia_canh', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 64)->unique()->comment('intro, su_nghiep, tai_chinh, tinh_duyen, suc_khoe, phat_trien_ban_than, ket_noi_xa_hoi');
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
        Schema::dropIfExists('tong_quan_khia_canh');
    }
};
