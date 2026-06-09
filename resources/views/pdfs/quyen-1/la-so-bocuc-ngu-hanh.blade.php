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

        .page:last-child {
            page-break-after: auto;
        }

        .bg-img {
            position: absolute;
            top: 0; left: 0;
            width: 210mm; height: 297mm;
        }

        @include('pdfs.partials.content-zone-styles')

        .chapter-title {
            font-family: 'svn-poppins', sans-serif;
            font-size: 16px;
            font-weight: bold;
            line-height: 130%;
            letter-spacing: 0;
            color: #6E0101;
            text-transform: uppercase;
            border-bottom: 1px solid #C9A227;
            padding-bottom: 3px;
            margin-bottom: 8px;
        }

        .sub-title {
            font-family: 'svn-poppins', sans-serif;
            font-size: 16px;
            font-weight: bold;
            line-height: 130%;
            letter-spacing: 0;
            color: #6E0101;
            margin-top: 8px;
            margin-bottom: 4px;
        }

        .block-sub-title:first-child .sub-title {
            margin-top: 0;
        }

        .para-text {
            font-family: 'svn-poppins', sans-serif;
            font-size: 14px;
            font-weight: normal;
            line-height: 140%;
            letter-spacing: 0;
            text-align: justify;
            color: #1A1A1A;
            margin-bottom: 3px;
        }

        .para-text.emphasis {
            color: #6E0101;
        }

        .para-text p {
            margin-bottom: 5px;
        }

        .content-img {
            display: block;
            width: 154mm;
            max-width: 154mm;
            height: auto;
            margin: 4px 0 6px;
        }
    </style>
</head>
<body>

@foreach ($pages as $page)
<div class="page">

    <img class="bg-img" src="{{ $page['bgPath'] }}">

    <div class="content-zone" style="top: {{ $page['contentZoneTopMm'] ?? 44.5 }}mm; height: {{ $page['contentZoneHeightMm'] ?? 208 }}mm; left: {{ $page['contentLeftMm'] ?? 28 }}mm; width: {{ $page['contentWidthMm'] ?? 154 }}mm;">
        <div class="content-inner" style="padding-top: {{ $page['paddingTopMm'] ?? 0 }}mm;">

        @if (!empty($page['chapterTitle']))
        <div class="chapter-title">{{ $page['chapterTitle'] }}</div>
        @endif

        @foreach ($page['blocks'] as $block)
            @if (($block['type'] ?? 'para') === 'sub_title')
            <div class="block-sub-title">
                <div class="sub-title">{{ $block['text'] ?? '' }}</div>
            </div>
            @elseif (($block['type'] ?? 'para') === 'image')
            <img class="content-img"
                 src="{{ $block['path'] }}"
                 @if (!empty($block['maxHeightMm']))
                 style="max-height: {{ $block['maxHeightMm'] }}mm;"
                 @endif
            >
            @else
            <div class="para-text{{ !empty($block['emphasis']) ? ' emphasis' : '' }}">
                <p>{{ $block['text'] ?? '' }}</p>
            </div>
            @endif
        @endforeach

        </div>
    </div>

</div>
@endforeach

</body>
</html>
