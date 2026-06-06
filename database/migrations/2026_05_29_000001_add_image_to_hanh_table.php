<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $images = [
        'hoa'  => 'resources/views/pdfs/quyen-1/ngu-hanh/hoa.png',
        'kim'  => 'resources/views/pdfs/quyen-1/ngu-hanh/kim.png',
        'moc'  => 'resources/views/pdfs/quyen-1/ngu-hanh/moc.png',
        'tho'  => 'resources/views/pdfs/quyen-1/ngu-hanh/tho.png',
        'thuy' => 'resources/views/pdfs/quyen-1/ngu-hanh/thuy.png',
    ];

    public function up(): void
    {
        Schema::table('hanh', function (Blueprint $table) {
            $table->string('image')->nullable()->after('sort_order')->comment('Đường dẫn ảnh minh họa ngũ hành');
        });

        foreach ($this->images as $slug => $image) {
            DB::table('hanh')->where('slug', $slug)->update(['image' => $image]);
        }
    }

    public function down(): void
    {
        Schema::table('hanh', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
