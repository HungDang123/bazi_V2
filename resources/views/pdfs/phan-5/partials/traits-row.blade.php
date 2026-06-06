@php
    $countTraitLines = static function (?string $text): int {
        if ($text === null || trim($text) === '') {
            return 0;
        }
        return count(array_filter(
            array_map('trim', preg_split('/\r\n|\r|\n/', $text) ?: []),
            static fn (string $line): bool => $line !== ''
        ));
    };
    $tichLineCount = $countTraitLines($tichCuc ?? '');
    $tieuLineCount = $countTraitLines($tieuCuc ?? '');
    $maxTraitLines = max($tichLineCount, $tieuLineCount, 1);
    $traitBodyMinHeightMm = round($maxTraitLines * 5.5 + 2, 2);
    $traitPillRowMm = 12;
@endphp
<table class="traits-row">
    <tr>
        <td>
            <table class="trait-box tich-cuc" style="min-height: {{ $traitBodyMinHeightMm + $traitPillRowMm }}mm;">
                <tr>
                    <td class="trait-pill-cell"><span class="trait-pill">Tích cực</span></td>
                </tr>
                <tr>
                    <td class="trait-body-cell">
                        @foreach (preg_split('/\r\n|\r|\n/', $tichCuc ?? '') ?: [] as $line)
                            @if (trim($line) !== '')<p>{{ trim($line) }}</p>@endif
                        @endforeach
                    </td>
                </tr>
            </table>
        </td>
        <td>
            <table class="trait-box tieu-cuc" style="min-height: {{ $traitBodyMinHeightMm + $traitPillRowMm }}mm;">
                <tr>
                    <td class="trait-pill-cell"><span class="trait-pill">Tiêu cực</span></td>
                </tr>
                <tr>
                    <td class="trait-body-cell">
                        @foreach (preg_split('/\r\n|\r|\n/', $tieuCuc ?? '') ?: [] as $line)
                            @if (trim($line) !== '')<p>{{ trim($line) }}</p>@endif
                        @endforeach
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
