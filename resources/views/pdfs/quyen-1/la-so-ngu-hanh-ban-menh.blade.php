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

        .content-wrap {
            position: absolute;
            left: 28mm;
            width: 154mm;
            top: 16mm;
            height: 240mm;
            overflow: hidden;
        }

        /* UTM Davida 24px – PNG supersampling, giữ tỉ lệ (không ép height gây vỡ nét) */
        .hanh-title-wrap {
            width: 154mm;
            text-align: center;
            margin-bottom: 3mm;
        }

        .hanh-title-img {
            display: block;
            width: 154mm;
            height: auto;
            margin: 0 auto;
        }

        .hanh-title-fallback {
            font-family: 'svn-poppins', sans-serif;
            font-size: 14px;
            font-weight: bold;
            line-height: 130%;
            letter-spacing: 0;
            text-align: center;
            color: #B90000;
            margin-bottom: 5mm;
        }

        .hanh-img {
            display: block;
            width: 154mm;
            height: 88mm;
            margin-bottom: 4mm;
        }

        .item-title {
            font-family: 'svn-poppins', sans-serif;
            font-size: 14px;
            font-weight: bold;
            line-height: 130%;
            letter-spacing: 0;
            color: #000000;
            margin-top: 7px;
            margin-bottom: 3px;
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
    </style>
</head>
<body>

@foreach ($pages as $page)
<div class="page">

    <img class="bg-img" src="{{ $page['bgPath'] }}">

    <div class="content-wrap">

        @if (!empty($page['showTitle']) && !empty($page['titleImagePath']))
        <div class="hanh-title-wrap">
            <img class="hanh-title-img" src="{{ $page['titleImagePath'] }}">
        </div>
        @elseif (!empty($page['showTitle']))
        <div class="hanh-title-fallback">HÀNH {{ $page['hanhName'] }} {{ $page['percent'] }}%</div>
        @endif

        @if (!empty($page['showImage']) && !empty($page['imagePath']))
        <img class="hanh-img" src="{{ $page['imagePath'] }}">
        @endif

        @foreach ($page['blocks'] as $block)
            @if ($block['type'] === 'item_title')
            <div class="item-title">{{ $block['text'] }}</div>
            @else
            <div class="para-text">{{ $block['text'] }}</div>
            @endif
        @endforeach

    </div>

</div>
@endforeach

</body>
</html>
