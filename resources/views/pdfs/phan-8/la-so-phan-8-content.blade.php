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

        .content-wrap {
            position: absolute;
            left: 24mm;
            width: 162mm;
            top: 18mm;
            height: 258mm;
            overflow: hidden;
        }

        .chapter-title {
            color: #6E0101;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1px solid #C9A227;
            padding-bottom: 2px;
            margin-bottom: 6px;
            font-size: 12px;
        }

        .red-title {
            color: #6E0101;
            font-weight: bold;
            margin-top: 5px;
            margin-bottom: 3px;
            font-size: 12px;
        }

        .para-text {
            color: #1A1A1A;
            line-height: 100%;
            margin-bottom: 3px;
            font-size: 12px;
        }
        .para-text p { margin-bottom: 2px; }

        .huong-label { font-weight: bold; margin-top: 3px; margin-bottom: 2px; font-size: 12px; }
        .huong-label.positive { color: #2E7D32; }
        .huong-label.negative { color: #C62828; }
    </style>
</head>
<body>

@foreach ($pages as $page)
<div class="page">
    <img class="bg-img" src="{{ $page['bgPath'] }}">
    <div class="content-wrap">
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
