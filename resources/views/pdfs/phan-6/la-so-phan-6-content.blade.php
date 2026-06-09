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
            color: #6E0101;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1px solid #C9A227;
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

        .red-title:first-child {
            margin-top: 0;
        }

        .para-text {
            color: #1A1A1A;
            line-height: 140%;
            margin-bottom: 3px;
            font-size: 14px;
            text-align: justify;
        }

        .para-text p {
            margin-bottom: 5px;
        }

        .coding-box {
            color: #1A1A1A;
            background-color: #FFF8E7;
            border: 0.4mm solid #E8C872;
            padding: 2.5mm 3mm;
            margin: 2mm 0 4mm;
            line-height: 140%;
            font-size: 14px;
        }

        .huong-label {
            font-weight: bold;
            margin-top: 4px;
            margin-bottom: 3px;
            font-size: 14px;
            line-height: 130%;
        }

        .huong-label.positive { color: #2E7D32; }
        .huong-label.negative { color: #C62828; }

        .content-img {
            display: block;
            width: 154mm;
            max-width: 154mm;
            height: auto;
            margin: 3mm 0 4mm;
        }

        .lsbt-wrap { margin: 3mm 0 5mm; }
        .lsbt-title {
            text-align: center;
            color: #6E0101;
            font-weight: bold;
            font-size: 14px;
            line-height: 130%;
            margin-bottom: 2mm;
        }
        .lsbt-table {
            width: 100%;
            border-collapse: collapse;
            border: 1pt solid #6E0101;
            font-size: 8pt;
        }
        .lsbt-table th,
        .lsbt-table td {
            border: 0.6pt solid #9C3030;
            padding: 1.5mm 1mm;
            vertical-align: top;
            text-align: left;
        }
        .lsbt-h {
            background-color: #6E0101;
            color: #FFFFFF;
            font-weight: bold;
            text-align: center;
        }
        .lsbt-loai {
            font-weight: bold;
            background-color: #F2F2F2;
            width: 18%;
        }
        .lsbt-cell {
            color: #1A1A1A;
            line-height: 125%;
            white-space: pre-line;
        }
    </style>
</head>
<body>

@foreach ($pages as $page)
<div class="page">

    <img class="bg-img" src="{{ $page['bgPath'] }}">

    <div class="content-zone" style="left:{{ $page['contentLeftMm'] ?? 24 }}mm;width:{{ $page['contentWidthMm'] ?? 162 }}mm;top:{{ $page['contentZoneTopMm'] ?? 18 }}mm;height:{{ $page['contentZoneHeightMm'] ?? 187.1 }}mm;">
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
            @elseif ($type === 'coding_box')
            <div class="coding-box">
                @foreach (preg_split('/\r\n|\r|\n/', $block['text'] ?? '') ?: [] as $line)
                    @if (trim($line) !== '')
                    <p>{{ trim($line) }}</p>
                    @endif
                @endforeach
            </div>
            @elseif ($type === 'table')
            @include('pdfs.phan-6.partials.la-so-bat-tu-table', ['table' => $block['table'] ?? []])
            @elseif ($type === 'image')
            <img class="content-img"
                 src="{{ $block['path'] }}"
                 @if (!empty($block['maxHeightMm']))
                 style="max-height: {{ $block['maxHeightMm'] }}mm;"
                 @endif
            >
            @else
            <div class="para-text">
                @foreach (preg_split('/\r\n|\r|\n/', $block['text'] ?? '') ?: [] as $line)
                    @if (trim($line) !== '')
                    <p>{{ trim($line) }}</p>
                    @endif
                @endforeach
            </div>
            @endif
        @endforeach

    </div>
</div>
@endforeach

</body>
</html>
