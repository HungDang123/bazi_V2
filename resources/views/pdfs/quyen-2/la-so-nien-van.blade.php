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
            top: 92mm;
            left: 16mm;
            width: 178mm;
        }

        .nv-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            border: 1.2pt solid #6E0101;
        }
        .nv-table th,
        .nv-table td {
            border: 0.5pt solid #FFFFFF;
            text-align: center;
            vertical-align: middle;
            padding: 1.5mm 1mm;
        }
        .nv-hdr th {
            background-color: #6E0101;
            color: #FFFFFF;
            font-size: 9pt;
            font-weight: bold;
            height: 11mm;
            border: 0.5pt solid #6E0101;
        }
        th.lbl {
            background-color: #6E0101 !important;
            color: #E5CA8E !important;
            font-size: 8pt;
            font-weight: bold;
            width: 12%;
            line-height: 1.25;
        }
        td.lbl {
            background-color: #EBE7E0 !important;
            color: #333333 !important;
            font-size: 8pt;
            font-weight: bold;
            width: 12%;
            line-height: 1.25;
        }

        .nv-tc-row td  { height: 26mm; background-color: #F8F6F2; }
        .nv-dc-row td  { height: 18mm; background-color: #F3F3F3; }
        .nv-tang-row td { height: auto; min-height: 22mm; background-color: #F8F6F2; padding: 1.5mm 0.8mm; }

        .nv-name { font-size: 13pt; font-weight: bold; color: #6E0101; line-height: 1.2; }
        .nv-meta { font-size: 8.5pt; color: #444444; line-height: 1.2; margin-top: 0.8mm; }
        .nv-tt   { font-size: 8.5pt; font-weight: bold; color: #6E0101; line-height: 1.2; margin-top: 0.8mm; }
        .kv-sm   { font-size: 7pt; font-style: italic; color: #888888; margin-top: 0.5mm; }

        .tang-stack {
            width: 100%;
            text-align: center;
            font-size: 0;
        }
        .tang-item {
            display: inline-block;
            vertical-align: top;
            width: 31%;
            padding: 0 0.5mm;
            text-align: center;
        }
        .tang-item .t-can  { display: block; font-size: 8pt; font-weight: bold; color: #6E0101; line-height: 1.3; }
        .tang-item .t-meta { display: block; font-size: 7pt; color: #555555; line-height: 1.3; margin-top: 0.4mm; }
        .tang-item .t-pho  { display: block; font-size: 7pt; font-weight: bold; color: #6E0101; line-height: 1.3; margin-top: 0.4mm; }
    </style>
</head>
<body>
<div class="page">

    <img class="bg-img" src="{{ $templatePath }}">

    <div class="content">
        <table class="nv-table">
            <thead>
                <tr class="nv-hdr">
                    <th class="lbl">NIÊN<br>VẬN</th>
                    @foreach($nienVan as $nv)
                    <th>{{ $nv['nam'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>

                <tr class="nv-tc-row">
                    <td class="lbl">Thiên<br>Can</td>
                    @foreach($nienVan as $nv)
                    <td>
                        <div class="nv-name">{{ $nv['can']['thien_can'] }}</div>
                        <div class="nv-meta">{{ $nv['can']['am_duong'] }} {{ $nv['can']['menh'] }}</div>
                        <div class="nv-tt">{{ $nv['can']['chu_tinh'] }}</div>
                    </td>
                    @endforeach
                </tr>

                <tr class="nv-dc-row">
                    <td class="lbl">Địa<br>Chi</td>
                    @foreach($nienVan as $nv)
                    <td>
                        <div class="nv-name">{{ $nv['chi']['dia_chi'] }}</div>
                        <div class="nv-meta">{{ $nv['chi']['am_duong'] }} {{ $nv['chi']['menh'] }}</div>
                        @if(!empty($nv['chi']['khong_vong']))<div class="kv-sm">(Không vong)</div>@endif
                    </td>
                    @endforeach
                </tr>

                <tr class="nv-tang-row">
                    <td class="lbl">Tàng<br>Can</td>
                    @foreach($nienVan as $nv)
                    <td>
                        <div class="tang-stack">
                            @foreach($nv['cantang'] as $tc)
                            <div class="tang-item">
                                <span class="t-can">{{ $tc['can_tang'] }}</span>
                                <span class="t-meta">{{ $tc['menh'] }}</span>
                                <span class="t-pho">{{ $tc['pho_tinh'] }}</span>
                            </div>
                            @endforeach
                        </div>
                    </td>
                    @endforeach
                </tr>

            </tbody>
        </table>
    </div>
</div>
</body>
</html>
