<?php

namespace App\Services\Pdf;

/**
 * Chuẩn hóa whitespace cho nội dung PDF — trim field và từng dòng.
 */
class PdfTextSanitizer
{
    /** Trường nội dung nhiều dòng — trim từng dòng, bỏ dòng trống. */
    private const MULTILINE_KEYS = [
        'text', 'content', 'tichCuc', 'tieuCuc', 'giaiNghia', 'chienLuoc',
        'tongQuan', 'noiDung', 'noi_dung', 'para', 'filtered', 'body',
        'description', 'nhanXet', 'trangThai',
    ];

    public static function trimString(string $text): string
    {
        return trim($text);
    }

    public static function trimMultiline(string $text): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }

        $lines = [];
        foreach (preg_split('/\r\n|\r|\n/', $text) ?: [] as $line) {
            $line = trim($line);
            if ($line !== '') {
                $lines[] = $line;
            }
        }

        return implode("\n", $lines);
    }

    public static function trimByKey(string $key, string $value): string
    {
        if (in_array($key, self::MULTILINE_KEYS, true)) {
            return self::trimMultiline($value);
        }

        return self::trimString($value);
    }

    /**
     * @param  array<int, string>  $keywords
     * @return array<int, string>
     */
    public static function trimKeywordList(array $keywords): array
    {
        $out = [];
        foreach ($keywords as $kw) {
            $kw = self::trimString((string) $kw);
            if ($kw !== '') {
                $out[] = $kw;
            }
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array<string, mixed>
     */
    public static function trimBlock(array $block): array
    {
        foreach ($block as $key => $value) {
            if (is_string($value)) {
                $block[$key] = self::trimByKey((string) $key, $value);
            } elseif ($key === 'keywords' && is_array($value)) {
                $block[$key] = self::trimKeywordList($value);
            }
        }

        return $block;
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    public static function trimBlocks(array $blocks): array
    {
        return array_map([self::class, 'trimBlock'], $blocks);
    }

    /**
     * @param  array<int, array<string, mixed>>  $sections
     * @return array<int, array<string, mixed>>
     */
    public static function trimSections(array $sections): array
    {
        $out = [];
        foreach ($sections as $sec) {
            if (! is_array($sec)) {
                continue;
            }
            $label   = self::trimString((string) ($sec['label'] ?? ''));
            $content = self::trimMultiline((string) ($sec['content'] ?? ''));
            if ($label === '' && $content === '') {
                continue;
            }
            $out[] = array_merge($sec, [
                'label'   => $label,
                'content' => $content,
            ]);
        }

        return $out;
    }

    /**
     * @param  array<int, array<string, mixed>>  $pages
     * @return array<int, array<string, mixed>>
     */
    public static function trimPages(array $pages): array
    {
        return array_map(static function (array $page): array {
            if (isset($page['blocks']) && is_array($page['blocks'])) {
                $page['blocks'] = self::trimBlocks($page['blocks']);
            }
            if (isset($page['sections']) && is_array($page['sections'])) {
                $page['sections'] = self::trimSections($page['sections']);
            }
            if (isset($page['preambleBlocks']) && is_array($page['preambleBlocks'])) {
                $page['preambleBlocks'] = self::trimBlocks($page['preambleBlocks']);
            }

            foreach ([
                'title', 'subtitle', 'meta', 'truHeading', 'blockLabel',
                'continuationTitle', 'chapterTitle',
            ] as $key) {
                if (isset($page[$key]) && is_string($page[$key])) {
                    $page[$key] = self::trimString($page[$key]);
                }
            }

            return $page;
        }, $pages);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function trimCodingData(array $data): array
    {
        if (isset($data['sections']) && is_array($data['sections'])) {
            $data['sections'] = self::trimSections($data['sections']);
        }
        if (isset($data['preambleBlocks']) && is_array($data['preambleBlocks'])) {
            $data['preambleBlocks'] = self::trimBlocks($data['preambleBlocks']);
        }

        foreach ([
            'title', 'subtitle', 'meta', 'truHeading', 'blockLabel',
        ] as $key) {
            if (isset($data[$key]) && is_string($data[$key])) {
                $data[$key] = self::trimString($data[$key]);
            }
        }

        return $data;
    }

    public static function sanitizeDeep(mixed $value, ?string $key = null): mixed
    {
        if (is_string($value)) {
            return self::trimByKey($key ?? '', $value);
        }

        if (! is_array($value)) {
            return $value;
        }

        if ($key === 'keywords' && array_is_list($value)) {
            return self::trimKeywordList($value);
        }

        if ($key === 'blocks') {
            return self::trimBlocks($value);
        }

        if ($key === 'sections') {
            return self::trimSections($value);
        }

        if ($key === 'pages') {
            return self::trimPages($value);
        }

        if ($key === 'preambleBlocks') {
            return self::trimBlocks($value);
        }

        $out = [];
        foreach ($value as $k => $v) {
            $out[$k] = self::sanitizeDeep($v, is_string($k) ? $k : null);
        }

        return $out;
    }
}
