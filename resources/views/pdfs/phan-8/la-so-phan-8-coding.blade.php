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

        .tru-heading {
            font-family: 'svn-poppins', sans-serif;
            font-weight: bold;
            font-size: 16px;
            color: #8A1C1C;
            text-align: left;
            line-height: 130%;
            margin-bottom: 4mm;
        }

        .block-label {
            font-size: 12px;
            color: #6B5A33;
            text-align: center;
            margin-bottom: 2mm;
        }

        .golden-title-img {
            display: block;
            margin: 0 auto 3mm auto;
            width: 162mm;
            height: auto;
            object-fit: contain;
        }

        .golden-title {
            font-family: 'utm-davida', serif;
            color: #B8860B;
            font-weight: normal;
            font-size: 25pt;
            text-align: center;
            text-transform: uppercase;
            line-height: 118%;
            letter-spacing: 0.5px;
            margin-bottom: 3mm;
        }

        .golden-subtitle {
            font-family: 'svn-poppins', sans-serif;
            color: #B8860B;
            font-weight: bold;
            font-size: 11pt;
            text-align: center;
            text-transform: uppercase;
            line-height: 130%;
            margin-bottom: 5mm;
        }

        .meta-line {
            font-size: 13px;
            color: #3A3A3A;
            text-align: center;
            margin-bottom: 5mm;
            line-height: 135%;
        }

        .continuation-title {
            font-family: 'utm-davida', serif;
            color: #B8860B;
            font-weight: normal;
            font-size: 17pt;
            text-align: center;
            text-transform: uppercase;
            line-height: 120%;
            margin-bottom: 5mm;
        }

        .preamble-title {
            color: #6E0101;
            font-weight: bold;
            text-transform: uppercase;
            padding-bottom: 3px;
            margin-bottom: 6px;
            font-size: 16px;
            line-height: 130%;
        }

        .preamble-subtitle {
            color: #6E0101;
            font-weight: bold;
            margin-top: 4px;
            margin-bottom: 4px;
            font-size: 16px;
            line-height: 130%;
        }

        .preamble-para {
            color: #1A1A1A;
            line-height: 140%;
            margin-bottom: 4px;
            font-size: 14px;
            text-align: justify;
        }
        .preamble-para p { margin-bottom: 4px; }

        .section-block {
            margin-bottom: 2mm;
            background: transparent;
            overflow: hidden;
        }

        .section-title {
            font-family: 'svn-poppins', sans-serif;
            font-weight: bold;
            text-align: center;
            font-size: 16px;
            line-height: 130%;
            color: #1A1A1A;
            margin-top: 1mm;
            margin-bottom: 3mm;
            background: transparent;
        }

        .section-box {
            background-color: transparent;
            border: 0.3mm solid rgba(190, 190, 190, 0.6);
            border-radius: 2.5mm;
            padding: 4mm 4.5mm;
            margin-bottom: 2mm;
            overflow: hidden;
        }

        .section-box p {
            font-family: 'svn-poppins', sans-serif;
            font-size: 10.5pt;
            font-weight: normal;
            line-height: 15pt;
            color: #1A1A1A;
            text-align: justify;
            display: block;
            margin: 0;
            padding-bottom: 2mm;
            background: transparent;
        }
        .section-box p:last-child { padding-bottom: 0; }

        .section-box .sub-label-line,
        .preamble-para .sub-label-line {
            font-family: 'svn-poppins', sans-serif;
            font-size: 10.5pt;
            font-weight: bold;
            line-height: 15pt;
            color: #6E0101;
            text-align: justify;
            display: block;
            margin: 0;
            padding-bottom: 2mm;
            background: transparent;
        }
    </style>
</head>
<body>

@foreach ($pages ?? [] as $page)
<div class="page">
    <img class="bg-img" src="{{ $page['bgPath'] }}">
    <div class="content-zone" style="left:24mm;width:162mm;top:{{ $page['contentZoneTopMm'] ?? 18 }}mm;height:{{ $page['contentZoneHeightMm'] ?? 240 }}mm;">
        @foreach ($page['items'] ?? [] as $it)
            @php $kind = $it['kind'] ?? ''; @endphp

            @if ($kind === 'preamble')
                @php $block = $it['block'] ?? []; $pType = $block['type'] ?? 'para'; @endphp
                @if ($pType === 'chapter_title')
                <div class="preamble-title">{{ $block['text'] ?? '' }}</div>
                @elseif (in_array($pType, ['sub_title', 'sub_ab'], true))
                <div class="preamble-subtitle">{{ $block['text'] ?? '' }}</div>
                @else
                <div class="preamble-para">
                    @include('pdfs.partials.pdf-text-chunks', [
                        'text' => $block['text'] ?? '',
                        'maxChars' => 72,
                        'bulletPrefix' => false,
                        'phan9SubLabels' => true,
                    ])
                </div>
                @endif

            @elseif ($kind === 'header')
                @php $hd = $it['data'] ?? []; @endphp
                @if (!empty($hd['truHeading']))
                <div class="tru-heading">{{ $hd['truHeading'] }}</div>
                @endif

                @if (!empty($hd['titleImagePath']))
                @php
                    $titleStyle = 'width:162mm;height:auto;object-fit:contain;';
                    if (!empty($hd['titleImageHeightMm'])) {
                        $titleStyle .= 'max-height:'.$hd['titleImageHeightMm'].'mm;';
                    }
                @endphp
                <img class="golden-title-img" src="{{ $hd['titleImagePath'] }}" style="{{ $titleStyle }}">
                @elseif (!empty($hd['title']))
                <div class="golden-title">{{ $hd['title'] }}</div>
                @endif

                @if (!empty($hd['subtitle']))
                <div class="golden-subtitle">{{ $hd['subtitle'] }}</div>
                @endif

            @elseif ($kind === 'cont')
                @php $cd = $it['data'] ?? []; @endphp
                @if (!empty($cd['contTitleImagePath']))
                @php
                    $contStyle = 'width:162mm;height:auto;object-fit:contain;';
                    if (!empty($cd['contTitleImageHeightMm'])) {
                        $contStyle .= 'max-height:'.$cd['contTitleImageHeightMm'].'mm;';
                    }
                @endphp
                <img class="golden-title-img" src="{{ $cd['contTitleImagePath'] }}" style="{{ $contStyle }}">
                @elseif (!empty($cd['continuationTitle']))
                <div class="continuation-title">{{ $cd['continuationTitle'] }}</div>
                @endif

            @elseif ($kind === 'section')
                @php $sec = $it['section'] ?? []; @endphp
                <div class="section-block">
                    <div class="section-title">{{ $sec['label'] ?? '' }}</div>
                    <div class="section-box">
                        @include('pdfs.partials.pdf-text-chunks', [
                            'text' => $sec['content'] ?? '',
                            'maxChars' => 72,
                            'bulletPrefix' => true,
                            'phan9SubLabels' => true,
                        ])
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>
@endforeach

</body>
</html>
