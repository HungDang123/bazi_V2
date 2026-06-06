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
        Schema::create('than_sat_rules', function (Blueprint $table) {
            $table->id();
            $table->string('ten_than_sat', 100);
            $table->enum('loai_than_sat', ['cat', 'hung', 'trung_tinh']);
            $table->text('phuong_phap_tra_cuu')->nullable();
            $table->timestamps();

            $table->index('loai_than_sat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('than_sat_rules');
    }
};
