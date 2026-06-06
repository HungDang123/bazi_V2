@if (!empty($keywords))
<div class="section-block kw-section">
    @if (!empty($label))
    <div class="muc-label">{{ $label }}</div>
    @endif
    <div class="kw-grid">
        <table>
            <tr>
                @foreach ($keywords as $kw)
                <td>
                    <div class="kw-box">
                        <img class="kw-frame" src="{{ $keywordFramePath }}" alt="">
                        <div class="kw-text">
                            <table>
                                <tr>
                                    <td class="kw-text-cell"><span>{{ $kw }}</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </td>
                @endforeach
                @for ($i = count($keywords); $i < 3; $i++)
                <td></td>
                @endfor
            </tr>
        </table>
    </div>
</div>
@endif
