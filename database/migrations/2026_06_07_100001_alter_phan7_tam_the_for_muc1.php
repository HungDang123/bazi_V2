<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phan7_tam_the', function (Blueprint $table) {
            if (Schema::hasColumn('phan7_tam_the', 'loai')) {
                $table->dropColumn('loai');
            }
            if (Schema::hasColumn('phan7_tam_the', 'thap_than')) {
                $table->dropColumn('thap_than');
            }
            if (Schema::hasColumn('phan7_tam_the', 'ten_truong_hop')) {
                $table->dropColumn('ten_truong_hop');
            }
        });
    }

    public function down(): void
    {
        Schema::table('phan7_tam_the', function (Blueprint $table) {
            $table->string('loai', 64)->nullable()->comment('Tổng Quan, Nguyên tắc cốt lõi, Trường hợp đặc biệt');
            $table->string('thap_than', 32)->nullable();
            $table->string('ten_truong_hop', 255)->nullable();
        });
    }
};
