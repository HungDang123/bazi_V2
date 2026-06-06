<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('la_so_pdf_exports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('params_hash', 64)->index();
            $table->json('params');
            $table->string('q1_status', 20)->default('pending');
            $table->string('q2_status', 20)->default('pending');
            $table->string('q1_path')->nullable();
            $table->string('q2_path')->nullable();
            $table->text('q1_error')->nullable();
            $table->text('q2_error')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('q1_ready_at')->nullable();
            $table->timestamp('q2_ready_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('la_so_pdf_exports');
    }
};
