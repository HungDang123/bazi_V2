<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @include('pdfs.partials.pdf-base-typography')
        @page { margin: 0; padding: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { width: 210mm; height: 297mm; }
        .page { position: relative; width: 210mm; height: 297mm; overflow: hidden; }
        .bg-img { position: absolute; top: 0; left: 0; width: 210mm; height: 297mm; }

        /* Chart image centered */
        .chart-img-wrap {
            position: absolute;
            top: 38mm;
            left: 0;
            width: 210mm;
            text-align: center;
        }
        .chart-img-wrap img { width: 130mm; height: auto; }

        /* Icons quanh radar */
        .icon-wrap {
            position: absolute;
            text-align: center;
        }
        .icon-wrap img { width: 12mm; height: 12mm; display: block; margin: 0 auto 0.8mm; }
        .icon-lbl-nh { font-size: 6.5pt; font-weight: bold; color: #333; white-space: nowrap; }

        /* Legend */
        .legend {
            position: absolute;
            top: 154mm;
            left: 28mm;
            font-size: 6.5pt;
            color: #444;
        }
        .legend-box {
            display: inline-block;
            width: 13px;
            height: 9px;
            vertical-align: middle;
            margin-right: 3px;
        }

        /* Bảng THẬP THẦN */
        .ts-table-wrap {
            position: absolute;
            top: 157mm;
            left: 14mm;
            width: 182mm;
        }
        .ts-tbl { width: 100%; border-collapse: collapse; }
        .ts-tbl th, .ts-tbl td {
            border: 0.4pt solid #fff;
            padding: 1mm 1.5mm;
            font-size: 7.5pt;
            vertical-align: middle;
            height: 7.5mm;
        }
        .ts-hdr th {
            background: #6E0101;
            color: #E5CA8E;
            font-weight: bold;
            height: 8mm;
            text-align: center;
            font-size: 8pt;
        }
        .ts-lbl-col {
            width: 25%;
            background: #6E0101;
            color: #E5CA8E;
            font-weight: bold;
            text-align: center;
            font-size: 7.5pt;
        }
        .bar-cell { width: 37.5%; padding: 1mm 2mm; }
        .ts-odd  td:not(.ts-lbl-col) { background: #F2EFE8; }
        .ts-even td:not(.ts-lbl-col) { background: #E9E5DE; }
        .bar-track {
            width: 100%;
            background: #CECECE;
            border-radius: 9px;
            height: 18px;
            overflow: hidden;
        }
        .bar-fill-bm {
            background: #0479FD;
            border-radius: 9px;
            height: 18px;
            font-size: 7.5px;
            color: #fff;
            text-align: center;
            padding-top: 3px;
            font-weight: bold;
        }
        .bar-fill-nv {
            background: #6E0101;
            border-radius: 9px;
            height: 18px;
            font-size: 7.5px;
            color: #fff;
            text-align: center;
            padding-top: 3px;
            font-weight: bold;
        }
        .bar-zero {
            height: 18px;
            font-size: 7.5px;
            color: #888;
            padding-top: 3px;
            padding-left: 6px;
        }
    </style>
</head>
<body>
<div class="page">

    <img class="bg-img" src="{{ $templatePath }}">

    @php
        // ═══════════════════════════════════════════════════
        // GD RADAR CHART — matching reference style
        // ═══════════════════════════════════════════════════
        $GW  = 600;
        $GH  = 500;
        $gcx = 300;
        $gcy = 252;
        $gR  = 182;  // outer radius

        $gAxes = [];
        for ($i = 0; $i < 5; $i++) {
            $gAxes[] = -M_PI / 2 + $i * 2 * M_PI / 5;
        }

        // Point on axis i at radius r
        $gpt = function (int $i, float $r) use ($gcx, $gcy, $gAxes): array {
            return [
                (int) round($gcx + $r * cos($gAxes[$i])),
                (int) round($gcy + $r * sin($gAxes[$i])),
            ];
        };

        $im = imagecreatetruecolor($GW, $GH);

        // Colors
        $bgC     = imagecolorallocate($im, 255, 255, 255);
        $goldC   = imagecolorallocate($im, 198, 158, 60);    // gold outer ring
        $gridC   = imagecolorallocate($im, 205, 200, 178);   // inner grid (tan)
        $axisC   = imagecolorallocate($im, 200, 194, 170);   // spoke lines
        $txtC    = imagecolorallocate($im, 125, 118, 95);    // label text
        $bmLine  = imagecolorallocate($im, 60, 145, 210);    // blue line
        $bmFill  = imagecolorallocatealpha($im, 60, 145, 210, 85);
        $nvLine  = imagecolorallocate($im, 138, 42, 42);     // dark-red line
        $nvFill  = imagecolorallocatealpha($im, 138, 42, 42, 85);

        imagefill($im, 0, 0, $bgC);
        imagealphablending($im, true);

        // 1. Spoke lines (axis)
        imagesetthickness($im, 1);
        for ($i = 0; $i < 5; $i++) {
            [$ex, $ey] = $gpt($i, $gR);
            imageline($im, $gcx, $gcy, $ex, $ey, $axisC);
        }

        // 2. Inner grid rings (4 levels: 20 40 60 80)
        foreach ([20, 40, 60, 80] as $pct) {
            $r = $gR * $pct / 100;
            $p = [];
            for ($i = 0; $i < 5; $i++) {
                [$px, $py] = $gpt($i, $r);
                $p[] = $px; $p[] = $py;
            }
            if (PHP_MAJOR_VERSION >= 8) imagepolygon($im, $p, $gridC);
            else imagepolygon($im, $p, 5, $gridC);
        }

        // 3. Outer pentagon (thick gold)
        imagesetthickness($im, 4);
        for ($i = 0; $i < 5; $i++) {
            [$x1, $y1] = $gpt($i, $gR);
            [$x2, $y2] = $gpt(($i + 1) % 5, $gR);
            imageline($im, $x1, $y1, $x2, $y2, $goldC);
        }
        imagesetthickness($im, 1);

        // 4. Labels on all 5 axes at each grid level
        $fIdx = 2;
        $fW   = imagefontwidth($fIdx);
        $fH   = imagefontheight($fIdx);

        foreach ([20, 40, 60, 80, 100] as $pct) {
            for ($i = 0; $i < 5; $i++) {
                $r   = $gR * $pct / 100;
                $a   = $gAxes[$i];
                $px  = (int) round($gcx + $r * cos($a));
                $py  = (int) round($gcy + $r * sin($a));
                $lbl = (string) $pct;
                $lw  = $fW * strlen($lbl);

                // Offset label relative to axis direction
                if (abs(cos($a)) < 0.1) {
                    // Near-vertical axis (top): label to the right
                    $ox = 3;
                    $oy = -(int)($fH / 2) - 1;
                } else {
                    $ox = cos($a) > 0 ? 3 : -($lw + 3);
                    $oy = sin($a) > 0 ? 2 : -($fH + 2);
                }

                imagestring($im, $fIdx, $px + $ox, $py + $oy, $lbl, $txtC);
            }
        }

        // 5. Data polygons
        $hasData = count($bieuDoNguHanh) >= 5;

        if ($hasData) {
            // Niên Vận (draw first / behind)
            $nvP = [];
            foreach (array_values($bieuDoNguHanh) as $idx => $ax) {
                $v = min(100, max(0, (float)($ax['diem_nien_van'] ?? 0)));
                [$px, $py] = $gpt($idx, $gR * $v / 100);
                $nvP[] = $px; $nvP[] = $py;
            }
            if (PHP_MAJOR_VERSION >= 8) { imagefilledpolygon($im, $nvP, $nvFill); imagepolygon($im, $nvP, $nvLine); }
            else { imagefilledpolygon($im, $nvP, 5, $nvFill); imagepolygon($im, $nvP, 5, $nvLine); }

            // Bản Mệnh (draw on top)
            $bmP = [];
            foreach (array_values($bieuDoNguHanh) as $idx => $ax) {
                $v = min(100, max(0, (float)($ax['diem_ngu_hanh'] ?? 0)));
                [$px, $py] = $gpt($idx, $gR * $v / 100);
                $bmP[] = $px; $bmP[] = $py;
            }
            if (PHP_MAJOR_VERSION >= 8) { imagefilledpolygon($im, $bmP, $bmFill); imagepolygon($im, $bmP, $bmLine); }
            else { imagefilledpolygon($im, $bmP, 5, $bmFill); imagepolygon($im, $bmP, 5, $bmLine); }
        }

        // Encode PNG as base64
        ob_start();
        imagepng($im);
        $chartPng = 'data:image/png;base64,' . base64_encode(ob_get_clean());
        imagedestroy($im);

        // ═══════════════════════════════════════════════════
        // Icon positions on the PDF page (mm)
        // Chart image: width=130mm, top=34mm, centered → left=40mm
        // ═══════════════════════════════════════════════════
        $cImgLeft = 40.0;
        $cImgTop  = 38.0;
        $cImgW    = 130.0;
        $cImgH    = $cImgW * ($GH / $GW);  // 130*(500/600) = 108.3mm

        // Chart center in page mm
        $pCX = $cImgLeft + $cImgW * ($gcx / $GW);   // 40+65 = 105mm
        $pCY = $cImgTop  + $cImgH * ($gcy / $GH);   // 34+54.6 = 88.6mm

        // Pentagon outer radius in mm
        $pRX = $cImgW * ($gR / $GW);   // 130*182/600 = 39.4mm
        $pRY = $cImgH * ($gR / $GH);   // 108.3*182/500 = 39.4mm

        // Icon offset beyond pentagon edge
        $offMm = 13.5;

        $iconMap = [
            'Thê Tài'  => 'the-tai',  'Thể Tài'  => 'the-tai',  'Thê tài'  => 'the-tai',
            'Quan Quỷ' => 'quan-quy', 'Quan quỷ' => 'quan-quy',
            'Phụ Mẫu'  => 'phu-mau',  'Phụ mẫu'  => 'phu-mau',
            'Huynh Đệ' => 'huynh-de', 'Huynh đệ' => 'huynh-de',
            'Tử Tôn'   => 'tu-ton',   'Tử tôn'   => 'tu-ton',
        ];

        $iW    = 12;   // icon img mm
        $lblW  = 26;   // container width mm

        $iconPositions = [];
        foreach (array_values($bieuDoNguHanh) as $idx => $ax) {
            $a     = $gAxes[$idx];
            $icX   = $pCX + ($pRX + $offMm) * cos($a);
            $icY   = $pCY + ($pRY + $offMm) * sin($a);
            $iconPositions[] = [
                'left' => round($icX - $lblW / 2, 2),
                'top'  => round($icY - $iW / 2 - 1, 2),
                'ax'   => $ax,
            ];
        }

        $nienMenhYear = $nienVanYear ?? date('Y');
        $rowIdx = 0;
    @endphp

    {{-- ── Radar chart image ── --}}
    <div class="chart-img-wrap">
        <img src="{{ $chartPng }}">
    </div>

    {{-- ── Icons quanh chart ── --}}
    @foreach($iconPositions as $pos)
    @php
        $ax       = $pos['ax'];
        $lucThan  = $ax['luc_than']     ?? '';
        $nguHanh  = $ax['ten_ngu_hanh'] ?? '';
        $iconFile = $iconMap[$lucThan]   ?? 'the-tai';
        $iconPath = $iconDir . '/' . $iconFile . '.png';
    @endphp
    <div class="icon-wrap"
         style="left:{{ $pos['left'] }}mm; top:{{ $pos['top'] }}mm; width:26mm;">
        <img src="{{ $iconPath }}">
        <div class="icon-lbl-nh">{{ $lucThan }}</div>
    </div>
    @endforeach

    {{-- ── CHẤT LƯỢNG THẬP THẦN ── --}}
    <div class="ts-table-wrap">
        <table class="ts-tbl">
            <thead>
                <tr class="ts-hdr">
                    <th style="width:25%;"></th>
                    <th style="width:37.5%;">BẢN MỆNH</th>
                    <th style="width:37.5%;">NIÊN MỆNH {{ $nienMenhYear }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($chatLuongThapThan as $item)
                @php
                    $cls   = ($rowIdx % 2 === 0) ? 'ts-odd' : 'ts-even';
                    $bmPct = min(100, max(0, (int)($item['natal']  ?? 0)));
                    $nvPct = min(100, max(0, (int)($item['annual'] ?? 0)));
                    $rowIdx++;
                @endphp
                <tr class="{{ $cls }}">
                    <td class="ts-lbl-col">{{ $item['name'] ?? '' }}</td>
                    <td class="bar-cell">
                        <div class="bar-track">
                            @if($bmPct > 0)
                            <div class="bar-fill-bm" style="width:{{ $bmPct }}%;">{{ $bmPct }}%</div>
                            @else
                            <div class="bar-zero">0%</div>
                            @endif
                        </div>
                    </td>
                    <td class="bar-cell">
                        <div class="bar-track">
                            @if($nvPct > 0)
                            <div class="bar-fill-nv" style="width:{{ $nvPct }}%;">{{ $nvPct }}%</div>
                            @else
                            <div class="bar-zero">0%</div>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
</body>
</html>
