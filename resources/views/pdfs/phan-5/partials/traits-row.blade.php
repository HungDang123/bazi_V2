@php
    use App\Services\Pdf\Phan5TraitLayout;
    use App\Services\NguHanhTitleRenderer;

    $tichPill = NguHanhTitleRenderer::pillImagePath('Tích cực', [0x6F, 0xC2, 0x45], [0x2E, 0x7D, 0x32]);
    $tieuPill = NguHanhTitleRenderer::pillImagePath('Tiêu cực', [0xC6, 0x28, 0x28], [0x7B, 0x10, 0x10]);
    $pillSize = $tichPill !== '' ? NguHanhTitleRenderer::pillDisplaySizeMm($tichPill) : ['widthMm' => 30.0, 'heightMm' => 11.0];
    $boxHeightMm = Phan5TraitLayout::boxHeightMm($tichCuc ?? '', $tieuCuc ?? '', $pillSize['heightMm']);

    $formatTraitLine = static function (string $line): string {
        $line = trim($line);
        if ($line === '') {
            return '';
        }
        if (preg_match('/^[-–•]\s*/u', $line)) {
            return preg_replace('/^[-–•]\s*/u', '– ', $line);
        }

        return '– '.$line;
    };

    $renderTraitBody = static function (?string $text) use ($formatTraitLine): string {
        $html = '';
        foreach (preg_split('/\r\n|\r|\n/', $text ?? '') ?: [] as $line) {
            $formatted = $formatTraitLine($line);
            if ($formatted !== '') {
                $html .= '<p class="pdf-justify">'.e($formatted).'</p>';
            }
        }

        return $html;
    };
@endphp
<table class="traits-row">
    <tr style="height: {{ $boxHeightMm }}mm;">
        <td class="traits-col traits-col-left" style="height: {{ $boxHeightMm }}mm;">
            <div class="trait-box tich-cuc" style="height: {{ $boxHeightMm }}mm; max-height: {{ $boxHeightMm }}mm;">
                <table class="trait-inner">
                    <tr>
                        <td class="trait-pill-wrap">
                            @if ($tichPill !== '')
                            <img class="trait-pill-img" style="width: {{ $pillSize['widthMm'] }}mm; height: {{ $pillSize['heightMm'] }}mm;" src="{{ $tichPill }}" alt="Tích cực">
                            @else
                            <span class="trait-pill">Tích cực</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="trait-body-cell">{!! $renderTraitBody($tichCuc ?? '') !!}</td>
                    </tr>
                </table>
            </div>
        </td>
        <td class="traits-col traits-col-right" style="height: {{ $boxHeightMm }}mm;">
            <div class="trait-box tieu-cuc" style="height: {{ $boxHeightMm }}mm; max-height: {{ $boxHeightMm }}mm;">
                <table class="trait-inner">
                    <tr>
                        <td class="trait-pill-wrap">
                            @if ($tieuPill !== '')
                            <img class="trait-pill-img" style="width: {{ $pillSize['widthMm'] }}mm; height: {{ $pillSize['heightMm'] }}mm;" src="{{ $tieuPill }}" alt="Tiêu cực">
                            @else
                            <span class="trait-pill">Tiêu cực</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="trait-body-cell">{!! $renderTraitBody($tieuCuc ?? '') !!}</td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>
</table>
