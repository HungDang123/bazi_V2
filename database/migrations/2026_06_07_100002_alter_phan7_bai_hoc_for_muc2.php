<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phan7_bai_hoc', function (Blueprint $table) {
            if (Schema::hasColumn('phan7_bai_hoc', 'phan')) {
                $table->dropIndex(['phan', 'loai']);
                $table->dropIndex(['phan', 'thap_than']);
                $table->dropColumn('phan');
            }
            if (Schema::hasColumn('phan7_bai_hoc', 'loai')) {
                $table->dropColumn('loai');
            }
            if (Schema::hasColumn('phan7_bai_hoc', 'gioi_tinh')) {
                $table->dropColumn('gioi_tinh');
            }
            if (! Schema::hasColumn('phan7_bai_hoc', 'tieu_de')) {
                $table->string('tieu_de', 255)->nullable()->comment('Tiêu đề phụ từ cột B, vd: a. Bản chất năng lượng:')->after('ten_truong_hop');
            }
        });
    }

    public function down(): void
    {
        Schema::table('phan7_bai_hoc', function (Blueprint $table) {
            $table->string('phan', 128)->nullable()->comment('II. XÁC ĐỊNH SỰ NGHIỆP, ...')->after('id');
            $table->string('loai', 64)->nullable()->comment('Tổng Quan, Nguyên tắc cốt lõi, Các trường hợp');
            $table->string('gioi_tinh', 8)->nullable();

            if (Schema::hasColumn('phan7_bai_hoc', 'tieu_de')) {
                $table->dropColumn('tieu_de');
            }
        });
    }
};
