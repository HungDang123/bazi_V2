<?php

namespace App\Services;

class Phan8TruSectionService
{
    /**
     * @return array<string, string>
     */
    public static function nienVanTruOrder(): array
    {
        return [
            'nam' => 'Trụ Năm',
            'thang' => 'Trụ Tháng',
            'ngay' => 'Trụ Ngày',
            'gio' => 'Trụ Giờ',
        ];
    }

    public static function interactionTitle(int $displayIndex, int $year, string $truLabel): string
    {
        return $displayIndex.'. Sự tương tác giữa Niên Vận '.$year.' và '.$truLabel;
    }

    /**
     * @param  array<string, mixed>  $tru
     */
    public static function hasCodingContent(array $tru): bool
    {
        foreach (['thien_can', 'dia_chi'] as $field) {
            $raw = $tru[$field] ?? null;
            if ($raw === null) {
                continue;
            }

            $blocks = is_array($raw) && array_key_exists('moi_quan_he', $raw)
                ? [$raw]
                : (is_array($raw) ? $raw : []);

            foreach ($blocks as $block) {
                if (! is_array($block)) {
                    continue;
                }
                if (trim((string) ($block['moi_quan_he'] ?? '')) !== '') {
                    return true;
                }
            }
        }

        return false;
    }

    public static function stripTemplateBoilerplate(string $text): string
    {
        if ($text === '') {
            return '';
        }

        $kept = [];
        foreach (preg_split('/\r\n|\r|\n/', $text) ?: [] as $line) {
            $t = trim($line);
            if ($t === '') {
                continue;
            }
            if (preg_match('/^(Thiên Can|Địa Chi)\s+(Niên Vận|Năm|Tháng|Ngày|Giờ)\s*$/ui', $t)) {
                continue;
            }
            if (preg_match('/^Nếu có Hợp\s*\/\s*Khắc/ui', $t)) {
                continue;
            }
            if (preg_match('/^Nếu có Hợp\s*\/\s*Xung/ui', $t)) {
                continue;
            }
            if (preg_match('/^Thập Thần\s+ở\s+(Thiên Can|Địa Chi)\s+Trụ/ui', $t)) {
                continue;
            }
            $kept[] = $line;
        }

        return trim(implode("\n", $kept));
    }

    public static function stripInteractionTitleLine(string $text): string
    {
        if ($text === '') {
            return '';
        }

        $kept = [];
        foreach (preg_split('/\r\n|\r|\n/', $text) ?: [] as $line) {
            $t = trim($line);
            if ($t === '') {
                continue;
            }
            if (preg_match('/^\d+\.\s*Sự tương tác giữa (Niên Vận|Đại Vận)\s+/u', $t)) {
                continue;
            }
            $kept[] = $line;
        }

        return trim(implode("\n", $kept));
    }

    /**
     * @param  array<string, mixed>  $tru
     */
    public static function hasDisplayContent(array $tru): bool
    {
        if (self::hasCodingContent($tru)) {
            return true;
        }

        $intro = self::stripTemplateBoilerplate((string) ($tru['gioi_thieu']['noi_dung'] ?? ''));
        $intro = self::stripInteractionTitleLine($intro);

        return trim($intro) !== '';
    }

    public static function applyInteractionTitle(string $text, int $displayIndex, int $year, string $truLabel): string
    {
        $newTitle = self::interactionTitle($displayIndex, $year, $truLabel);
        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $replaced = false;

        foreach ($lines as $i => $line) {
            if (preg_match('/^\s*\d+\.\s*Sự tương tác giữa Niên Vận\s+/u', $line)) {
                $lines[$i] = $newTitle;
                $replaced = true;

                break;
            }
        }

        if (! $replaced) {
            array_unshift($lines, $newTitle);
        }

        return implode("\n", $lines);
    }

    public static function introBodyWithoutTitle(string $text): string
    {
        return self::stripInteractionTitleLine(
            self::stripTemplateBoilerplate($text)
        );
    }

    /**
     * Ẩn Trụ không có nội dung; đánh số lại 1, 2, 3… theo thứ tự hiển thị.
     *
     * @param  array<string, mixed>|null  $item
     * @return array<string, mixed>|null
     */
    public static function normalizeNienVanItem(?array $item): ?array
    {
        if ($item === null) {
            return null;
        }

        $year = (int) ($item['nam_number'] ?? 0);
        $displayIndex = 0;

        foreach (self::nienVanTruOrder() as $key => $truLabel) {
            $tru = $item[$key] ?? null;
            if (! is_array($tru) || ! self::hasDisplayContent($tru)) {
                $item[$key] = null;

                continue;
            }

            $displayIndex++;
            $title = self::interactionTitle($displayIndex, $year, $truLabel);
            $intro = trim((string) ($tru['gioi_thieu']['noi_dung'] ?? ''));
            $body = self::introBodyWithoutTitle($intro);

            if ($body !== '') {
                $tru['gioi_thieu']['noi_dung'] = $title."\n\n".$body;
            } elseif (self::hasCodingContent($tru)) {
                $tru['gioi_thieu'] = ['noi_dung' => $title];
            } else {
                $tru['gioi_thieu'] = null;
            }

            $item[$key] = $tru;
        }

        return $item;
    }
}
