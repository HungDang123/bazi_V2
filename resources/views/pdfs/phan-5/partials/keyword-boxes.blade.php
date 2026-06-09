@if (!empty($keywords))
<div class="kw-section">
    @if (!empty($label))
    <div class="muc-label">{{ $label }}</div>
    @endif
    <table class="kw-grid">
        <tr>
            @foreach ($keywords as $kw)
            <td class="kw-cell">
                <div class="kw-box">
                    <img class="kw-frame" src="{{ $keywordFramePath }}" alt="">
                    <div class="kw-text">
                        <span>{{ $kw }}</span>
                    </div>
                </div>
            </td>
            @endforeach
            @for ($i = count($keywords); $i < 3; $i++)
            <td class="kw-cell kw-cell-empty"></td>
            @endfor
        </tr>
    </table>
</div>
@endif
