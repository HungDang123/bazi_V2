@php
    $highlightPillars = $highlightPillars ?? [];
    $pillarCols = [
        ['key' => 'hour', 'hl' => in_array('hour', $highlightPillars, true)],
        ['key' => 'day', 'hl' => in_array('day', $highlightPillars, true)],
        ['key' => 'month', 'hl' => in_array('month', $highlightPillars, true)],
        ['key' => 'year', 'hl' => in_array('year', $highlightPillars, true)],
    ];
@endphp

<div class="table-title">KẾT QUẢ LÁ SỐ TỨ TRỤ</div>

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
        <tr>
            <td class="lbl-col">Thiên Can</td>
            @foreach($pillarCols as $pillar)
            @php
                $col   = $pillar['key'];
                $hl    = $pillar['hl'];
                $can   = $batTu[$col]['can'] ?? [];
                $isDay = ($col === 'day');
                $tt    = $isDay ? '/' : ($can['chu_tinh'] ?? '');
            @endphp
            <td class="data-cell{{ $hl ? ' hl' : '' }}">
                <div class="name-lg">{{ $can['thien_can'] ?? '' }}</div>
                <div class="meta-sm">{{ $can['am_duong'] ?? '' }} {{ $can['menh'] ?? '' }}</div>
                <div class="thap-than">{{ $tt }}</div>
            </td>
            @endforeach
        </tr>

        <tr>
            <td class="lbl-col">Địa Chi</td>
            @foreach($pillarCols as $pillar)
            @php
                $col = $pillar['key'];
                $hl  = $pillar['hl'];
                $chi = $batTu[$col]['chi'] ?? [];
                $kv  = !empty($chi['khong_vong']);
            @endphp
            <td class="data-cell{{ $hl ? ' hl' : '' }}">
                <div class="name-lg">{{ $chi['dia_chi'] ?? '' }}</div>
                <div class="meta-sm">{{ $chi['am_duong'] ?? '' }} {{ $chi['menh'] ?? '' }}</div>
                @if($kv)<div class="kv-txt">(Không vong)</div>@endif
                <div class="thap-than">/</div>
            </td>
            @endforeach
        </tr>

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
                        <td><div class="tc-meta">{{ ($tc['am_duong'] ?? '') }} {{ $tc['menh'] ?? '' }}</div></td>
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
