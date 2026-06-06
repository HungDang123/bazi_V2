<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @include('pdfs.partials.pdf-base-typography')
        @page { margin: 0; padding: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { width: 210mm; height: 297mm; }

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

        .content-wrap {
            position: absolute;
            left: 28mm;
            width: 154mm;
            overflow: hidden;
        }

        .sub-title {
            color: #333;
            margin-top: 6px;
            margin-bottom: 3px;
        }

        .sub-section:first-child .sub-title {
            margin-top: 0;
        }

        .para-text {
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

    <div class="content-wrap" style="top: 16mm; height: 268mm;">
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
