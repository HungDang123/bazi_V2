@php
    $table = $table ?? [];
    $cols = is_array($table['columns'] ?? null) ? $table['columns'] : [];
    $rows = is_array($table['rows'] ?? null) ? $table['rows'] : [];
@endphp

@if ($rows !== [])
<div class="lsbt-wrap">
    <div class="lsbt-title">{{ $table['title'] ?? 'Lá số Bát Tự' }}</div>
    <table class="lsbt-table">
        <thead>
            <tr class="lsbt-hdr">
                <th class="lsbt-corner"></th>
                @foreach ($cols as $col)
                <th class="lsbt-col">{{ $col['label'] ?? $col['key'] ?? '' }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
            @php
                $loaiKey = $row['loai_key'] ?? '';
                $rowCls  = $loaiKey === 'dia_chi' ? 'lsbt-row-dc' : 'lsbt-row-tc';
            @endphp
            <tr class="{{ $rowCls }}">
                <td class="lsbt-lbl">{{ $row['loai'] ?? '' }}</td>
                @foreach ($cols as $col)
                @php $key = $col['key'] ?? ''; @endphp
                <td class="lsbt-cell">{{ $row['cells'][$key] ?? '' }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
