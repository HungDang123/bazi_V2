<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phan8_du_bao_khia_canh', function (Blueprint $table) {
            $table->string('phan_ban', 5)->default('8a')->after('id');
            $table->index(['phan_ban', 'khia_canh', 'gioi_tinh']);
        });
    }

    public function down(): void
    {
        Schema::table('phan8_du_bao_khia_canh', function (Blueprint $table) {
            $table->dropIndex(['phan_ban', 'khia_canh', 'gioi_tinh']);
            $table->dropColumn('phan_ban');
        });
    }
};
