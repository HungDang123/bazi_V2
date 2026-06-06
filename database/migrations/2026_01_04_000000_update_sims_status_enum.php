<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Bước 1: Thêm 'available' vào enum (giữ lại các giá trị cũ)
        DB::statement("ALTER TABLE sims MODIFY COLUMN status ENUM('pending', 'confirmed', 'sold', 'available') DEFAULT 'pending'");
        
        // Bước 2: Cập nhật dữ liệu hiện có: confirmed -> available, pending -> available
        DB::statement("UPDATE sims SET status = 'available' WHERE status IN ('confirmed', 'pending')");
        
        // Bước 3: Thay đổi enum cuối cùng chỉ giữ lại 'available' và 'sold'
        DB::statement("ALTER TABLE sims MODIFY COLUMN status ENUM('available', 'sold') DEFAULT 'available' COMMENT 'Trạng thái: available: chưa bán, sold: đã bán'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Khôi phục lại enum cũ
        DB::statement("ALTER TABLE sims MODIFY COLUMN status ENUM('pending', 'confirmed', 'sold') DEFAULT 'pending' COMMENT 'Trạng thái: pending: chưa chốt, confirmed: đã chốt, sold: đã bán'");
        
        // Khôi phục dữ liệu
        DB::statement("UPDATE sims SET status = 'confirmed' WHERE status = 'available'");
    }
};
