<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('thap_than_theo_vi_tri', function (Blueprint $table) {
            $table->string('huong', 48)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('thap_than_theo_vi_tri', function (Blueprint $table) {
            $table->string('huong', 16)->nullable()->change();
        });
    }
};
