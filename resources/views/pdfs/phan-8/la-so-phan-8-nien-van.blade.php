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
            margin-bottom: 5mm;
            font-size: 16px;
            line-height: 130%;
        }

        .red-title {
            color: #6E0101;
            font-weight: bold;
            margin-top: 6px;
            margin-bottom: 4px;
            font-size: 16px;
            line-height: 130%;
        }

        .para-text {
            color: #1A1A1A;
            line-height: 140%;
            margin-bottom: 5px;
            font-size: 14px;
            text-align: justify;
        }
        .para-text p { margin-bottom: 3mm; }
    </style>
</head>
<body>

@foreach ($pages as $page)
<div class="page">
    <img class="bg-img" src="{{ $page['bgPath'] }}">
    <div class="content-zone" style="left:{{ $page['contentLeftMm'] ?? 24 }}mm;width:{{ $page['contentWidthMm'] ?? 162 }}mm;top:{{ $page['contentZoneTopMm'] ?? 18 }}mm;height:{{ $page['contentZoneHeightMm'] ?? 252.45 }}mm;">
@if (!empty($page['chapterTitle']))
        <div class="chapter-title">{{ $page['chapterTitle'] }}</div>
        @endif

        @foreach ($page['blocks'] as $block)
            @php $type = $block['type'] ?? 'para'; @endphp
            @if ($type === 'chapter_title')
            <div class="chapter-title">{{ $block['text'] ?? '' }}</div>
            @elseif (in_array($type, ['sub_title', 'sub_ab'], true))
            <div class="red-title">{{ $block['text'] ?? '' }}</div>
            @else
            <div class="para-text">
                @foreach (preg_split('/\r\n|\r|\n/', $block['text'] ?? '') ?: [] as $line)
                    @if (trim($line) !== '')<p>{{ trim($line) }}</p>@endif
                @endforeach
            </div>
            @endif
        @endforeach
    </div>
</div>
@endforeach

</body>
</html>
