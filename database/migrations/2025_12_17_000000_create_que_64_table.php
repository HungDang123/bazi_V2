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
        Schema::create('que_64', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Tên quẻ')->index();
            $table->json('tong_quan')->nullable()->comment('Nội dung tổng quan');
            $table->json('su_nghiep')->nullable()->comment('Sự nghiệp (tich_cuc, tieu_cuc)');
            $table->json('tai_chinh')->nullable()->comment('Tài chính (tich_cuc, tieu_cuc)');
            $table->json('tinh_duyen')->nullable()->comment('Tình duyên (tich_cuc, tieu_cuc)');
            $table->json('suc_khoe')->nullable()->comment('Sức khoẻ (tich_cuc, tieu_cuc)');
            $table->json('phat_trien_ban_than')->nullable()->comment('Phát triển bản thân (tich_cuc, tieu_cuc)');
            $table->json('ket_noi_xa_hoi')->nullable()->comment('Kết nối xã hội (tich_cuc, tieu_cuc)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('que_64');
    }
};
