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

        .cover-title {
            font-family: 'utm-davida', serif;
            color: #6E0101;
            font-weight: normal;
            font-size: 30pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            line-height: 120%;
        }

        .cover-subtitle {
            font-family: 'svn-poppins', sans-serif;
            color: #6E0101;
            font-weight: bold;
            font-size: 13pt;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 3mm;
        }
    </style>
</head>
<body>
<div class="page">
    <img class="bg-img" src="{{ $bgPath }}">
    <div class="cover-title-wrap">
        @if (!empty($title))
        <div class="cover-title">{{ $title }}</div>
        @endif
        @if (!empty($subtitle))
        <div class="cover-subtitle">{{ $subtitle }}</div>
        @endif
    </div>
</div>
</body>
</html>
