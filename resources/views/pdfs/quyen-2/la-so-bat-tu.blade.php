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

        /* Nền trang trí (khung + scroll header) */
        .bg-img {
            position: absolute;
            top: 0; left: 0;
            width: 210mm; height: 297mm;
        }

        /* Vùng nội dung bảng */
        .content {
            position: absolute;
            top: 16mm;
            left: 17mm;
            width: 176mm;
        }

        /* ── Bảng Tứ Trụ chính ─────────────────────────────────── */
        .bt-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5pt solid #6E0101;
        }
        .bt-table th,
        .bt-table td {
            border: 1pt solid #9C3030;
            text-align: center;
            vertical-align: middle;
        }

        /* Header row đỏ */
        .hdr th {
            background-color: #6E0101;
            color: #FFFFFF;
            font-size: 9.5pt;
            font-weight: bold;
            padding: 3mm 1mm;
            height: 12mm;
        }

        /* Cột nhãn (Thiên Can / Địa Chi / Tàng can) */
        .lbl-col {
            width: 14%;
            font-size: 8.5pt;
            font-weight: bold;
            color: #333;
            text-align: center !important;
            padding: 2mm !important;
            background-color: #F2F2F2;
        }

        /* Cell dữ liệu Thiên Can / Địa Chi */
        .data-cell {
            width: 21.5%;
            height: 43mm;
            padding: 2mm 1mm;
            background-color: #FAFAFA;
            vertical-align: middle;
        }

        /* Tên can/chi lớn */
        .name-lg {
            font-size: 24pt;
            font-weight: bold;
            color: #6E0101;
            margin-bottom: 2mm;
        }
        /* Âm dương + ngũ hành */
        .meta-sm {
            font-size: 12pt;
            color: #555555;
            margin-bottom: 1mm;
        }
        /* Thập thần */
        .thap-than {
            font-size: 12pt;
            font-weight: bold;
            color: #6E0101;
        }
        /* Không vong */
        .kv-txt {
            font-size: 7pt;
            font-style: italic;
            color: #777;
            margin-bottom: 0.5mm;
        }

        /* Cell Tàng Can */
        .tc-cell {
            width: 21.5%;
            height: 38mm;
            padding: 2mm 1mm;
            background-color: #FAFAFA;
            vertical-align: middle;
        }

        /* Sub-table Tàng Can */
        .tc-sub {
            width: 100%;
            border-collapse: collapse;
        }
        .tc-sub td {
            border: none !important;
            padding: 0.8mm 0.5mm;
            text-align: center;
            vertical-align: top;
        }
        .tc-can  { font-size: 7pt; font-weight: bold; color: #6E0101; }
        .tc-meta { font-size: 7pt; color: #555555; }
        .tc-pho  { font-size: 7pt; font-weight: bold; color: #6E0101; }

        /* ── Bảng Thần Sát ─────────────────────────────────────── */
        .ts-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5pt solid #6E0101;
            margin-top: 5mm;
        }
        .ts-table th,
        .ts-table td {
            border: 1pt solid #9C3030;
            padding: 2.5mm 2mm;
            text-align: center;
            font-size: 9pt;
        }
        .ts-hdr {
            background-color: #6E0101;
            color: #FFFFFF;
            font-weight: bold;
            height: 10mm;
            font-size: 9pt;
        }
        .ts-lbl {
            font-weight: bold;
            color: #333;
            background-color: #F5F5F5;
            width: 50%;
        }
        .ts-val {
            color: #333;
            font-weight: bold;
            width: 50%;
        }
        .ts-even {
            background-color: #FAFAFA;
        }
        .ts-odd {
            background-color: #FFFFFF;
        }
    </style>
</head>
<body>
<div class="page">

    {{-- Nền trang trí LBTV-531 --}}
    <img class="bg-img" src="{{ $templatePath }}">

    <div class="content">

        {{-- ════════════════════════════════════════════════
             BẢNG TỨ TRỤ
             ════════════════════════════════════════════════ --}}
        <table class="bt-table">
            <thead>
                <tr class="hdr">
                    <th style="width:14%; background-color:#6E0101;"></th>
                    <th style="width:21.5%;">Giờ</th>
                    <th style="width:21.5%;">Ngày<br>Nhật Chủ</th>
                    <th style="width:21.5%;">Tháng</th>
                    <th style="width:21.5%;">Năm</th>
                </tr>
            </thead>
            <tbody>

                {{-- ─── THIÊN CAN ─── --}}
                <tr>
                    <td class="lbl-col">Thiên Can</td>

                    @foreach(['hour','day','month','year'] as $col)
                    @php
                        $can   = $batTu[$col]['can'] ?? [];
                        $isDay = ($col === 'day');
                        $tt    = $isDay ? '/' : ($can['chu_tinh'] ?? '');
                    @endphp
                    <td class="data-cell">
                        <div class="name-lg">{{ $can['thien_can'] ?? '' }}</div>
                        <div class="meta-sm">{{ $can['am_duong'] ?? '' }} {{ $can['menh'] ?? '' }}</div>
                        <div class="thap-than">{{ $tt }}</div>
                    </td>
                    @endforeach
                </tr>

                {{-- ─── ĐỊA CHI ─── --}}
                <tr>
                    <td class="lbl-col">Địa Chi</td>

                    @foreach(['hour','day','month','year'] as $col)
                    @php
                        $chi = $batTu[$col]['chi'] ?? [];
                        $kv  = !empty($chi['khong_vong']);
                    @endphp
                    <td class="data-cell">
                        <div class="name-lg">{{ $chi['dia_chi'] ?? '' }}</div>
                        <div class="meta-sm">{{ $chi['am_duong'] ?? '' }} {{ $chi['menh'] ?? '' }}</div>
                        @if($kv)<div class="kv-txt">(Không vong)</div>@endif
                        <div class="thap-than">/</div>
                    </td>
                    @endforeach
                </tr>

                {{-- ─── TÀNG CAN ─── --}}
                <tr>
                    <td class="lbl-col">Tàng can</td>

                    @foreach(['hour','day','month','year'] as $col)
                    @php $tangs = $batTu[$col]['can_tang'] ?? []; @endphp
                    <td class="tc-cell">
                        <table class="tc-sub">
                            <tr>
                                @foreach($tangs as $tc)
                                <td><div class="tc-can">{{ $tc['can_tang'] ?? '' }}</div></td>
                                @endforeach
                            </tr>
                            <tr>
                                @foreach($tangs as $tc)
                                <td><div class="tc-meta">{{ ($tc['am_duong'] ?? '') }}{{ $tc['menh'] ?? '' }}</div></td>
                                @endforeach
                            </tr>
                            <tr>
                                @foreach($tangs as $tc)
                                <td><div class="tc-pho">{{ $tc['pho_tinh'] ?? '' }}</div></td>
                                @endforeach
                            </tr>
                        </table>
                    </td>
                    @endforeach
                </tr>

            </tbody>
        </table>

        {{-- ════════════════════════════════════════════════
             BẢNG THẦN SÁT
             ════════════════════════════════════════════════ --}}
        @php
            $nhatChu = trim(($batTu['day']['can']['thien_can'] ?? '') . ' ' . ($batTu['day']['chi']['dia_chi'] ?? ''));
            $thanSat = [
                'Quý Nhân'  => $quyNhanVanXuong['quy_nhan']  ?? '',
                'Văn Xương' => $quyNhanVanXuong['van_xuong'] ?? '',
                'Đào Hoa'   => $quyNhanVanXuong['dao_hoa']   ?? '',
                'Dịch Mã'   => $quyNhanVanXuong['dich_ma']   ?? '',
                'Cô Thần'   => $quyNhanVanXuong['co_than']   ?? '',
            ];
            $rowIdx = 0;
        @endphp

        <table class="ts-table">
            <thead>
                <tr>
                    <th class="ts-hdr" style="width:50%;">Ngày sinh / Nhật Chủ</th>
                    <th class="ts-hdr" style="width:50%;">{{ $nhatChu }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($thanSat as $ten => $giaTri)
                @php $cls = ($rowIdx % 2 === 0) ? 'ts-odd' : 'ts-even'; $rowIdx++; @endphp
                <tr class="{{ $cls }}">
                    <td class="ts-lbl">{{ $ten }}</td>
                    <td class="ts-val">{{ $giaTri }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

    </div>{{-- .content --}}
</div>{{-- .page --}}
</body>
</html>
