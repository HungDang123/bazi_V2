<?php

namespace App\Services;

use App\Models\Phan7BaiHoc;
use Illuminate\Http\Request;

class Phan7MucIIPdfService
{
    public static function contentBgPath(): string
    {
        return resource_path('views/pdfs/phan-7/page-content-bg.png');
    }

    /**
     * Build the Blade view spec for dynamic Phần 7 Mục II pages.
     *
     * @return array{view: string, data: array{pages: array<int, mixed>}}|null
     */
    public static function buildContentPageSpec(Request $req): ?array
    {
        $muc2 = self::buildMuc2Data($req);

        if ($muc2 === []) {
            return null;
        }

        $blocks = Phan7ContentService::buildAllBlocks($muc2);

        if ($blocks === []) {
            return null;
        }

        $rawPages = Phan7PdfPaginator::paginate($blocks, self::contentBgPath());

        if ($rawPages === []) {
            return null;
        }

        return [
            'view' => 'pdfs.phan-7.la-so-phan-7-muc2-content',
            'data' => ['pages' => $rawPages],
        ];
    }

    /**
     * Compute Mục II entries (with actual `diem` scores) from the request birth data.
     *
     * @return array<int, array<string, mixed>>
     */
    private static function buildMuc2Data(Request $req): array
    {
        $fullName = (string) $req->input('full_name', '');
        $y        = (int) $req->input('y', 0);
        $m        = (int) $req->input('m', 0);
        $d        = (int) $req->input('d', 0);
        $h        = $req->input('h') !== null ? (int) $req->input('h') : null;
        $minute   = $req->input('minute') !== null ? (int) $req->input('minute') : null;
        $g        = (string) $req->input('g', 'male');

        if ($y === 0 || $m === 0 || $d === 0) {
            return [];
        }

        /** @var \App\Services\BaZiServiceV2 $bazi */
        $bazi   = app(BaZiServiceV2::class);
        $result = $bazi->calc($fullName, $y, $m, $d, $h, $minute, $g, needStrength: true);

        // Aggregate Ngũ Hành scores by Lục Thần
        $diemLucThanAgg = [];
        foreach ((array) ($result['bieu_do_ngu_hanh'] ?? []) as $row) {
            $lucThan = trim((string) ($row['luc_than'] ?? ''));
            if ($lucThan === '') {
                continue;
            }
            $diem = (int) ($row['diem_ngu_hanh'] ?? 0);
            if (! isset($diemLucThanAgg[$lucThan])) {
                $diemLucThanAgg[$lucThan] = ['sum' => 0, 'count' => 0];
            }
            $diemLucThanAgg[$lucThan]['sum']   += $diem;
            $diemLucThanAgg[$lucThan]['count'] += 1;
        }

        $diemLucThan = [];
        foreach ($diemLucThanAgg as $lucThan => $agg) {
            $count               = max(1, (int) $agg['count']);
            $diemLucThan[$lucThan] = (int) round($agg['sum'] / $count);
        }

        $mapThapThanToLucThan = [
            'HUYNH ĐỆ' => 'Huynh Đệ',
            'TỬ TÔN'   => 'Tử Tôn',
            'QUAN QUỶ' => 'Quan Quỷ',
            'THÊ TÀI'  => 'Thê Tài',
            'PHỤ MẪU'  => 'Phụ Mẫu',
        ];

        $muc2 = [];

        foreach (Phan7BaiHoc::THAP_THAN_ORDER as $thapThan) {
            $lucThanKey = $mapThapThanToLucThan[$thapThan] ?? null;
            $diem       = $lucThanKey !== null ? ($diemLucThan[$lucThanKey] ?? 0) : 0;

            $rows = Phan7BaiHoc::getByThapThan($thapThan);

            // Find the matching trường hợp bucket
            $matchedTruongHop = null;
            foreach ($rows as $r) {
                $bucket = Phan7ContentService::parseBucket($r->ten_truong_hop ?? '');
                if ($bucket !== null && $diem >= $bucket['min'] && $diem <= $bucket['max']) {
                    $matchedTruongHop = $r->ten_truong_hop;
                    break;
                }
            }

            if ($matchedTruongHop === null) {
                continue;
            }

            // Group content lines by tieu_de
            $matchedRows = $rows->filter(fn ($r) => $r->ten_truong_hop === $matchedTruongHop);
            $grouped     = [];
            foreach ($matchedRows as $r) {
                $grouped[$r->tieu_de ?? ''][] = $r->noi_dung;
            }

            $noiDungList = [];
            foreach ($grouped as $tieuDe => $lines) {
                $noiDungList[] = ['tieu_de' => $tieuDe, 'lines' => $lines];
            }

            $muc2[] = [
                'thap_than'      => $thapThan,
                'ten_truong_hop' => $matchedTruongHop,
                'diem'           => $diem,
                'noi_dung'       => $noiDungList,
            ];
        }

        return $muc2;
    }
}
