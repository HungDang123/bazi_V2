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

        /* Vùng kem cuộn lịch — canh giữa vùng parchment LBTV-143 */
        .val-block {
            position: absolute;
            left: 27mm;
            width: 156mm;
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
            font-size: 18px;
            line-height: 122%;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        .val-text {
            font-size: 15px;
            line-height: 132%;
        }

        .val-text strong {
            font-weight: bold;
            color: #6E0101;
        }

        .val-bat-tu {
            font-size: 13px;
            line-height: 138%;
        }
    </style>
</head>
<body>
<div class="page">

    <img class="bg-img" src="{{ $templatePath }}">

    {{-- Vị trí giá trị theo label in sẵn page-02-bg.png (LBTV-143) --}}
    {{-- HỌ & TÊN: label ~113mm → value 122mm --}}
    <div class="val-block" style="top: 122mm;">
        <p class="val-name">{{ $fullName }}</p>
    </div>

    {{-- GIỚI TÍNH: label ~151mm → value 160mm --}}
    <div class="val-block" style="top: 160mm;">
        <p class="val-text">{{ $gender }}</p>
    </div>

    {{-- NGÀY SINH DƯƠNG LỊCH: label ~181mm → value 190mm --}}
    <div class="val-block" style="top: 190mm;">
        <p class="val-text">{{ $birthDate }}</p>
    </div>

    {{-- BÁT TỰ SINH THẦN: label ~214mm → value 223mm --}}
    @if (!empty($batTu))
    <div class="val-block" style="top: 223mm;">
        <p class="val-text val-bat-tu">{!! $batTu !!}</p>
    </div>
    @endif

    {{-- ĐỊA CHỈ: label ~246mm → value 255mm --}}
    @if (!empty($address))
    <div class="val-block" style="top: 255mm;">
        <p class="val-text">{{ $address }}</p>
    </div>
    @endif

</div>
</body>
</html>
