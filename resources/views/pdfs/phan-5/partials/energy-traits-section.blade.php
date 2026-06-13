@php
    $giaiNghia = trim((string) ($giaiNghia ?? ''));
    $tichCuc   = trim((string) ($tichCuc ?? ''));
    $tieuCuc   = trim((string) ($tieuCuc ?? ''));
@endphp

<div class="energy-traits-wrap">
@if($giaiNghia !== '')
<div class="muc-label">Giải nghĩa năng lượng</div>
<div class="para-text energy-giai-nghia">
    @foreach(preg_split('/\r\n|\r|\n/', $giaiNghia) ?: [] as $line)
        @php $line = trim($line); @endphp
        @if($line !== '')
        <p class="pdf-justify">{{ $line }}</p>
        @endif
    @endforeach
</div>
@endif

@if($tichCuc !== '' || $tieuCuc !== '')
@include('pdfs.phan-5.partials.traits-row', [
    'tichCuc' => $tichCuc,
    'tieuCuc' => $tieuCuc,
])
@endif
</div>
