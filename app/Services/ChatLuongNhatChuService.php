<?php

namespace App\Services;

use App\Models\ChatLuongNhatChu;
use App\Models\DiaChiThang;

class ChatLuongNhatChuService
{
    /**
     * @param  array  $batTu  bat_tu từ BaZiServiceV2
     * @return array{mua_sinh: string|null, ngu_hanh_mua_sinh: string|null, items: array<int, array{title: string|null, content: string|null, trang_thai: string|null}>}
     */
    public static function buildFromBatTu(array $batTu): array
    {
        $diaChi = trim((string) ($batTu['month']['chi']['dia_chi'] ?? ''));
        if ($diaChi === 'Tí') {
            $diaChi = 'Tý';
        }

        $diaChiModel = DiaChiThang::findByDiaChi($diaChi);
        if (! $diaChiModel) {
            return [
                'mua_sinh'          => null,
                'ngu_hanh_mua_sinh' => null,
                'items'             => [],
            ];
        }

        $thienCanNgay = trim((string) ($batTu['day']['can']['thien_can'] ?? ''));
        $records      = ChatLuongNhatChu::findByThienCanMuaSinh($thienCanNgay, $diaChiModel->mua_sinh);
        $items        = [];

        foreach ($records as $r) {
            $items[] = [
                'title'      => $r->title,
                'content'    => $r->content,
                'trang_thai' => $r->trang_thai,
            ];
        }

        return [
            'mua_sinh'          => $diaChiModel->mua_sinh,
            'ngu_hanh_mua_sinh' => $diaChiModel->ngu_hanh_mua_sinh,
            'items'             => $items,
        ];
    }

    public static function hasContent(array $view): bool
    {
        return ! empty($view['mua_sinh'])
            || ! empty($view['ngu_hanh_mua_sinh'])
            || ! empty($view['items']);
    }

    /**
     * @return array<int, array{type: string, text: string}>
     */
    public static function toPdfBlocks(array $view): array
    {
        $blocks = [];
        $meta   = [];

        if (! empty($view['mua_sinh'])) {
            $meta[] = 'Mùa sinh: '.$view['mua_sinh'];
        }
        if (! empty($view['ngu_hanh_mua_sinh'])) {
            $meta[] = 'Ngũ hành mùa sinh: '.$view['ngu_hanh_mua_sinh'];
        }
        if ($meta !== []) {
            $blocks[] = ['type' => 'para', 'text' => implode('  |  ', $meta)];
        }

        foreach ($view['items'] ?? [] as $item) {
            $title = trim((string) ($item['title'] ?? ''));
            if ($title !== '') {
                $blocks[] = ['type' => 'sub_title', 'text' => $title];
            }

            $trangThai = trim((string) ($item['trang_thai'] ?? ''));
            if ($trangThai !== '') {
                $blocks[] = ['type' => 'para', 'text' => $trangThai, 'emphasis' => true];
            }

            $content = trim((string) ($item['content'] ?? ''));
            if ($content !== '') {
                $blocks[] = ['type' => 'para', 'text' => $content];
            }
        }

        return $blocks;
    }

    /**
     * @return array<int, array{bgPath: string, chapterTitle: string, blocks: array<int, array<string, mixed>>}>
     */
    public static function buildPdfPages(
        array $view,
        string $firstBgPath,
        string $contBgPath,
        string $chapterTitle = 'III. CHẤT LƯỢNG NHẬT CHỦ'
    ): array {
        if (! self::hasContent($view)) {
            return [];
        }

        return Phan3PdfPaginator::paginateFlatBlocks(
            $chapterTitle,
            self::toPdfBlocks($view),
            $firstBgPath,
            $contBgPath
        );
    }
}
