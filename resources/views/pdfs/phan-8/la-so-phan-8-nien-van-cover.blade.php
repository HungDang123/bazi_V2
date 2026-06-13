<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @include('pdfs.partials.pdf-fonts')
        @page { margin: 0; padding: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }

        .page {
            position: relative;
            width: 210mm;
            height: 297mm;
            overflow: hidden;
        }

        .bg-img {
            position: absolute;
            top: 0; left: 0;
            width: 210mm; height: 297mm;
        }

        .cover-title-wrap {
            position: absolute;
            top: 14mm;
            left: 0;
            width: 210mm;
            text-align: center;
        }

        .cover-title-img {
            display: block;
            margin: 0 auto;
            width: 170mm;
            height: auto;
            object-fit: contain;
        }

        .cover-subtitle {
            font-family: 'svn-poppins', sans-serif;
            color: #6E0101;
            font-weight: bold;
            font-style: normal !important;
            font-size: 13pt;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 3mm;
            text-align: center;
        }
    </style>
</head>
<body>
@php use App\Services\NguHanhTitleRenderer; @endphp
<div class="page">
    <img class="bg-img" src="{{ $bgPath }}">
    <div class="cover-title-wrap">
        @if (!empty($title))
            @php
                $titleSrc = NguHanhTitleRenderer::embedPath((string) ($titleImagePath ?? ''));
                if ($titleSrc === '') {
                    $embedded = NguHanhTitleRenderer::goldTitleEmbedded((string) $title, 30, 170.0);
                    $titleSrc = $embedded['src'] ?? '';
                    $titleHeightMm = $embedded['heightMm'] ?? 0;
                }
                $titleStyle = 'width:170mm;height:auto;object-fit:contain;';
                if (!empty($titleHeightMm)) {
                    $titleStyle .= 'max-height:'.$titleHeightMm.'mm;';
                }
            @endphp
            @if ($titleSrc !== '')
            <img class="cover-title-img" src="{!! $titleSrc !!}" style="{{ $titleStyle }}">
            @else
            <div class="cover-subtitle" style="font-size:30pt;margin-top:0;">{{ $title }}</div>
            @endif
        @endif
        @if (!empty($subtitle))
        <div class="cover-subtitle">{{ $subtitle }}</div>
        @endif
    </div>
</div>
</body>
</html>
