<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; padding: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @include('pdfs.partials.pdf-justify-styles')
        body {
            width: 210mm;
            height: 297mm;
            font-family: 'svn-poppins', sans-serif;
            font-size: 14px;
            font-weight: normal;
            line-height: 140%;
            text-align: justify;
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
            top: 16mm;
            left: 17mm;
            width: 176mm;
        }

        /* ── Bảng Niên Vận ───────────────────────────────────── */
        .nv-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5pt solid #6E0101;
        }
        .nv-table th,
        .nv-table td {
            border: 0.5pt solid #FFFFFF;
            text-align: center;
            vertical-align: middle;
            padding: 1mm;
        }
        .nv-hdr th {
            background-color: #6E0101;
            color: #ffffff;
            font-size: 8.5pt;
            font-weight: bold;
            height: 9mm;
            border: 0.5pt solid #6E0101;
        }
        th.lbl {
            background-color: #6E0101 !important;
            color: #ffffff !important;
            font-size: 7.5pt;
            font-weight: bold;
            width: 9%;
        }
        td.lbl {
            background-color: #F3F3F3 !important;
            color: #333333 !important;
            font-size: 7.5pt;
            font-weight: bold;
            width: 9%;
        }
        .nv-tc-row td  { height: 22mm; background-color: #F3F3F3; }
        .nv-dc-row td  { height: 16mm; background-color: #F3F3F3; }
        .nv-tang-row td{ height: 10mm; background-color: #F3F3F3; padding: 0.5mm; }
        .nv-tang-row .tc-sub td { font-size: 5pt; padding: 0; line-height: 1.0; }
        .nv-name { font-size: 10pt; font-weight: bold; color: #6E0101; }
        .nv-meta { font-size: 7.5pt; color: #555555; }
        .nv-tt   { font-size: 7.5pt; font-weight: bold; color: #6E0101; }
        .kv-sm   { font-size: 6pt; font-style: italic; color: #888; }

        /* Sub-table Tàng Can */
        .tc-sub { width: 100%; border-collapse: collapse; }
        .tc-sub td { border: none !important; padding: 0.1mm; text-align: center; font-size: 5.5pt; line-height: 1.1; }
        .t-can  { color: #6E0101; font-weight: bold; }
        .t-meta { color: #555555; }
        .t-pho  { color: #6E0101; font-weight: bold; }
    </style>
</head>
<body>
<div class="page">

    {{-- Nền trang trí --}}
    <img class="bg-img" src="{{ $templatePath }}">

    <div class="content">

        {{-- ══════════════════════════════════════════════
             BẢNG NIÊN VẬN  (3 năm: trước / hiện / sau)
             ══════════════════════════════════════════════ --}}
        <table class="nv-table">
            <thead>
                <tr class="nv-hdr">
                    <th class="lbl" style="width:9%;">NIÊN<br>VẬN</th>
                    @foreach($nienVan as $nv)
                    <th style="width:30.33%;">{{ $nv['nam'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>

                {{-- Thiên Can --}}
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

                {{-- Địa Chi --}}
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

                {{-- Tàng Can --}}
                <tr class="nv-tang-row">
                    <td class="lbl">Tàng<br>Can</td>
                    @foreach($nienVan as $nv)
                    <td>
                        <table class="tc-sub">
                            <tr>
                                @foreach($nv['cantang'] as $tc)
                                <td><span class="t-can">{{ $tc['can_tang'] }}</span></td>
                                @endforeach
                            </tr>
                            <tr>
                                @foreach($nv['cantang'] as $tc)
                                <td><span class="t-meta">{{ $tc['menh'] }}</span></td>
                                @endforeach
                            </tr>
                            <tr>
                                @foreach($nv['cantang'] as $tc)
                                <td><span class="t-pho">{{ $tc['pho_tinh'] }}</span></td>
                                @endforeach
                            </tr>
                        </table>
                    </td>
                    @endforeach
                </tr>

            </tbody>
        </table>

    </div>{{-- .content --}}
</div>{{-- .page --}}
</body>
</html>
