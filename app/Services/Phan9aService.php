<?php

namespace App\Services;

use App\Models\Phan9aNoiLuc;

class Phan9aService
{
    /**
     * Chọn ngũ hành yếu nhất trong biểu đồ NGŨ HÀNH BẢN MỆNH (5 hành Kim–Thổ).
     * Hòa điểm: ưu tiên thứ tự kim → moc → thuy → hoa → tho.
     *
     * @param  array<string, int|float>  $nguHanhDong
     * @return array{slug: string, ten: string, phan_tram: int}
     */
    /**
     * Chuẩn hóa key ngũ hành → kim | moc | thuy | hoa | tho.
     *
     * @param  array<string, int|float>  $raw
     * @return array<string, int>
     */
    public static function normalizeNguHanhDong(array $raw): array
    {
        $labelToSlug = [
            'kim' => 'kim', 'moc' => 'moc', 'thuy' => 'thuy', 'hoa' => 'hoa', 'tho' => 'tho',
            'Kim' => 'kim', 'Mộc' => 'moc', 'Thủy' => 'thuy', 'Hỏa' => 'hoa', 'Thổ' => 'tho',
            'KIM' => 'kim', 'MỘC' => 'moc', 'THỦY' => 'thuy', 'HỎA' => 'hoa', 'THỔ' => 'tho',
        ];

        $out = [];
        foreach ($raw as $key => $value) {
            $k = is_string($key) ? trim($key) : (string) $key;
            $slug = $labelToSlug[$k] ?? $labelToSlug[mb_strtoupper($k, 'UTF-8')] ?? null;
            if ($slug !== null) {
                $out[$slug] = (int) round((float) $value);
            }
        }

        return $out;
    }

    public static function resolveYeuNhatNguHanh(array $nguHanhDong): ?array
    {
        $nguHanhDong = self::normalizeNguHanhDong($nguHanhDong);
        if ($nguHanhDong === []) {
            return null;
        }

        $min = null;
        $slug = null;

        foreach (Phan9aNoiLuc::HANH_ORDER as $key) {
            if (! array_key_exists($key, $nguHanhDong)) {
                continue;
            }
            $val = (int) $nguHanhDong[$key];
            if ($min === null || $val < $min) {
                $min = $val;
                $slug = $key;
            }
        }

        if ($slug === null) {
            return null;
        }

        return [
            'slug' => $slug,
            'ten' => Phan9aNoiLuc::SLUG_TO_LABEL[$slug] ?? $slug,
            'phan_tram' => $min,
        ];
    }

    public static function replaceIntroPlaceholders(string $text, string $hanhTen, int $phanTram): string
    {
        $pct = (string) $phanTram . '%';

        $text = preg_replace('/Hành\s*\[YẾU\s*NHẤT\]/iu', 'Hành ' . $hanhTen, $text);
        $text = preg_replace('/bổ\s*sung\s+Hành\s*\[YẾU\s*NHẤT\]/iu', 'bổ sung Hành ' . $hanhTen, $text);
        $text = preg_replace('/\[YẾU\s*NHẤT\]/iu', $hanhTen, $text);
        $text = preg_replace('/mức\s*\[TỶ\s*LỆ\s*%\]/iu', 'mức ' . $pct, $text);
        $text = preg_replace('/\[TỶ\s*LỆ\s*%\]/iu', $pct, $text);

        return $text;
    }

    /**
     * @param  iterable<Phan9aNoiLuc>  $rows
     * @return array{tieu_de_chinh: string|null, sections: array<int, array{tieu_de: string|null, doan: array<int, string>}>}
     */
    public static function buildHanhDisplay(iterable $rows): array
    {
        $tieuDeChinh = null;
        $sections = [];
        $current = null;

        foreach ($rows as $row) {
            $td = trim((string) ($row->tieu_de ?? ''));
            $nd = trim((string) ($row->noi_dung ?? ''));

            if ($td !== '' && preg_match('/^Về\s+/u', $td)) {
                $current = ['tieu_de' => $td, 'doan' => []];
                $sections[] = $current;

                continue;
            }

            if ($tieuDeChinh === null && $td === '' && $nd !== '') {
                $tieuDeChinh = $nd;

                continue;
            }

            if ($nd === '') {
                continue;
            }

            if ($current === null) {
                $current = ['tieu_de' => $td !== '' ? $td : null, 'doan' => []];
                $sections[] = $current;
            }

            $sections[count($sections) - 1]['doan'][] = $nd;
        }

        return [
            'tieu_de_chinh' => $tieuDeChinh,
            'sections' => $sections,
        ];
    }

    public static function isSkippableImportLine(string $line): bool
    {
        if ($line === '') {
            return true;
        }

        if (preg_match('/^\[CHÈN NỘI DUNG/i', $line)) {
            return true;
        }

        if (preg_match('/^PHẦN\s*9A\b/iu', $line)) {
            return true;
        }

        return false;
    }
}
