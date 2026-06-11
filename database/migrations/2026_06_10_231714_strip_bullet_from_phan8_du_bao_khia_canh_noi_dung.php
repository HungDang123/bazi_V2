<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Dọn dấu bullet (•) thừa trong field noi_dung của phan8_du_bao_khia_canh.
 *
 * Dữ liệu Excel có dạng:
 *   "• Câu nội dung a.\n• Câu nội dung b."
 * Sau migration:
 *   "Câu nội dung a.\nCâu nội dung b."
 */
return new class extends Migration
{
    public function up(): void
    {
        $rows = DB::table('phan8_du_bao_khia_canh')
            ->whereNotNull('noi_dung')
            ->select('id', 'noi_dung')
            ->get();

        $updated = 0;

        foreach ($rows as $row) {
            $cleaned = self::stripBullets((string) $row->noi_dung);

            if ($cleaned !== $row->noi_dung) {
                DB::table('phan8_du_bao_khia_canh')
                    ->where('id', $row->id)
                    ->update(['noi_dung' => $cleaned]);

                $updated++;
            }
        }

        echo "  strip_bullets: đã cập nhật {$updated} bản ghi.\n";
    }

    public function down(): void
    {
        // Không thể hoàn tác — dữ liệu gốc không lưu lại.
    }

    private static function stripBullets(string $text): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];

        $lines = array_map(static function (string $line): string {
            // Xóa dấu •, –, - ở đầu dòng (cùng khoảng trắng theo sau)
            return preg_replace('/^[\x{2022}\x{2013}\x{2014}\-]\s*/u', '', $line) ?? $line;
        }, $lines);

        return implode("\n", $lines);
    }
};
