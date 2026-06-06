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
            top: 40mm;
            height: 222mm;
            overflow: hidden;
        }

        .tru-heading {
            font-family: 'svn-poppins', sans-serif;
            font-weight: bold;
            font-size: 13px;
            color: #8A1C1C;
            text-align: left;
            line-height: 135%;
            margin-bottom: 4mm;
        }

        .block-label {
            font-size: 10px;
            color: #6B5A33;
            text-align: center;
            margin-bottom: 2mm;
        }

        .golden-title-img {
            display: block;
            margin: 0 auto 3mm auto;
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
            font-size: 11px;
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

        .section-title {
            font-family: 'svn-poppins', sans-serif;
            font-weight: bold;
            text-align: center;
            font-size: 13px;
            color: #1A1A1A;
            margin-top: 1mm;
            margin-bottom: 2.5mm;
        }

        .section-box {
            background-color: rgba(255, 255, 255, 0.55);
            border: 0.3mm solid rgba(190, 190, 190, 0.6);
            border-radius: 2.5mm;
            padding: 4mm 4.5mm;
            margin-bottom: 4mm;
        }

        .section-box p {
            font-family: 'svn-poppins', sans-serif;
            font-size: 12px;
            font-weight: normal;
            line-height: 150%;
            color: #1A1A1A;
            text-align: justify;
            margin-bottom: 2mm;
        }
        .section-box p:last-child { margin-bottom: 0; }
    </style>
</head>
<body>

@foreach ($pages ?? [] as $page)
<div class="page">
    <img class="bg-img" src="{{ $page['bgPath'] }}">
    <div class="content-wrap">
        @if (!empty($page['showHeader']))
            @if (!empty($page['titleImagePath']))
            <img class="golden-title-img" src="{{ $page['titleImagePath'] }}" style="width: 162mm; height: auto;">
            @elseif (!empty($page['title']))
            <div class="golden-title">{{ $page['title'] }}</div>
            @endif

            @if (!empty($page['subtitle']))
            <div class="golden-subtitle">{{ $page['subtitle'] }}</div>
            @endif
        @elseif (!empty($page['contTitleImagePath']))
            <img class="golden-title-img" src="{{ $page['contTitleImagePath'] }}" style="width: 162mm; height: auto;">
        @elseif (!empty($page['continuationTitle']))
            <div class="continuation-title">{{ $page['continuationTitle'] }}</div>
        @endif

        @foreach ($page['sections'] ?? [] as $sec)
        <div class="section-title">{{ $sec['label'] ?? '' }}</div>
        <div class="section-box">
            @foreach (preg_split('/\r\n|\r|\n/', $sec['content'] ?? '') ?: [] as $line)
                @php $line = trim($line); @endphp
                @if ($line !== '')
                <p>{{ preg_match('/^-\s*/u', $line) ? $line : '– '.$line }}</p>
                @endif
            @endforeach
        </div>
        @endforeach
    </div>
</div>
@endforeach

</body>
</html>
