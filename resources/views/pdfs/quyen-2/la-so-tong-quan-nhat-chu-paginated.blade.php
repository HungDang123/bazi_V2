<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; padding: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'svn-poppins', sans-serif;
            font-size: 14px;
            font-weight: normal;
            line-height: 140%;
            text-align: justify;
            letter-spacing: 0;
        }

        .page {
            position: relative;
            width: 210mm;
            height: 297mm;
            overflow: hidden;
            page-break-after: always;
        }

        .page:last-child { page-break-after: auto; }

        .bg-img {
            position: absolute;
            top: 0; left: 0;
            width: 210mm; height: 297mm;
            z-index: 0;
        }

        .scroll-cover {
            position: absolute;
            left: 14mm;
            width: 182mm;
            background: #6E0101;
            z-index: 2;
        }

        .scroll-sub {
            position: absolute;
            left: 14mm;
            width: 182mm;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            line-height: 130%;
            color: #E8C97A;
            z-index: 3;
        }

        .scroll-main {
            position: absolute;
            left: 14mm;
            width: 182mm;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            line-height: 130%;
            color: #D4AF37;
            z-index: 3;
        }

        @include('pdfs.partials.content-zone-styles')

        .chapter-title {
            font-size: 16px;
            font-weight: bold;
            line-height: 130%;
            color: #6E0101;
            text-transform: uppercase;
            border-bottom: 1px solid #C9A227;
            padding-bottom: 3px;
            margin-bottom: 8px;
        }

        .sub-title {
            font-size: 16px;
            font-weight: bold;
            line-height: 130%;
            color: #6E0101;
            margin-top: 8px;
            margin-bottom: 4px;
        }

        .block-sub-title:first-child .sub-title { margin-top: 0; }

        .para-text {
            font-size: 14px;
            line-height: 140%;
            text-align: justify;
            color: #1A1A1A;
        }

        .para-text p {
            margin: 0;
            padding-bottom: 0.8mm;
            font-size: 14px;
            line-height: 140%;
            text-align: justify;
        }

        .para-text p:last-child { padding-bottom: 0; }
    </style>
</head>
<body>

@php
    $titleLines = array_filter(array_map('trim', preg_split('/[\r\n]+/', $nhatChuTitle ?? '')));
    $mainTitle  = mb_strtoupper(array_pop($titleLines) ?? '', 'UTF-8');
    $subTitle   = !empty($titleLines) ? mb_strtoupper(implode(' – ', $titleLines), 'UTF-8') : '';
@endphp

@foreach ($pages as $pageIdx => $page)
<div class="page">

    <img class="bg-img" src="{{ $page['bgPath'] }}">

    @if ($pageIdx === 0)
    <div class="scroll-cover" style="top: 24mm; height: 30mm;"></div>
    @if ($subTitle)
    <div class="scroll-sub" style="top: 27mm;">{{ $subTitle }}</div>
    @endif
    @if ($mainTitle)
    <div class="scroll-main" style="top: {{ $subTitle ? '33mm' : '35mm' }};">{{ $mainTitle }}</div>
    @endif
    @endif

    <div class="content-zone" style="top: {{ $page['contentZoneTopMm'] ?? 66 }}mm; height: {{ $page['contentZoneHeightMm'] ?? 192 }}mm; left: {{ $page['contentLeftMm'] ?? 28 }}mm; width: {{ $page['contentWidthMm'] ?? 154 }}mm;">

        @foreach ($page['blocks'] as $block)
            @if (($block['type'] ?? 'para') === 'chapter_title')
            <div class="chapter-title">{{ $block['text'] ?? '' }}</div>
            @elseif (($block['type'] ?? 'para') === 'sub_title')
            <div class="block-sub-title">
                <div class="sub-title">{{ $block['text'] ?? '' }}</div>
            </div>
            @else
            <div class="para-text">
                @include('pdfs.partials.pdf-text-chunks', [
                    'text' => $block['text'] ?? '',
                    'maxChars' => 75,
                    'bulletPrefix' => false,
                ])
            </div>
            @endif
        @endforeach

    </div>
</div>
@endforeach

</body>
</html>
