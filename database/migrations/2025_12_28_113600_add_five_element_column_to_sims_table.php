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
            $table->enum('five_element', ['Kim', 'Mộc', 'Thủy', 'Hỏa', 'Thổ'])->nullable()->after('que_id')->comment('Ngũ hành');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sims', function (Blueprint $table) {
            $table->dropColumn('five_element');
        });
    }
};
