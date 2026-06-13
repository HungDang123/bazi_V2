<?php

namespace App\Services\Pdf;

use App\Services\PdfFooterService;

/**
 * Theo dõi vị trí trang vật lý và bookmark mục lục khi ghép PDF.
 */
class PdfTocTracker
{
    public const TOC_INSERT_PHYSICAL = 5;

    private int $physicalPage = 0;

    /** @var array<string, array{label: string, physical: int}> */
    private array $bookmarks = [];

    public function physicalPage(): int
    {
        return $this->physicalPage;
    }

    public function addSegment(string $path): int
    {
        if ($path === '' || ! is_file($path)) {
            return $this->physicalPage + 1;
        }

        $start = $this->physicalPage + 1;
        $this->physicalPage += PdfFooterService::countPdfPages($path);

        return $start;
    }

    public function mark(string $key, string $label): void
    {
        $this->bookmarks[$key] = [
            'label'  => trim($label),
            'physical' => $this->physicalPage + 1,
        ];
    }

    public function markAtPhysical(string $key, string $label, int $physical): void
    {
        $this->bookmarks[$key] = [
            'label'    => trim($label),
            'physical' => max(1, $physical),
        ];
    }

    /**
     * Quét pages[] trong spec PDF (la-so-phan-8-content, …) và đánh dấu chapter_title.
     *
     * @param  array<string, string>  $prefixMap  prefix uppercase => bookmark key
     */
    public function markChaptersFromSpec(array $spec, int $segmentStartPhysical, array $prefixMap): void
    {
        $pages = $spec['data']['pages'] ?? [];
        if (! is_array($pages)) {
            return;
        }

        foreach ($pages as $pageIdx => $page) {
            if (! is_array($page)) {
                continue;
            }

            $physical = $segmentStartPhysical + (int) $pageIdx;
            $candidates = [];

            if (! empty($page['chapterTitle'])) {
                $candidates[] = (string) $page['chapterTitle'];
            }

            foreach ($page['blocks'] ?? [] as $block) {
                if (! is_array($block)) {
                    continue;
                }
                $type = (string) ($block['type'] ?? '');
                if (in_array($type, ['chapter_title', 'section_title'], true)) {
                    $candidates[] = (string) ($block['text'] ?? '');
                }
            }

            foreach ($candidates as $text) {
                $upper = mb_strtoupper(trim($text));
                if ($upper === '') {
                    continue;
                }
                foreach ($prefixMap as $prefix => $key) {
                    if (isset($this->bookmarks[$key])) {
                        continue;
                    }
                    if (str_starts_with($upper, mb_strtoupper($prefix))) {
                        $this->markAtPhysical($key, $text, $physical);
                    }
                }
            }
        }
    }

    /**
     * @return array<string, array{label: string, physical: int}>
     */
    public function bookmarks(): array
    {
        return $this->bookmarks;
    }

    public function hasBookmark(string $key): bool
    {
        return isset($this->bookmarks[$key]);
    }

    public function bookmarkPhysical(string $key): ?int
    {
        return $this->bookmarks[$key]['physical'] ?? null;
    }

    public function displayPage(int $physical, int $actualTocPages = 1): int
    {
        if ($physical < PdfFooterService::FIRST_FOOTER_PAGE) {
            return 0;
        }

        $adjusted = $physical;
        if ($physical >= self::TOC_INSERT_PHYSICAL) {
            $adjusted += max(1, $actualTocPages);
        }

        return PdfFooterService::FIRST_DISPLAY_PAGE_NUMBER
            + ($adjusted - PdfFooterService::FIRST_FOOTER_PAGE);
    }

    public function bookmarkDisplayPage(string $key, int $actualTocPages = 1): ?int
    {
        $physical = $this->bookmarkPhysical($key);
        if ($physical === null) {
            return null;
        }

        $page = $this->displayPage($physical, $actualTocPages);

        return $page > 0 ? $page : null;
    }
}
