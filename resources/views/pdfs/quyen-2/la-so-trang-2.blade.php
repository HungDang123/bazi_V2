<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; padding: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @include('pdfs.partials.pdf-justify-styles')
        body {
            width: 210mm; height: 297mm;
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
        }

        .bg-img {
            position: absolute;
            top: 0; left: 0;
            width: 210mm; height: 297mm;
        }

        /* Vùng kem cuộn lịch — canh giữa vùng parchment LBTV-494 */
        .val-block {
            position: absolute;
            left: 28mm;
            width: 154mm;
            text-align: center;
        }

        .val-block p {
            margin: 0;
            padding: 0;
        }

        .val-name,
        .val-text {
            color: #6E0101;
            font-family: 'svn-poppins', sans-serif;
            font-weight: bold;
            text-align: center !important;
        }

        .val-name {
            font-size: 17px;
            line-height: 115%;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        .val-text {
            font-size: 14px;
            line-height: 115%;
        }

        .val-text strong {
            font-weight: bold;
            color: #6E0101;
        }

        .val-bat-tu {
            font-size: 12px;
            line-height: 120%;
        }
    </style>
</head>
<body>
<div class="page">

    <img class="bg-img" src="{{ $templatePath }}">

    {{-- Vị trí giá trị — dưới nhãn in sẵn LBTV-494 (5 hàng ~32mm) --}}
    <div class="val-block" style="top: 137.5mm;">
        <p class="val-name">{{ $fullName }}</p>
    </div>

    <div class="val-block" style="top: 150mm;">
        <p class="val-text">{{ $gender }}</p>
    </div>

    <div class="val-block" style="top: 182mm;">
        <p class="val-text">{{ $birthDate }}</p>
    </div>

    @if (!empty($batTu))
    <div class="val-block" style="top: 214mm;">
        <p class="val-text val-bat-tu">{!! $batTu !!}</p>
    </div>
    @endif

    @if (!empty($address))
    <div class="val-block" style="top: 246mm;">
        <p class="val-text">{{ $address }}</p>
    </div>
    @endif

</div>
</body>
</html>
