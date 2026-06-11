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

        .golden-title-img {
            display: block;
            margin: 0 auto 3mm auto;
            width: 162mm;
            height: auto;
            object-fit: contain;
        }

        .golden-subtitle-img {
            display: block;
            margin: 0 auto 5mm auto;
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
    </style>
</head>
<body>

@foreach ($pages ?? [] as $page)
<div class="page">
    <img class="bg-img" src="{{ $page['bgPath'] }}">
    <div class="content-zone" style="left:24mm;width:162mm;top:{{ $page['contentZoneTopMm'] ?? 18 }}mm;height:{{ $page['contentZoneHeightMm'] ?? 252.45 }}mm;">
        @foreach ($page['items'] ?? [] as $it)
            @php $kind = $it['kind'] ?? ''; @endphp

            @if ($kind === 'header')
                @php $hd = $it['data'] ?? []; @endphp

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

                @if (!empty($hd['subtitleImagePath']))
                @php
                    $subStyle = 'width:162mm;height:auto;object-fit:contain;';
                    if (!empty($hd['subtitleImageHeightMm'])) {
                        $subStyle .= 'max-height:'.$hd['subtitleImageHeightMm'].'mm;';
                    }
                @endphp
                <img class="golden-subtitle-img" src="{{ $hd['subtitleImagePath'] }}" style="{{ $subStyle }}">
                @elseif (!empty($hd['subtitle']))
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
