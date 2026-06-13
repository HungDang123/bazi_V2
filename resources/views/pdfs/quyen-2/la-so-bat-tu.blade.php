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
            top: 18mm;
            left: 16mm;
            width: 178mm;
        }

        .bt-table,
        .ts-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            border: 1.2pt solid #6E0101;
        }
        .bt-table th,
        .bt-table td,
        .ts-table th,
        .ts-table td {
            border: 0.5pt solid #FFFFFF;
            text-align: center;
            vertical-align: middle;
            padding: 0.8mm 0.5mm;
        }

        .bt-hdr th,
        .ts-hdr th {
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
            width: 12%;
            line-height: 1.25;
        }
        td.lbl {
            background-color: #EBE7E0 !important;
            color: #333333 !important;
            font-size: 7.5pt;
            font-weight: bold;
            width: 12%;
            line-height: 1.25;
        }

        .tc-row td  { height: 24mm; background-color: #F8F6F2; }
        .dc-row td  { height: 20mm; background-color: #F3F3F3; }
        .tang-row td { height: auto; min-height: 22mm; background-color: #F8F6F2; vertical-align: middle; padding: 1.2mm 0.4mm; }

        .bt-name { font-size: 12pt; font-weight: bold; color: #6E0101; line-height: 1.2; }
        .bt-meta { font-size: 8pt; color: #444444; line-height: 1.2; margin-top: 0.6mm; }
        .bt-tt   { font-size: 8pt; font-weight: bold; color: #6E0101; line-height: 1.2; margin-top: 0.6mm; }
        .kv-note { font-size: 6.5pt; font-style: italic; color: #888888; margin-top: 0.4mm; }

        .tang-stack {
            width: 100%;
            text-align: center;
            font-size: 0;
        }
        .tang-item {
            display: inline-block;
            vertical-align: top;
            padding: 0 0.4mm;
            text-align: center;
        }
        .tang-item.w1 { width: 96%; }
        .tang-item.w2 { width: 48%; }
        .tang-item.w3 { width: 31%; }
        .tang-item .t-can  { display: block; font-size: 7.5pt; font-weight: bold; color: #6E0101; line-height: 1.25; }
        .tang-item .t-meta { display: block; font-size: 6.5pt; color: #555555; line-height: 1.25; margin-top: 0.3mm; }
        .tang-item .t-pho  { display: block; font-size: 6.5pt; font-weight: bold; color: #6E0101; line-height: 1.25; margin-top: 0.3mm; }

        .ts-table { margin-top: 5mm; }
        .ts-row td.ts-val { height: 8mm; background-color: #F8F6F2; font-size: 8pt; font-weight: bold; color: #333333; width: 50%; }
        .ts-row-alt td.ts-val { background-color: #EDE8DF; }
        .ts-row td.lbl { width: 50%; }
    </style>
</head>
<body>
<div class="page">

    <img class="bg-img" src="{{ $templatePath }}">

    <div class="content">

        <table class="bt-table">
            <thead>
                <tr class="bt-hdr">
                    <th class="lbl"></th>
                    <th>Giờ</th>
                    <th>Ngày<br>Nhật Chủ</th>
                    <th>Tháng</th>
                    <th>Năm</th>
                </tr>
            </thead>
            <tbody>

                <tr class="tc-row">
                    <td class="lbl">Thiên<br>Can</td>
                    @foreach(['hour','day','month','year'] as $col)
                    @php
                        $can   = $batTu[$col]['can'] ?? [];
                        $isDay = ($col === 'day');
                        $tt    = $isDay ? '/' : ($can['chu_tinh'] ?? '');
                    @endphp
                    <td>
                        <div class="bt-name">{{ $can['thien_can'] ?? '' }}</div>
                        <div class="bt-meta">{{ $can['am_duong'] ?? '' }} {{ $can['menh'] ?? '' }}</div>
                        <div class="bt-tt">{{ $tt }}</div>
                    </td>
                    @endforeach
                </tr>

                <tr class="dc-row">
                    <td class="lbl">Địa<br>Chi</td>
                    @foreach(['hour','day','month','year'] as $col)
                    @php $chi = $batTu[$col]['chi'] ?? []; @endphp
                    <td>
                        <div class="bt-name">{{ $chi['dia_chi'] ?? '' }}</div>
                        <div class="bt-meta">{{ $chi['am_duong'] ?? '' }} {{ $chi['menh'] ?? '' }}</div>
                        @if(!empty($chi['khong_vong']))<div class="kv-note">(KV)</div>@endif
                        <div class="bt-tt">/</div>
                    </td>
                    @endforeach
                </tr>

                <tr class="tang-row">
                    <td class="lbl">Tàng<br>Can</td>
                    @foreach(['hour','day','month','year'] as $col)
                    @php
                        $tangs = $batTu[$col]['can_tang'] ?? [];
                        $cnt   = max(1, count($tangs));
                        $wCls  = $cnt >= 3 ? 'w3' : ($cnt === 2 ? 'w2' : 'w1');
                    @endphp
                    <td>
                        <div class="tang-stack">
                            @foreach($tangs as $tc)
                            <div class="tang-item {{ $wCls }}">
                                <span class="t-can">{{ $tc['can_tang'] ?? '' }}</span>
                                <span class="t-meta">{{ ($tc['am_duong'] ?? '') }} {{ preg_replace('/^[+\-]\s*/u', '', (string) ($tc['menh'] ?? '')) }}</span>
                                <span class="t-pho">{{ $tc['pho_tinh'] ?? '' }}</span>
                            </div>
                            @endforeach
                        </div>
                    </td>
                    @endforeach
                </tr>

            </tbody>
        </table>

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
                <tr class="ts-hdr">
                    <th style="width:50%;">Ngày sinh / Nhật Chủ</th>
                    <th style="width:50%;">{{ $nhatChu }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($thanSat as $ten => $giaTri)
                <tr class="{{ $rowIdx % 2 === 0 ? 'ts-row' : 'ts-row ts-row-alt' }}">
                    <td class="lbl">{{ $ten }}</td>
                    <td class="ts-val">{{ $giaTri }}</td>
                </tr>
                @php $rowIdx++; @endphp
                @endforeach
            </tbody>
        </table>

    </div>
</div>
</body>
</html>
