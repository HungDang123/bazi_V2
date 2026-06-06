<?php

namespace App\Services;

use App\Models\Phan5KhiaCanh;
use App\Models\Phan5ThapThanHinhAnh;

class Phan5KhiaCanhService
{
    /**
     * @param  array<string, mixed>  $payload  Dữ liệu đã build từ suNghiep()
     * @return array<int, array<string, mixed>>
     */
    public function buildKhiaCanhBlocks(array $payload): array
    {
        $thapThanImageMap = Phan5ThapThanHinhAnh::imageMapByThapThan();
        $layouts = Phan5KhiaCanh::getAllOrdered();
        $blocks = [];

        foreach ($layouts as $layout) {
            $slug = (string) $layout->slug;
            $items = match ($slug) {
                'su_nghiep' => $this->buildSuNghiepItems($payload, $thapThanImageMap),
                'tai_chinh' => $this->buildTaiChinhItems($payload, $thapThanImageMap),
                'tinh_duyen' => $this->buildTinhDuyenItems($payload, $thapThanImageMap),
                'phat_trien_ban_than' => $this->buildPhatTrienItems($payload, $thapThanImageMap),
                'ket_noi_xa_hoi' => $this->buildKetNoiItems($payload, $thapThanImageMap),
                default => [],
            };

            $blocks[] = [
                'slug' => $slug,
                'section_code' => $layout->section_code,
                'title' => $layout->title,
                'tong_quan' => trim((string) $layout->tong_quan),
                'image_vi_tri' => $this->resolveImageUrl((string) $layout->image_vi_tri),
                'items' => $items,
            ];
        }

        return $blocks;
    }

    /**
     * @param  array<string, string>  $imageMap
     * @return array<int, array<string, mixed>>
     */
    protected function buildSuNghiepItems(array $payload, array $imageMap): array
    {
        $items = [];

        $thang = $payload['su_nghiep_thang'] ?? null;
        if ($thang !== null) {
            $items[] = $this->makeItem(
                'su_nghiep',
                'Thiên Can Trụ Tháng',
                $payload['thien_can_thang'] ?? null,
                $payload['thap_than_thang'] ?? null,
                $imageMap,
                $this->normalizeSuNghiepContent($thang)
            );
        }

        $nam = $payload['su_nghiep_nam'] ?? null;
        if ($nam !== null) {
            $items[] = $this->makeItem(
                'su_nghiep',
                'Thiên Can Trụ Năm',
                $payload['thien_can_nam'] ?? null,
                $payload['thap_than_nam'] ?? null,
                $imageMap,
                $this->normalizeSuNghiepContent($nam)
            );
        }

        return $items;
    }

    /**
     * @param  array<string, string>  $imageMap
     * @return array<int, array<string, mixed>>
     */
    protected function buildTaiChinhItems(array $payload, array $imageMap): array
    {
        $items = [];
        $taiChinh = $payload['tai_chinh_tang_can'] ?? null;
        if (! is_array($taiChinh)) {
            return $items;
        }

        $thangList = is_array($taiChinh['thang'] ?? null) ? $taiChinh['thang'] : [];
        foreach ($thangList as $row) {
            $sections = $this->normalizeTaiChinhContent($row);
            if ($sections === []) {
                continue;
            }
            $items[] = $this->makeItem(
                'tai_chinh',
                'Tàng Can Trụ Tháng',
                $row['can_tang'] ?? null,
                $row['thap_than'] ?? null,
                $imageMap,
                $sections
            );
        }

        $gioList = is_array($taiChinh['gio'] ?? null) ? $taiChinh['gio'] : [];
        foreach ($gioList as $row) {
            $sections = $this->normalizeTaiChinhContent($row);
            if ($sections === []) {
                continue;
            }
            $items[] = $this->makeItem(
                'tai_chinh',
                'Tàng Can Trụ Giờ',
                $row['can_tang'] ?? null,
                $row['thap_than'] ?? null,
                $imageMap,
                $sections
            );
        }

        return $items;
    }

    /**
     * @param  array<string, string>  $imageMap
     * @return array<int, array<string, mixed>>
     */
    protected function buildTinhDuyenItems(array $payload, array $imageMap): array
    {
        $items = [];
        $list = $payload['tinh_cam_tang_can_ngay'] ?? null;
        if (! is_array($list)) {
            return $items;
        }

        foreach ($list as $row) {
            $sections = $this->normalizeFullSections($row);
            if ($sections === []) {
                continue;
            }
            $items[] = $this->makeItem(
                'tinh_duyen',
                'Tàng Can Trụ Ngày',
                $row['can_tang'] ?? null,
                $row['thap_than'] ?? null,
                $imageMap,
                $sections
            );
        }

        return $items;
    }

    /**
     * @param  array<string, string>  $imageMap
     * @return array<int, array<string, mixed>>
     */
    protected function buildPhatTrienItems(array $payload, array $imageMap): array
    {
        $items = [];
        $data = $payload['phat_trien_ban_than'] ?? null;
        if (! is_array($data)) {
            return $items;
        }

        $thienCanGio = $data['thien_can_gio'] ?? null;
        if (is_array($thienCanGio)) {
            $sections = $this->normalizeFullSections($thienCanGio);
            if ($sections !== []) {
                $items[] = $this->makeItem(
                    'phat_trien_ban_than',
                    'Thiên Can Trụ Giờ',
                    null,
                    $thienCanGio['thap_than'] ?? null,
                    $imageMap,
                    $sections
                );
            }
        }

        $tangCanGio = $data['tang_can_gio'] ?? null;
        if (is_array($tangCanGio)) {
            $sections = $this->normalizeFullSections($tangCanGio);
            if ($sections !== []) {
                $items[] = $this->makeItem(
                    'phat_trien_ban_than',
                    'Tàng Can Trụ Giờ',
                    $tangCanGio['can_tang'] ?? null,
                    $tangCanGio['thap_than'] ?? null,
                    $imageMap,
                    $sections
                );
            }
        }

        return $items;
    }

    /**
     * @param  array<string, string>  $imageMap
     * @return array<int, array<string, mixed>>
     */
    protected function buildKetNoiItems(array $payload, array $imageMap): array
    {
        $items = [];
        $data = $payload['ket_noi_xa_hoi'] ?? null;
        if (! is_array($data)) {
            return $items;
        }

        $thienCanNam = $data['thien_can_nam'] ?? null;
        if (is_array($thienCanNam)) {
            $sections = $this->normalizeFullSections($thienCanNam);
            if ($sections !== []) {
                $items[] = $this->makeItem(
                    'ket_noi_xa_hoi',
                    'Thiên Can Trụ Năm',
                    null,
                    $thienCanNam['thap_than'] ?? null,
                    $imageMap,
                    $sections
                );
            }
        }

        $tangCanList = is_array($data['tang_can_nam'] ?? null) ? $data['tang_can_nam'] : [];
        foreach ($tangCanList as $row) {
            $sections = $this->normalizeFullSections($row);
            if ($sections === []) {
                continue;
            }
            $items[] = $this->makeItem(
                'ket_noi_xa_hoi',
                'Tàng Can Trụ Năm',
                $row['can_tang'] ?? null,
                $row['thap_than'] ?? null,
                $imageMap,
                $sections
            );
        }

        return $items;
    }

    /**
     * @param  array<int, array{label: string, content: string, tone?: string}>  $sections
     * @return array<string, mixed>
     */
    protected function makeItem(
        string $khiaSlug,
        string $label,
        ?string $can,
        ?string $thapThan,
        array $imageMap,
        array $sections
    ): array {
        $thapThan = trim((string) $thapThan);

        return [
            'label' => $label,
            'can' => $can !== null && trim($can) !== '' ? trim($can) : null,
            'thap_than' => $thapThan !== '' ? $thapThan : null,
            'image_minh_hoa' => $this->resolveItemImageUrl($khiaSlug, $label, $thapThan, $imageMap),
            'sections' => $sections,
        ];
    }

    /**
     * @param  array<string, string>  $imageMap
     */
    protected function resolveItemImageUrl(string $khiaSlug, string $itemLabel, string $thapThan, array $imageMap): ?string
    {
        $url = Phan5MinhHoaService::publicUrl($khiaSlug, $itemLabel);
        if ($url !== null) {
            return $url;
        }

        return $this->resolveThapThanImageUrl($thapThan, $imageMap);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, array{label: string, content: string, tone?: string}>
     */
    protected function normalizeSuNghiepContent(array $data): array
    {
        return $this->normalizeFullSections($data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, array{label: string, content: string, tone?: string}>
     */
    protected function normalizeFullSections(array $data): array
    {
        if (! empty($data['sections']) && is_array($data['sections'])) {
            $out = [];
            foreach ($data['sections'] as $sec) {
                $content = trim((string) ($sec['content'] ?? ''));
                if ($content === '') {
                    continue;
                }
                $label = trim((string) ($sec['label'] ?? ''));
                $out[] = [
                    'label' => $label,
                    'content' => $content,
                    'tone' => $this->toneFromLabel($label),
                ];
            }

            return $out;
        }

        return $this->normalizeTichTieuContent($data);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<int, array{label: string, content: string, tone?: string}>
     */
    protected function normalizeTaiChinhContent(array $row): array
    {
        $sections = [];
        $taiChinh = trim((string) ($row['tai_chinh'] ?? ''));
        if ($taiChinh !== '') {
            $sections[] = [
                'label' => 'Tài chính',
                'content' => $taiChinh,
                'tone' => 'neutral',
            ];
        }

        return $sections;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<int, array{label: string, content: string, tone?: string}>
     */
    protected function normalizeTichTieuContent(array $row): array
    {
        $sections = [];
        $tichCuc = trim((string) ($row['tich_cuc'] ?? ''));
        $tieuCuc = trim((string) ($row['tieu_cuc'] ?? ''));

        if ($tichCuc !== '') {
            $sections[] = ['label' => 'Mặt tích cực', 'content' => $tichCuc, 'tone' => 'positive'];
        }
        if ($tieuCuc !== '') {
            $sections[] = ['label' => 'Mặt tiêu cực', 'content' => $tieuCuc, 'tone' => 'negative'];
        }

        return $sections;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<int, array{label: string, content: string, tone?: string}>
     */
    protected function normalizePhatTrienContent(array $row): array
    {
        $sections = [];
        $phatTrien = $row['phat_trien_ban_than'] ?? null;
        if (is_array($phatTrien)) {
            $sections = array_merge($sections, $this->normalizeTichTieuBlock($phatTrien, 'Phát triển bản thân'));
        }
        $tinhCach = $row['tinh_cach'] ?? null;
        if (is_array($tinhCach)) {
            $sections = array_merge($sections, $this->normalizeTichTieuBlock($tinhCach, 'Tính cách'));
        }

        return $sections;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<int, array{label: string, content: string, tone?: string}>
     */
    protected function normalizeKetNoiContent(array $row): array
    {
        $sections = [];
        $moiQuanHe = $row['moi_quan_he_xa_hoi'] ?? $row['moi_quan_he'] ?? null;
        if (is_array($moiQuanHe)) {
            $sections = array_merge($sections, $this->normalizeTichTieuBlock($moiQuanHe, 'Mối quan hệ xã hội'));
        }
        $tinhCach = $row['tinh_cach_xa_hoi'] ?? $row['tinh_cach'] ?? null;
        if (is_array($tinhCach)) {
            $sections = array_merge($sections, $this->normalizeTichTieuBlock($tinhCach, 'Tính cách xã hội'));
        }

        return $sections;
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array<int, array{label: string, content: string, tone?: string}>
     */
    protected function normalizeTichTieuBlock(array $block, string $prefix): array
    {
        $sections = [];
        $tichCuc = trim((string) ($block['tich_cuc'] ?? ''));
        $tieuCuc = trim((string) ($block['tieu_cuc'] ?? ''));

        if ($tichCuc !== '') {
            $sections[] = [
                'label' => $prefix.' — Mặt tích cực',
                'content' => $tichCuc,
                'tone' => 'positive',
            ];
        }
        if ($tieuCuc !== '') {
            $sections[] = [
                'label' => $prefix.' — Mặt tiêu cực',
                'content' => $tieuCuc,
                'tone' => 'negative',
            ];
        }

        return $sections;
    }

    protected function toneFromLabel(string $label): string
    {
        $lower = mb_strtolower($label, 'UTF-8');
        if (str_contains($lower, 'tích cực')) {
            return 'positive';
        }
        if (str_contains($lower, 'tiêu cực')) {
            return 'negative';
        }
        if (str_contains($lower, 'chiến lược')) {
            return 'strategy';
        }

        return 'neutral';
    }

    protected function resolveImageUrl(string $path): ?string
    {
        $path = trim($path);
        if ($path === '') {
            return null;
        }

        return DocxTextService::publicUrlForMarkerPath($path);
    }

    /**
     * @param  array<string, string>  $imageMap
     */
    protected function resolveThapThanImageUrl(string $thapThan, array $imageMap): ?string
    {
        $thapThan = trim($thapThan);
        if ($thapThan === '') {
            return null;
        }

        $path = $imageMap[$thapThan] ?? null;
        if ($path === null || trim($path) === '') {
            return null;
        }

        return DocxTextService::publicUrlForMarkerPath(trim($path));
    }
}
