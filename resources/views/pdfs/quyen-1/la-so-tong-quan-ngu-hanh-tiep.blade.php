<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; padding: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            width: 210mm; height: 297mm;
            font-family: 'svn-poppins', sans-serif;
            font-size: 12px;
            font-weight: normal;
            line-height: 100%;
            text-align: justify;
            letter-spacing: 0;
        }

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

        @include('pdfs.partials.content-zone-styles')

        .content-zone {
            left: 28mm;
            width: 154mm;
            top: 15mm;
            height: 207.9mm;
        }

        .sub-title {
            font-family: 'svn-poppins', sans-serif;
            font-size: 14px;
            font-weight: bold;
            line-height: 100%;
            letter-spacing: 0;
            color: #6E0101;
            margin-top: 6px;
            margin-bottom: 3px;
        }

        .sub-section:first-child .sub-title {
            margin-top: 0;
        }

        .para-text {
            font-family: 'svn-poppins', sans-serif;
            font-size: 12px;
            font-weight: normal;
            line-height: 100%;
            letter-spacing: 0;
            text-align: justify;
            color: #1A1A1A;
        }

        .para-text p {
            margin-bottom: 3px;
        }

        .sub-section {
            margin-bottom: 4px;
        }
    </style>
</head>
<body>
<div class="page">

    <img class="bg-img" src="{{ $templatePath }}">

    <div class="content-zone">
        @foreach ($subSections as $sub)
        <div class="sub-section">
            @if (!empty($sub['sub_title']))
            <div class="sub-title">{{ $sub['sub_title'] }}</div>
            @endif

            @if (!empty($sub['content']))
            <div class="para-text">
                @foreach ($sub['content'] as $para)
                    <p>{{ $para }}</p>
                @endforeach
            </div>
            @endif
        </div>
        @endforeach
    </div>

</div>
</body>
</html>
