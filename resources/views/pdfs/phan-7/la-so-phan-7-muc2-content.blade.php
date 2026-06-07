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

        .content-wrap {
            position: absolute;
            left: 24mm;
            width: 162mm;
            top: 18mm;
            height: 242mm;
            overflow: hidden;
        }

        /* ── Thập Thần header block ── */
        .thap-than-header {
            text-align: center;
            margin-bottom: 4mm;
        }

        .thap-than-title {
            color: #6E0101;
            font-weight: bold;
            font-size: 15px;
            text-transform: uppercase;
            letter-spacing: 0.3mm;
            margin-bottom: 2px;
        }

        .thap-than-subtitle {
            color: #6E0101;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.2mm;
            margin-bottom: 3mm;
        }

        .gold-separator {
            height: 0;
            border: none;
            border-top: 0.5mm solid #C9A227;
            margin: 0 0 4mm 0;
        }

        /* ── Section labels (a. b. c.) ── */
        .section-label {
            color: #6E0101;
            font-style: italic;
            font-weight: bold;
            font-size: 12px;
            margin-top: 3mm;
            margin-bottom: 1.5mm;
        }

        /* ── Paragraph text ── */
        .para-text {
            color: #1A1A1A;
            font-size: 12px;
            line-height: 140%;
            margin-bottom: 2mm;
        }

        .para-text p {
            margin-bottom: 1.5px;
        }

        /* ── Thập Thần illustration image ── */
        .content-img {
            display: block;
            width: 154mm;
            max-width: 154mm;
            height: auto;
            margin: 2mm 0 3mm;
        }
    </style>
</head>
<body>

@foreach ($pages as $page)
<div class="page">

    <img class="bg-img" src="{{ $page['bgPath'] }}">

    <div class="content-wrap">

        @foreach ($page['blocks'] as $block)
            @php $type = $block['type'] ?? 'para'; @endphp

            @if ($type === 'thap_than_title')
            <div class="thap-than-header">
                <div class="thap-than-title">{{ $block['title'] ?? '' }}</div>
                @if (!empty($block['subtitle']))
                <div class="thap-than-subtitle">{{ $block['subtitle'] }}</div>
                @endif
                <div class="gold-separator"></div>
            </div>

            @elseif ($type === 'section_label')
            <div class="section-label">{{ $block['text'] ?? '' }}</div>

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
