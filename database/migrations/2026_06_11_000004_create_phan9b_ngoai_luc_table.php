<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phan9b_ngoai_luc', function (Blueprint $table) {
            $table->id();
            $table->string('loai', 32)->comment('header | subtitle | intro | section | item');
            $table->unsignedTinyInteger('section_number')->nullable();
            $table->longText('noi_dung');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['loai', 'section_number']);
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phan9b_ngoai_luc');
    }
};
