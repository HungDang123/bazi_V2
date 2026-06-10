@php use App\Services\NguHanhTitleRenderer; @endphp
@if (!empty($keywords))
<div class="kw-section">
    @if (!empty($label))
    <div class="muc-label">{{ $label }}</div>
    @endif
    <table class="kw-grid">
        <tr>
            @foreach ($keywords as $kw)
            @php $kwImg = NguHanhTitleRenderer::keywordImagePath((string) $kw); @endphp
            <td class="kw-cell">
                <div class="kw-box">
                    <img class="kw-frame" src="{{ $keywordFramePath }}" alt="">
                    <div class="kw-text">
                        @if ($kwImg !== '')
                        <img class="kw-text-img" src="{{ $kwImg }}" alt="{{ $kw }}">
                        @else
                        <span>{{ mb_strtoupper(trim((string) $kw)) }}</span>
                        @endif
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
