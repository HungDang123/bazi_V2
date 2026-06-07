<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phan7_tam_the', function (Blueprint $table) {
            if (! Schema::hasColumn('phan7_tam_the', 'image')) {
                $table->string('image', 512)->nullable()->comment('Đường dẫn ảnh (tương đối từ public/)')->after('noi_dung');
            }
        });
    }

    public function down(): void
    {
        Schema::table('phan7_tam_the', function (Blueprint $table) {
            if (Schema::hasColumn('phan7_tam_the', 'image')) {
                $table->dropColumn('image');
            }
        });
    }
};
