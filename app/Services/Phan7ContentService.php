<?php

namespace App\Services;

use App\Models\Phan7BaiHoc;

class Phan7ContentService
{
    /**
     * Parse the percentage bucket from ten_truong_hop string.
     * Returns ['min' => int, 'max' => int] or null.
     */
    public static function parseBucket(string $tenTruongHop): ?array
    {
        $t = $tenTruongHop;

        if (mb_strpos($t, 'bị khuyết') !== false && mb_strpos($t, '0%') !== false) {
            return ['min' => 0, 'max' => 0];
        }
        if (mb_strpos($t, 'trên 80%') !== false) {
            return ['min' => 81, 'max' => 100];
        }
        if (mb_strpos($t, '60%') !== false && mb_strpos($t, '80%') !== false) {
            return ['min' => 60, 'max' => 80];
        }
        if (mb_strpos($t, '30%') !== false && (mb_strpos($t, '60%') !== false || mb_strpos($t, 'dưới 60') !== false)) {
            return ['min' => 30, 'max' => 59];
        }
        if (mb_strpos($t, 'dưới 30%') !== false || mb_strpos($t, '30%') !== false) {
            return ['min' => 1, 'max' => 29];
        }

        return null;
    }

    /**
     * Parse ten_truong_hop into a display-ready title prefix and subtitle.
     *
     * Input:  "1. Năng lượng Huynh Đệ từ trên 80% – Sức mạnh bản ngã..."
     * Output: ['title_prefix' => '1. NĂNG LƯỢNG HUYNH ĐỆ', 'subtitle' => 'SỨC MẠNH BẢN NGÃ...']
     *
     * @return array{title_prefix: string, subtitle: string}
     */
    public static function parseTenTruongHop(string $tenTruongHop): array
    {
        // Split on em dash " – " or regular dash " - "
        $parts = preg_split('/\s[–\-]\s/u', $tenTruongHop, 2);
        $left  = trim($parts[0] ?? $tenTruongHop);
        $right = trim($parts[1] ?? '');

        // Remove the bucket clause ("từ trên 80%", "từ 30% đến dưới 60%", etc.)
        $titlePrefix = preg_replace('/\s+từ\s+.+$/u', '', $left);
        $titlePrefix = mb_strtoupper(trim($titlePrefix ?? $left));

        return [
            'title_prefix' => $titlePrefix,
            'subtitle'     => mb_strtoupper($right),
        ];
    }

    /**
     * Return the filesystem path to the Thập Thần illustration image.
     */
    public static function getThapThanImagePath(string $thapThan): ?string
    {
        $map = [
            'HUYNH ĐỆ' => public_path('images/phan-7/huynh-de.png'),
            'TỬ TÔN'   => public_path('images/phan-7/tu-ton.png'),
            'QUAN QUỶ' => public_path('images/phan-7/quan-quy.png'),
            'THÊ TÀI'  => public_path('images/phan-7/the-tai.png'),
            'PHỤ MẪU'  => public_path('images/phan-7/phu-mau.png'),
        ];

        $path = $map[$thapThan] ?? null;

        return ($path !== null && file_exists($path)) ? $path : null;
    }

    /**
     * Build content blocks for a single Mục II entry (one Thập Thần scenario).
     *
     * Block types produced:
     *   - thap_than_title : { title, subtitle } — always starts a new PDF page
     *   - section_label   : { text }
     *   - image           : { path }
     *   - para            : { text }
     *
     * @param  array{thap_than: string, ten_truong_hop: string, diem: int, noi_dung: array} $entry
     * @return array<int, array<string, mixed>>
     */
    public static function buildEntryBlocks(array $entry): array
    {
        $blocks   = [];
        $parsed   = self::parseTenTruongHop($entry['ten_truong_hop'] ?? '');
        $diem     = (int) ($entry['diem'] ?? 0);
        $thapThan = $entry['thap_than'] ?? '';

        $blocks[] = [
            'type'     => 'thap_than_title',
            'title'    => $parsed['title_prefix'] . ' (' . $diem . '%)',
            'subtitle' => $parsed['subtitle'],
        ];

        $noiDungList = $entry['noi_dung'] ?? [];
        $imagePlaced = false;

        foreach ($noiDungList as $idx => $group) {
            $tieuDe = trim((string) ($group['tieu_de'] ?? ''));
            $lines  = (array) ($group['lines'] ?? []);

            if ($tieuDe !== '') {
                $blocks[] = ['type' => 'section_label', 'text' => $tieuDe];
            }

            // Insert the Thập Thần illustration right after the first section label
            if (! $imagePlaced) {
                $imagePath = self::getThapThanImagePath($thapThan);
                if ($imagePath !== null) {
                    $blocks[] = ['type' => 'image', 'path' => $imagePath];
                }
                $imagePlaced = true;
            }

            foreach ($lines as $line) {
                $line = trim((string) $line);
                if ($line !== '') {
                    $blocks[] = ['type' => 'para', 'text' => $line];
                }
            }
        }

        return $blocks;
    }

    /**
     * Build all blocks for the full Phần 7 Mục II.
     *
     * @param  array<int, array<string, mixed>>  $muc2  Entries must include a 'diem' key.
     * @return array<int, array<string, mixed>>
     */
    public static function buildAllBlocks(array $muc2): array
    {
        $all = [];
        foreach ($muc2 as $entry) {
            $all = array_merge($all, self::buildEntryBlocks($entry));
        }

        return $all;
    }
}
