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
        .chart-wrap { position: absolute; left: 0; width: 210mm; text-align: center; }
        .chart-wrap img { width: 168mm; height: auto; }
        .x-label {
            position: absolute;
            text-align: center;
            font-size: 6.5pt;
            color: #333;
            width: 28mm;
            line-height: 1.3;
        }
        .legend-wrap {
            position: absolute;
            left: 0;
            width: 210mm;
            text-align: center;
            font-size: 7pt;
            color: #444;
        }
        .legend-box {
            display: inline-block;
            width: 14px;
            height: 12px;
            vertical-align: middle;
            margin-right: 3px;
        }
    </style>
</head>
<body>
<div class="page">

    <img class="bg-img" src="{{ $templatePath }}">

    @php
        // ═══════════════════════════════════════════════════════
        // GD Bar Chart — Biểu đồ 6 khía cạnh
        // ═══════════════════════════════════════════════════════
        $GW  = 580;  // image width px
        $GH  = 360;  // image height px
        $lM  = 52;   // left margin (Y-axis labels)
        $rM  = 12;   // right margin
        $tM  = 22;   // top margin
        $bM  = 12;   // bottom margin

        $cX1 = $lM;          // chart left
        $cX2 = $GW - $rM;    // chart right = 568
        $cY1 = $tM;          // chart top
        $cY2 = $GH - $bM;    // chart bottom = 348
        $cW  = $cX2 - $cX1;  // chart width = 516
        $cH  = $cY2 - $cY1;  // chart height = 326

        $nGroups = 6;
        $groupW  = $cW / $nGroups;   // 86px
        $barW    = 30;
        $barGap  = 5;
        $pairW   = 2 * $barW + $barGap;  // 65px
        $sidePad = ($groupW - $pairW) / 2; // ~10.5px

        // Colors
        $im    = imagecreatetruecolor($GW, $GH);
        $bgC   = imagecolorallocate($im, 255, 255, 255);
        $gridC = imagecolorallocate($im, 228, 228, 228);
        $axisC = imagecolorallocate($im, 180, 180, 180);
        $txtC  = imagecolorallocate($im, 90, 90, 90);
        $whtC  = imagecolorallocate($im, 255, 255, 255);
        $bmC   = imagecolorallocate($im, 75, 192, 235);   // light blue (Bản Mệnh)
        $nvC   = imagecolorallocate($im, 110, 1, 1);      // dark red  (Niên Vận)

        imagefill($im, 0, 0, $bgC);

        // Y-axis gridlines & labels
        foreach ([0, 20, 40, 60, 80, 100] as $pct) {
            $y = (int) round($cY2 - $cH * $pct / 100);
            imageline($im, $cX1, $y, $cX2, $y, $gridC);
            $lbl = $pct . '%';
            $lw  = imagefontwidth(2) * strlen($lbl);
            $lh  = imagefontheight(2);
            imagestring($im, 2, $cX1 - $lw - 5, $y - (int)($lh / 2), $lbl, $txtC);
        }

        // Left axis line
        imageline($im, $cX1, $cY1, $cX1, $cY2, $axisC);
        // Bottom axis line
        imageline($im, $cX1, $cY2, $cX2, $cY2, $axisC);

        // ── Extract data ──────────────────────────────────────
        $natal  = $chiSoBieuDoCot['natal']  ?? [];
        $annual = $chiSoBieuDoCot['annual'] ?? [];
        $tinhCamKey = ($gender === 'female') ? 'tinh_cam_nu' : 'tinh_cam_nam';

        $groups = [
            [$natal['su_nghiep'] ?? 0,            $annual['su_nghiep'] ?? 0],
            [$natal['tai_chinh'] ?? 0,             $annual['tai_chinh'] ?? 0],
            [$natal[$tinhCamKey] ?? 0,             $annual[$tinhCamKey] ?? 0],
            [$natal['suc_khoe'] ?? 0,              $annual['suc_khoe'] ?? 0],
            [$natal['phat_trien_ban_than'] ?? 0,   $annual['phat_trien_ban_than'] ?? 0],
            [$natal['ket_noi_xa_hoi'] ?? 0,        $annual['ket_noi_xa_hoi'] ?? 0],
        ];

        // ── Draw bars ─────────────────────────────────────────
        foreach ($groups as $gi => [$bmVal, $nvVal]) {
            $baseX = (int) round($cX1 + $gi * $groupW + $sidePad);

            foreach ([[$bmVal, $bmC, 0], [$nvVal, $nvC, 1]] as [$val, $color, $bi]) {
                $v    = min(100.0, max(0.0, (float) $val));
                $barH = (int) round($cH * $v / 100);
                $x1   = $baseX + $bi * ($barW + $barGap);
                $x2   = $x1 + $barW - 1;
                $y2   = $cY2;
                $y1   = $y2 - $barH;

                if ($barH > 0) {
                    imagefilledrectangle($im, $x1, $y1, $x2, $y2, $color);
                }

                // Value label
                $pctLbl = (int) round($v) . '%';
                $fIdx   = 2;
                $fw     = imagefontwidth($fIdx) * strlen($pctLbl);
                $fh     = imagefontheight($fIdx);
                $lx     = $x1 + (int)(($barW - $fw) / 2);

                if ($barH >= $fh + 6) {
                    // Inside bar, near top – white text
                    $ly = $y1 + 3;
                    imagestring($im, $fIdx, $lx, $ly, $pctLbl, $whtC);
                } else {
                    // Above bar – dark text
                    $ly = max($cY1, $y1 - $fh - 2);
                    imagestring($im, $fIdx, $lx, $ly, $pctLbl, $txtC);
                }
            }
        }

        // Encode PNG
        ob_start();
        imagepng($im);
        $chartPng = 'data:image/png;base64,' . base64_encode(ob_get_clean());
        imagedestroy($im);

        // ── X-axis label positions (mm) ───────────────────────
        $cImgW    = 168.0;
        $cImgH    = $cImgW * ($GH / $GW);   // 168*(360/580) ≈ 104.3mm
        $cImgLeft = (210.0 - $cImgW) / 2;   // 21mm
        $cImgTop  = 39.0;
        $scale    = $cImgW / $GW;            // 0.2897 mm/px

        $xAxisLabels = [
            'Sự nghiệp',
            'Tài chính',
            'Tình duyên',
            'Sức khỏe',
            'Phát triển bản thân',
            'Kết nối xã hội',
        ];

        $labelW   = 30.0;  // mm
        $labelTop = $cImgTop + $cImgH + 1.5;  // mm (just below chart)

        $labelPositions = [];
        foreach ($xAxisLabels as $gi => $label) {
            $centerPx = $cX1 + $gi * $groupW + $groupW / 2;
            $centerMm = $cImgLeft + $centerPx * $scale;
            $labelPositions[] = [
                'left'  => round($centerMm - $labelW / 2, 2),
                'label' => $label,
            ];
        }

        $legendTop = $labelTop + 9;
    @endphp

    {{-- Chart image --}}
    <div class="chart-wrap" style="top:{{ $cImgTop }}mm;">
        <img src="{{ $chartPng }}">
    </div>

    {{-- X-axis category labels --}}
    @foreach($labelPositions as $pos)
    <div class="x-label"
         style="left:{{ $pos['left'] }}mm; top:{{ round($labelTop, 2) }}mm;">
        {{ $pos['label'] }}
    </div>
    @endforeach

    {{-- Legend --}}
    <div class="legend-wrap" style="top:{{ round($legendTop, 2) }}mm;">
        <span class="legend-box" style="background:#4BC0EB;"></span>Bản mệnh
        &nbsp;&nbsp;&nbsp;
        <span class="legend-box" style="background:#6E0101;"></span>Niên mệnh
    </div>

</div>
</body>
</html>
