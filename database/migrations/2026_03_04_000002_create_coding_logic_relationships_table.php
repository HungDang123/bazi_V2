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
        Schema::create('coding_logic_relationships', function (Blueprint $table) {
            $table->id();
            $table->string('loai', 16)->comment('thien_can, dia_chi');
            $table->string('item1', 16)->comment('Thiên Can 1 hoặc Địa Chi 1');
            $table->string('item2', 16)->comment('Thiên Can 2 hoặc Địa Chi 2');
            $table->string('moi_quan_he', 16)->comment('Hợp, Khắc, Xung, Hình, Hại, Phá');
            $table->string('ngu_hanh_sinh_ra', 16)->nullable()->comment('Thổ, Kim, Thủy, Mộc, Hỏa - chỉ có khi Hợp');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['loai', 'item1', 'item2', 'moi_quan_he'], 'clr_unique');
            $table->index(['loai', 'moi_quan_he']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coding_logic_relationships');
    }
};
