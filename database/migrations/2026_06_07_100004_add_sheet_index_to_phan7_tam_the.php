<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phan7_tam_the', function (Blueprint $table) {
            $table->unsignedTinyInteger('sheet_index')->default(0)->after('id');
            $table->index('sheet_index');
        });
    }

    public function down(): void
    {
        Schema::table('phan7_tam_the', function (Blueprint $table) {
            $table->dropIndex(['sheet_index']);
            $table->dropColumn('sheet_index');
        });
    }
};
