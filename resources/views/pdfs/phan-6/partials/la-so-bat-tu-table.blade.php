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
            <tr>
                <th class="lsbt-h">{{ $table['title'] ?? 'Lá số Bát Tự' }}</th>
                @foreach ($cols as $col)
                <th class="lsbt-h">{{ $col['label'] ?? $col['key'] ?? '' }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
            <tr>
                <td class="lsbt-loai">{{ $row['loai'] ?? '' }}</td>
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
