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

        /* ── Bảng Đại Vận ───────────────────────────────────── */
        .dv-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5pt solid #6E0101;
        }
        .dv-table th,
        .dv-table td {
            border: 0.5pt solid #FFFFFF;
            text-align: center;
            vertical-align: middle;
            padding: 0.5mm;
        }

        /* Header hàng ĐẠI VẬN */
        .dv-hdr th {
            background-color: #6E0101;
            color: #ffffff;
            font-size: 8.5pt;
            font-weight: bold;
            height: 9mm;
            border: 0.5pt solid #6E0101;
        }

        /* Header hàng (th) – đỏ */
        th.lbl {
            background-color: #6E0101 !important;
            color: #ffffff !important;
            font-size: 8pt;
            font-weight: bold;
            width: 9%;
        }
        /* Nhãn hàng dữ liệu (td) – xám */
        td.lbl {
            background-color: #F3F3F3 !important;
            color: #333333 !important;
            font-size: 8pt;
            font-weight: bold;
            width: 9%;
        }

        /* Thiên Can row */
        .tc-row td  { height: 26mm; background-color: #F3F3F3; }
        .dv-name    { font-size: 10pt;  font-weight: bold; color: #6E0101; line-height: 1.15; }
        .dv-meta    { font-size: 7pt;   color: #555555; line-height: 1.15; }
        .dv-tt      { font-size: 7pt;   font-weight: bold; color: #6E0101; line-height: 1.15; }

        /* Địa Chi row */
        .dc-row td  { height: 15mm; background-color: #F3F3F3; }

        /* Tàng Cang row */
        .tang-row td { height: 13mm; background-color: #F3F3F3; }

        /* Sub-table Tàng Can (dùng chung) */
        .tc-sub { width: 100%; border-collapse: collapse; }
        .tc-sub td { border: none !important; padding: 0.2mm; text-align: center; font-size: 6pt; line-height: 1.15; vertical-align: middle; }
        .t-can  { color: #6E0101; font-weight: bold; }
        .t-meta { color: #555555; }
        .t-pho  { color: #6E0101; font-weight: bold; }

        /* Year rows (phần 2 — 10 năm) */
        .yr-row td {
            height: 6.2mm;
            font-size: 6.5pt;
            line-height: 1.15;
            color: #333;
            vertical-align: middle;
            padding: 0.2mm 0.3mm;
            background-color: #F3F3F3;
        }
        .yr-row-alt td { background-color: #EBEBEB; }
        .kv-note { font-size: 5.5pt; font-style: italic; color: #888; }

    </style>
</head>
<body>
<div class="page">

    {{-- Nền trang trí LBTV-532 --}}
    <img class="bg-img" src="{{ $templatePath }}">

    <div class="content">

        {{-- ══════════════════════════════════════════════
             BẢNG ĐẠI VẬN  (9 kỳ × 10 năm)
             ══════════════════════════════════════════════ --}}
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

                {{-- ─── Thiên Can ─── --}}
                <tr class="tc-row">
                    <td class="lbl">Thiên<br>Can</td>
                    @foreach($bangDaiVan as $dv)
                    <td>
                        <div class="dv-name">{{ $dv['can']['thien_can'] }}</div>
                        <div class="dv-meta">{{ $dv['can']['am_duong'] }} {{ $dv['can']['menh'] }}</div>
                        <div class="dv-tt">{{ $dv['can']['chu_tinh'] }}</div>
                    </td>
                    @endforeach
                </tr>

                {{-- ─── Địa Chi ─── --}}
                <tr class="dc-row">
                    <td class="lbl">Địa<br>Chi</td>
                    @foreach($bangDaiVan as $dv)
                    <td>
                        <div class="dv-name">{{ $dv['chi']['dia_chi'] }}</div>
                        <div class="dv-meta">{{ $dv['chi']['am_duong'] }} {{ $dv['chi']['menh'] }}</div>
                        @if(!empty($dv['chi']['khong_vong']))<div class="kv-note">(KV)</div>@endif
                    </td>
                    @endforeach
                </tr>

                {{-- ─── Tàng Cang ─── --}}
                <tr class="tang-row">
                    <td class="lbl">Tàng<br>Cang</td>
                    @foreach($bangDaiVan as $dv)
                    <td>
                        <table class="tc-sub">
                            <tr>
                                @foreach($dv['cantang'] as $tc)
                                <td><span class="t-can">{{ $tc['can_tang'] }}</span></td>
                                @endforeach
                            </tr>
                            <tr>
                                @foreach($dv['cantang'] as $tc)
                                <td><span class="t-meta">{{ $tc['menh'] }}</span></td>
                                @endforeach
                            </tr>
                            <tr>
                                @foreach($dv['cantang'] as $tc)
                                <td><span class="t-pho">{{ $tc['pho_tinh'] }}</span></td>
                                @endforeach
                            </tr>
                        </table>
                    </td>
                    @endforeach
                </tr>

                {{-- ─── 10 năm trong mỗi kỳ Đại Vận ─── --}}
                @for($j = 0; $j < 10; $j++)
                <tr class="{{ $j % 2 === 0 ? 'yr-row' : 'yr-row yr-row-alt' }}">
                    <td class="lbl"></td>
                    @foreach($bangDaiVan as $dv)
                    @php $yr = $dv['list_year'][$j] ?? null; @endphp
                    <td>
                        @if($yr)
                        {{ $yr['can_chi'] }}<br>{{ $yr['nam'] }}
                        @if(!empty($yr['khong_vong']))<div class="kv-note">(KV)</div>@endif
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endfor

            </tbody>
        </table>


    </div>{{-- .content --}}
</div>{{-- .page --}}
</body>
</html>
