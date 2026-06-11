<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phan9b_noi_luc', function (Blueprint $table) {
            $table->id();
            $table->string('loai', 32)->comment('section | intro | muc | hanh');
            $table->string('ngu_hanh', 16)->nullable()->comment('kim | moc | thuy | hoa | tho — null khi intro/section/muc');
            $table->string('tieu_de', 512)->nullable()->comment('Tiêu đề phụ, vd: Về tư duy và tâm thức...');
            $table->longText('noi_dung');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['loai', 'ngu_hanh']);
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phan9b_noi_luc');
    }
};
