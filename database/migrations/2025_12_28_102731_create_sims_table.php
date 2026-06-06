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
        Schema::create('sims', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number')->unique();
            $table->decimal('cost_price', 8, 2);
            $table->decimal('selling_price', 8, 2)->nullable();
            $table->enum('network_operator', ['viettel', 'vinaphone', 'mobifone']);
            $table->integer('upper_trigram')->nullable()->comment('Quẻ thượng');
            $table->integer('lower_trigram')->nullable()->comment('Quẻ hạ');
            $table->string('upper_trigram_name')->nullable()->comment('Tên quẻ thượng');
            $table->string('lower_trigram_name')->nullable()->comment('Tên quẻ hạ');
            $table->integer('moving_line')->nullable()->comment('Động hào');
            $table->integer('que_id')->nullable()->index()->comment('id quẻ dịch');
            $table->enum('status', ['pending', 'confirmed', 'sold'])->default('pending')->comment('Trạng thái: pending: chưa chốt, confirmed: đã chốt, sold: đã bán');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sims');
    }
};
