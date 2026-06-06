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
        if (Schema::hasTable('phan5_suc_khoe') && ! Schema::hasTable('suc_khoe_hy_ky_than')) {
            Schema::rename('phan5_suc_khoe', 'suc_khoe_hy_ky_than');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('suc_khoe_hy_ky_than') && ! Schema::hasTable('phan5_suc_khoe')) {
            Schema::rename('suc_khoe_hy_ky_than', 'phan5_suc_khoe');
        }
    }
};

