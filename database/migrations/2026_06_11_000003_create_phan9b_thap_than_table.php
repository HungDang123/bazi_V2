<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phan9b_thap_than', function (Blueprint $table) {
            $table->id();
            $table->string('loai', 32)->comment('intro | muc | muc_note | thap_than');
            $table->string('bo', 32)->nullable()->comment('huynh_de | tu_ton | the_tai | quan_quy | phu_mau');
            $table->string('thap_than', 32)->nullable()->comment('ty_kien | chinh_tai | ...');
            $table->string('tieu_de', 512)->nullable();
            $table->longText('noi_dung');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['loai', 'thap_than']);
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phan9b_thap_than');
    }
};
