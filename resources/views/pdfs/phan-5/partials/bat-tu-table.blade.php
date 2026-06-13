@php
    $highlightPillars = $highlightPillars ?? [];
    $pillarCols = [
        ['key' => 'hour',  'hl' => in_array('hour', $highlightPillars, true)],
        ['key' => 'day',   'hl' => in_array('day', $highlightPillars, true)],
        ['key' => 'month', 'hl' => in_array('month', $highlightPillars, true)],
        ['key' => 'year',  'hl' => in_array('year', $highlightPillars, true)],
    ];
@endphp

<div class="table-title">KẾT QUẢ LÁ SỐ TỨ TRỤ</div>

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
            @foreach($pillarCols as $pillar)
            @php
                $col   = $pillar['key'];
                $hl    = $pillar['hl'];
                $can   = $batTu[$col]['can'] ?? [];
                $isDay = ($col === 'day');
                $tt    = $isDay ? '/' : ($can['chu_tinh'] ?? '');
            @endphp
            <td class="{{ $hl ? 'hl' : '' }}">
                <div class="bt-name">{{ $can['thien_can'] ?? '' }}</div>
                <div class="bt-meta">{{ $can['am_duong'] ?? '' }} {{ $can['menh'] ?? '' }}</div>
                <div class="bt-tt">{{ $tt }}</div>
            </td>
            @endforeach
        </tr>

        <tr class="dc-row">
            <td class="lbl">Địa<br>Chi</td>
            @foreach($pillarCols as $pillar)
            @php
                $col = $pillar['key'];
                $hl  = $pillar['hl'];
                $chi = $batTu[$col]['chi'] ?? [];
            @endphp
            <td class="{{ $hl ? 'hl' : '' }}">
                <div class="bt-name">{{ $chi['dia_chi'] ?? '' }}</div>
                <div class="bt-meta">{{ $chi['am_duong'] ?? '' }} {{ $chi['menh'] ?? '' }}</div>
                @if(!empty($chi['khong_vong']))<div class="kv-note">(KV)</div>@endif
                <div class="bt-tt">/</div>
            </td>
            @endforeach
        </tr>

        <tr class="tang-row">
            <td class="lbl">Tàng<br>Can</td>
            @foreach($pillarCols as $pillar)
            @php
                $col   = $pillar['key'];
                $hl    = $pillar['hl'];
                $tangs = $batTu[$col]['can_tang'] ?? [];
                $cnt   = max(1, count($tangs));
                $wCls  = $cnt >= 3 ? 'w3' : ($cnt === 2 ? 'w2' : 'w1');
            @endphp
            <td class="{{ $hl ? 'hl' : '' }}">
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
