<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @include('pdfs.partials.pdf-base-typography')
        @page { margin: 0; padding: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }

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
        }

        @include('pdfs.partials.content-zone-styles')

        .chapter-title {
            color: #6E0101;
            font-weight: bold;
            text-transform: uppercase;
            padding-bottom: 3px;
            margin-bottom: 8px;
            font-size: 16px;
            line-height: 130%;
        }

        .red-title {
            color: #6E0101;
            font-weight: bold;
            margin-top: 8px;
            margin-bottom: 4px;
            font-size: 16px;
            line-height: 130%;
        }

        .para-text {
            color: #1A1A1A;
            margin-bottom: 2mm;
            font-size: 14px;
            text-align: justify;
            background: transparent;
        }
        .para-text p {
            font-size: 14px;
            line-height: 15pt;
            display: block;
            margin: 0;
            padding-bottom: 2mm;
            color: #1A1A1A;
            background: transparent;
            text-align: justify;
        }
        .para-text p:last-child { padding-bottom: 0; }

        .para-text .sub-label-line {
            color: #6E0101;
            font-weight: bold;
            font-size: 14px;
            line-height: 15pt;
        }

        .huong-label { font-weight: bold; margin-top: 4px; margin-bottom: 3px; font-size: 14px; line-height: 130%; }
        .huong-label.positive { color: #2E7D32; }
        .huong-label.negative { color: #C62828; }
    </style>
</head>
<body>

@foreach ($pages as $page)
<div class="page">
    <img class="bg-img" src="{{ $page['bgPath'] }}">
    <div class="content-zone" style="left:{{ $page['contentLeftMm'] ?? 24 }}mm;width:{{ $page['contentWidthMm'] ?? 162 }}mm;top:{{ $page['contentZoneTopMm'] ?? 18 }}mm;height:{{ $page['contentZoneHeightMm'] ?? 240 }}mm;">
@if (!empty($page['chapterTitle']))
        <div class="chapter-title">{{ $page['chapterTitle'] }}</div>
        @endif

        @foreach ($page['blocks'] as $block)
            @php $type = $block['type'] ?? 'para'; @endphp
            @if ($type === 'chapter_title')
            <div class="chapter-title">{{ $block['text'] ?? '' }}</div>
            @elseif (in_array($type, ['sub_title', 'sub_ab'], true))
            <div class="red-title">{{ $block['text'] ?? '' }}</div>
            @elseif ($type === 'huong_label')
            <div class="huong-label {{ $block['tone'] ?? '' }}">{{ $block['text'] ?? '' }}</div>
            @else
            <div class="para-text">
                @include('pdfs.partials.pdf-text-chunks', [
                    'text' => $block['text'] ?? '',
                    'maxChars' => 72,
                    'bulletPrefix' => false,
                    'phan9SubLabels' => true,
                ])
            </div>
            @endif
        @endforeach
    </div>
</div>
@endforeach

</body>
</html>
