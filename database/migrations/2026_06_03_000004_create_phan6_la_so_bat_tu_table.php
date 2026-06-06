<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phan6_la_so_bat_tu', function (Blueprint $table) {
            $table->id();
            $table->string('loai', 16)->comment('thien_can | dia_chi');
            $table->string('tru', 16)->comment('gio | ngay | thang | nam');
            $table->longText('noi_dung');
            $table->unsignedTinyInteger('sort_row')->default(0);
            $table->unsignedTinyInteger('sort_col')->default(0);
            $table->timestamps();

            $table->unique(['loai', 'tru']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phan6_la_so_bat_tu');
    }
};
