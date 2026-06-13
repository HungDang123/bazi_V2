<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; padding: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            width: 210mm;
            height: 297mm;
            font-family: 'svn-poppins', sans-serif;
            font-size: 14px;
            font-weight: normal;
            line-height: 140%;
            letter-spacing: 0;
        }
        .page {
            position: relative;
            width: 210mm;
            height: 297mm;
            overflow: hidden;
        }
        .bg-img {
            position: absolute;
            top: 0; left: 0;
            width: 210mm; height: 297mm;
        }
        .content {
            position: absolute;
            top: 44mm;
            left: 16mm;
            width: 178mm;
        }

        .dv-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            border: 1.2pt solid #6E0101;
        }
        .dv-table th,
        .dv-table td {
            border: 0.5pt solid #FFFFFF;
            text-align: center;
            vertical-align: middle;
            padding: 0.8mm 0.5mm;
        }

        .dv-hdr th {
            background-color: #6E0101;
            color: #FFFFFF;
            font-size: 8.5pt;
            font-weight: bold;
            height: 10mm;
            line-height: 1.2;
            border: 0.5pt solid #6E0101;
        }

        th.lbl {
            background-color: #6E0101 !important;
            color: #E5CA8E !important;
            font-size: 7.5pt;
            font-weight: bold;
            width: 10%;
            line-height: 1.25;
        }
        td.lbl {
            background-color: #EBE7E0 !important;
            color: #333333 !important;
            font-size: 7.5pt;
            font-weight: bold;
            width: 10%;
            line-height: 1.25;
        }

        .tc-row td  { height: 21mm; background-color: #F8F6F2; }
        .dc-row td  { height: 14mm; background-color: #F3F3F3; }
        .tang-row td { height: auto; min-height: 17mm; background-color: #F8F6F2; vertical-align: middle; padding: 1.2mm 0.4mm; }

        .dv-name { font-size: 10.5pt; font-weight: bold; color: #6E0101; line-height: 1.2; }
        .dv-meta { font-size: 7.5pt; color: #444444; line-height: 1.2; margin-top: 0.6mm; }
        .dv-tt   { font-size: 7.5pt; font-weight: bold; color: #6E0101; line-height: 1.2; margin-top: 0.6mm; }
        .kv-note { font-size: 6pt; font-style: italic; color: #888888; margin-top: 0.4mm; }

        .tang-stack {
            width: 100%;
            text-align: center;
            font-size: 0;
        }
        .tang-item {
            display: inline-block;
            vertical-align: top;
            width: 31%;
            padding: 0 0.3mm;
            text-align: center;
        }
        .tang-item .t-can  { display: block; font-size: 7pt; font-weight: bold; color: #6E0101; line-height: 1.25; }
        .tang-item .t-meta { display: block; font-size: 6.5pt; color: #555555; line-height: 1.25; margin-top: 0.3mm; }
        .tang-item .t-pho  { display: block; font-size: 6.5pt; font-weight: bold; color: #6E0101; line-height: 1.25; margin-top: 0.3mm; }

        .yr-row td {
            height: 8.5mm;
            background-color: #F8F6F2;
            vertical-align: middle;
            padding: 0.4mm 0.3mm;
        }
        .yr-row-alt td { background-color: #EDE8DF; }
        .yr-can { font-size: 7pt; font-weight: bold; color: #6E0101; line-height: 1.15; }
        .yr-num { font-size: 6.5pt; color: #333333; line-height: 1.15; margin-top: 0.2mm; }
    </style>
</head>
<body>
<div class="page">

    <img class="bg-img" src="{{ $templatePath }}">

    <div class="content">
        <table class="dv-table">
            <thead>
                <tr class="dv-hdr">
                    <th class="lbl">ĐẠI<br>VẬN</th>
                    @foreach($bangDaiVan as $dv)
                    <th>{{ $dv['age'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>

                <tr class="tc-row">
                    <td class="lbl">Thiên<br>Can</td>
                    @foreach($bangDaiVan as $dv)
                    <td>
                        <div class="dv-name">{{ $dv['can']['thien_can'] }}</div>
                        <div class="dv-meta">{{ $dv['can']['menh'] }}</div>
                        <div class="dv-tt">{{ $dv['can']['chu_tinh'] }}</div>
                    </td>
                    @endforeach
                </tr>

                <tr class="dc-row">
                    <td class="lbl">Địa<br>Chi</td>
                    @foreach($bangDaiVan as $dv)
                    <td>
                        <div class="dv-name">{{ $dv['chi']['dia_chi'] }}</div>
                        <div class="dv-meta">{{ $dv['chi']['menh'] }}</div>
                        @if(!empty($dv['chi']['khong_vong']))<div class="kv-note">(KV)</div>@endif
                    </td>
                    @endforeach
                </tr>

                <tr class="tang-row">
                    <td class="lbl">Tàng<br>Can</td>
                    @foreach($bangDaiVan as $dv)
                    <td>
                        <div class="tang-stack">
                            @foreach($dv['cantang'] as $tc)
                            <div class="tang-item">
                                <span class="t-can">{{ $tc['can_tang'] }}</span>
                                <span class="t-meta">{{ preg_replace('/^[+\-]\s*/u', '', $tc['menh']) }}</span>
                                <span class="t-pho">{{ $tc['pho_tinh'] }}</span>
                            </div>
                            @endforeach
                        </div>
                    </td>
                    @endforeach
                </tr>

                @for($j = 0; $j < 10; $j++)
                <tr class="{{ $j % 2 === 0 ? 'yr-row' : 'yr-row yr-row-alt' }}">
                    <td class="lbl"></td>
                    @foreach($bangDaiVan as $dv)
                    @php $yr = $dv['list_year'][$j] ?? null; @endphp
                    <td>
                        @if($yr)
                        <div class="yr-can">{{ $yr['can_chi'] }}</div>
                        <div class="yr-num">{{ $yr['nam'] }}</div>
                        @if(!empty($yr['khong_vong']))<div class="kv-note">(KV)</div>@endif
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endfor

            </tbody>
        </table>
    </div>
</div>
</body>
</html>
