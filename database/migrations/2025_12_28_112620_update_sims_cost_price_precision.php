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
        Schema::table('sims', function (Blueprint $table) {
            $table->decimal('cost_price', 12, 2)->change(); // Tăng từ 8,2 lên 12,2
            $table->decimal('selling_price', 12, 2)->nullable()->change(); // Tăng từ 8,2 lên 12,2
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sims', function (Blueprint $table) {
            $table->decimal('cost_price', 8, 2)->change();
            $table->decimal('selling_price', 8, 2)->nullable()->change();
        });
    }
};
