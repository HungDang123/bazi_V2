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

        /* Radar — tỉ lệ 1.5 giống Chart.js desktop */
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

        /* Bảng THẬP THẦN */
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
        }
        .ts-tbl col.col-lbl { width: 46mm; }
        .ts-tbl col.col-bar { width: 68mm; }
        .ts-tbl th,
        .ts-tbl td {
            border: none;
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
            text-align: center;
            font-size: 7.5pt;
            line-height: 8mm;
            height: 8mm;
            padding: 0 1.5mm;
            white-space: nowrap;
        }
        .bar-cell {
            height: 8mm;
            padding: 1.6mm 2mm 0;
            vertical-align: middle;
            text-align: center;
        }
        .ts-odd  .bar-cell { background: #F2EFE8; }
        .ts-even .bar-cell { background: #E9E5DE; }
        .bar-track {
            width: 100%;
            background: #CECECE;
            border-radius: 2.4mm;
            height: 4.8mm;
            overflow: hidden;
            margin: 0;
            display: flex;
            align-items: center;
        }
        .bar-fill-bm,
        .bar-fill-nv {
            border-radius: 2.4mm;
            height: 4.8mm;
            min-width: 8mm;
            font-size: 7pt;
            color: #ffffff;
            font-weight: bold;
            padding: 0.4mm 0 0;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }
        .bar-fill-bm {
            background: #4169E1;
        }
        .bar-fill-nv {
            background: #8B4513;
        }
        .bar-zero {
            height: 4.8mm;
            font-size: 7pt;
            color: #666666;
            line-height: 1;
            text-align: left;
            padding: 0.4mm 0 0 1.5mm;
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body>
<div class="page">

    <img class="bg-img" src="{{ $templatePath }}">

    @php
        use App\Services\NguHanhRadarChartService;

        $chartPng = NguHanhRadarChartService::toDataUri($bieuDoNguHanh ?? []);
        $nienMenhYear = $nienVanYear ?? date('Y');
        $rowIdx = 0;
    @endphp

    <div class="chart-img-wrap">
        <img src="{{ $chartPng }}" alt="">
    </div>

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
                    $cls   = ($rowIdx % 2 === 0) ? 'ts-odd' : 'ts-even';
                    $bmPct = min(100, max(0, (int)($item['natal']  ?? 0)));
                    $nvPct = min(100, max(0, (int)($item['annual'] ?? 0)));
                    $rowIdx++;
                @endphp
                <tr class="{{ $cls }}">
                    <td class="ts-lbl-col" align="center" valign="middle">{{ $item['name'] ?? '' }}</td>
                    <td class="bar-cell" align="center" valign="middle">
                        <div class="bar-track">
                            @if($bmPct > 0)
                            <div class="bar-fill-bm" style="width:{{ $bmPct }}%;">{{ $bmPct }}%</div>
                            @else
                            <div class="bar-zero">0%</div>
                            @endif
                        </div>
                    </td>
                    <td class="bar-cell" align="center" valign="middle">
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
