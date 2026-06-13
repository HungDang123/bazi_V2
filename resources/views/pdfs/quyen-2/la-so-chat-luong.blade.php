<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; padding: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @include('pdfs.partials.pdf-justify-styles')
        body {
            width: 210mm; height: 297mm;
            font-family: 'svn-poppins', sans-serif;
            font-size: 14px;
            font-weight: normal;
            line-height: 140%;
            text-align: justify;
            letter-spacing: 0;
        }
        .page { position: relative; width: 210mm; height: 297mm; overflow: hidden; }
        .bg-img { position: absolute; top: 0; left: 0; width: 210mm; height: 297mm; }

        .chart-img-wrap {
            position: absolute;
            top: 36mm;
            left: 0;
            width: 210mm;
            text-align: center;
        }
        .chart-img-wrap img {
            width: 138mm;
            height: auto;
            display: inline-block;
        }

        .icon-wrap {
            position: absolute;
            text-align: center;
            width: 26mm;
        }
        .icon-wrap img {
            width: 11mm;
            height: 11mm;
            display: block;
            margin: 0 auto 0.6mm;
        }
        .icon-lbl-nh {
            font-size: 6.5pt;
            font-weight: bold;
            color: #333333;
            line-height: 1.25;
            text-align: center;
        }
        .icon-lbl-lt {
            font-size: 6pt;
            color: #555555;
            line-height: 1.2;
            text-align: center;
        }

        .ts-table-wrap {
            position: absolute;
            top: 157mm;
            left: 14mm;
            width: 182mm;
        }
        .ts-tbl {
            width: 182mm;
            table-layout: fixed;
            border-collapse: collapse;
            border-spacing: 0;
            background: #ffffff;
        }
        .ts-tbl col.col-lbl { width: 46mm; }
        .ts-tbl col.col-bar { width: 68mm; }
        .ts-tbl th,
        .ts-tbl td {
            border: 1px solid #ffffff;
            padding: 0;
            margin: 0;
            vertical-align: middle;
        }
        .ts-hdr th {
            background: #6E0101;
            color: #E5CA8E;
            font-weight: bold;
            height: 8mm;
            text-align: center;
            font-size: 8pt;
            line-height: 8mm;
            padding: 0 1mm;
        }
        .ts-tbl tbody tr {
            height: 8mm;
        }
        .ts-lbl-col {
            background: #6E0101;
            color: #E5CA8E;
            font-weight: bold;
            text-align: center !important;
            font-size: 7.5pt;
            line-height: 8mm;
            height: 8mm;
            padding: 0 2.5mm;
        }
        .bar-cell {
            height: 8mm;
            padding: 1.6mm 2mm 0;
            vertical-align: middle;
            background: #EBE7E0;
            text-align: left !important;
        }
        .bar-track {
            width: 100%;
            height: 4.8mm;
            background: #D5D5D5;
            border-radius: 2.4mm;
            overflow: hidden;
        }
        .bar-inner {
            width: 100%;
            height: 4.8mm;
            border-collapse: collapse;
            border-spacing: 0;
            table-layout: fixed;
        }
        .bar-inner td {
            height: 4.8mm;
            padding: 0;
            margin: 0;
            border: 0;
            vertical-align: middle;
        }
        .bar-fill {
            font-size: 7pt;
            font-weight: bold;
            line-height: 4.8mm;
            color: #ffffff;
            text-align: right !important;
            padding-right: 1.5mm;
            white-space: nowrap;
        }
        .bar-fill-bm {
            background: #4169E1;
            border-radius: 2.4mm 0 0 2.4mm;
        }
        .bar-fill-nv {
            background: #6E0101;
            border-radius: 2.4mm 0 0 2.4mm;
        }
        .bar-empty {
            background: #D5D5D5;
        }
        .bar-zero {
            background: #D5D5D5;
            text-align: left !important;
            font-size: 7pt;
            font-weight: bold;
            line-height: 4.8mm;
            color: #000000;
            padding-left: 1.5mm;
        }
    </style>
</head>
<body>
<div class="page">

    <img class="bg-img" src="{{ $templatePath }}">

    @php
        use App\Services\NguHanhRadarChartService;

        $chartItems = array_values($bieuDoNguHanh ?? []);
        $chartPng   = NguHanhRadarChartService::toDataUri($chartItems);
        $nienMenhYear = $nienVanYear ?? date('Y');

        // Vị trí icon + nhãn quanh radar (khớp NguHanhRadarChartService 900×600, R=185)
        $chartGw = 900;
        $chartGh = 600;
        $gcx     = 450;
        $gcy     = 265;
        $gR      = 185;

        $axisCount = max(3, count($chartItems));
        $gAxes     = [];
        for ($i = 0; $i < $axisCount; $i++) {
            $gAxes[] = -M_PI_2 + $i * 2 * M_PI / $axisCount;
        }

        $cImgW    = 138.0;
        $cImgTop  = 36.0;
        $cImgLeft = (210.0 - $cImgW) / 2.0;
        $cImgH    = $cImgW * ($chartGh / $chartGw);

        $pCX = $cImgLeft + $cImgW * ($gcx / $chartGw);
        $pCY = $cImgTop  + $cImgH * ($gcy / $chartGh);
        $pRX = $cImgW * ($gR / $chartGw);
        $pRY = $cImgH * ($gR / $chartGh);
        $offMm = 12.0;

        $iconMap = [
            'Thê Tài'  => 'the-tai',  'Thể Tài'  => 'the-tai',  'Thê tài'  => 'the-tai',
            'Quan Quỷ' => 'quan-quy', 'Quan quỷ' => 'quan-quy',
            'Phụ Mẫu'  => 'phu-mau',  'Phụ mẫu'  => 'phu-mau',
            'Huynh Đệ' => 'huynh-de', 'Huynh đệ' => 'huynh-de',
            'Tử Tôn'   => 'tu-ton',   'Tử tôn'   => 'tu-ton',
        ];

        $iconDir = $iconDir ?? resource_path('views/pdfs/quyen-2/chat-luong-ngu-hanh');

        $iconPositions = [];
        foreach ($chartItems as $idx => $ax) {
            $a   = $gAxes[$idx] ?? -M_PI_2;
            $icX = $pCX + ($pRX + $offMm) * cos($a);
            $icY = $pCY + ($pRY + $offMm) * sin($a);
            $iconPositions[] = [
                'left' => round($icX - 13, 2),
                'top'  => round($icY - 8, 2),
                'ax'   => $ax,
            ];
        }
    @endphp

    <div class="chart-img-wrap">
        <img src="{{ $chartPng }}" alt="">
    </div>

    @foreach($iconPositions as $pos)
    @php
        $ax       = $pos['ax'];
        $lucThan  = trim((string) ($ax['luc_than'] ?? ''));
        $nguHanh  = trim((string) ($ax['ten_ngu_hanh'] ?? ''));
        $iconFile = $iconMap[$lucThan] ?? 'the-tai';
        $iconPath = $iconDir . '/' . $iconFile . '.png';
    @endphp
    <div class="icon-wrap" style="left:{{ $pos['left'] }}mm; top:{{ $pos['top'] }}mm;">
        <img src="{{ $iconPath }}" alt="">
        @if($nguHanh !== '')
        <div class="icon-lbl-nh">{{ $nguHanh }}</div>
        @endif
        @if($lucThan !== '')
        <div class="icon-lbl-lt">{{ $lucThan }}</div>
        @endif
    </div>
    @endforeach

    <div class="ts-table-wrap">
        <table class="ts-tbl">
            <colgroup>
                <col class="col-lbl">
                <col class="col-bar">
                <col class="col-bar">
            </colgroup>
            <thead>
                <tr class="ts-hdr">
                    <th align="center"></th>
                    <th align="center">BẢN MỆNH</th>
                    <th align="center">NIÊN MỆNH {{ $nienMenhYear }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($chatLuongThapThan as $item)
                @php
                    $bmPct = min(100, max(0, (int)($item['natal']  ?? 0)));
                    $nvPct = min(100, max(0, (int)($item['annual'] ?? 0)));
                @endphp
                <tr>
                    <td class="ts-lbl-col" align="center" valign="middle">{{ $item['name'] ?? '' }}</td>
                    <td class="bar-cell" valign="middle" align="left">
                        <div class="bar-track">
                            <table class="bar-inner" cellpadding="0" cellspacing="0">
                                <tr>
                                    @if($bmPct > 0)
                                    <td class="bar-fill bar-fill-bm" width="{{ $bmPct }}%" align="right">{{ $bmPct }}%</td>
                                    <td class="bar-empty">&nbsp;</td>
                                    @else
                                    <td class="bar-zero" align="left">0%</td>
                                    @endif
                                </tr>
                            </table>
                        </div>
                    </td>
                    <td class="bar-cell" valign="middle" align="left">
                        <div class="bar-track">
                            <table class="bar-inner" cellpadding="0" cellspacing="0">
                                <tr>
                                    @if($nvPct > 0)
                                    <td class="bar-fill bar-fill-nv" width="{{ $nvPct }}%" align="right">{{ $nvPct }}%</td>
                                    <td class="bar-empty">&nbsp;</td>
                                    @else
                                    <td class="bar-zero" align="left">0%</td>
                                    @endif
                                </tr>
                            </table>
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
