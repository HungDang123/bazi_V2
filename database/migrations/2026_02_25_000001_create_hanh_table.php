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
        Schema::create('hanh', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Hỏa, Kim, Mộc, Thổ, Thủy');
            $table->string('slug')->unique()->comment('hoa, kim, moc, tho, thuy');
            $table->integer('sort_order')->default(0)->comment('Thứ tự hiển thị');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hanh');
    }
};
