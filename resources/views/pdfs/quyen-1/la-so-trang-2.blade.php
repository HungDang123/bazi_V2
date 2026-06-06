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

        .val-block {
            position: absolute;
            left: 28mm;
            width: 154mm;
            text-align: center;
        }

        .val-name {
            color: #2E0A00;
            font-weight: bold;
            font-size: 17px;
            line-height: 112%;
            text-transform: uppercase;
            text-align: center;
        }

        .val-text {
            color: #2E0A00;
            font-weight: bold;
            font-size: 14px;
            line-height: 116%;
            text-align: center;
        }

        .val-text strong {
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="page">

    {{-- Ảnh nền cuộn lịch (labels có sẵn trên ảnh) --}}
    <img class="bg-img" src="{{ $templatePath }}">

    {{-- Họ & Tên (label ≈ 148.5mm) --}}
    <div class="val-block" style="top: 154mm;">
        <p class="val-name">{{ $fullName }}</p>
    </div>

    {{-- Giới Tính (label ≈ 165mm) --}}
    <div class="val-block" style="top: 170.5mm;">
        <p class="val-text">{{ $gender }}</p>
    </div>

    {{-- Ngày Sinh Dương Lịch (label ≈ 182mm) --}}
    <div class="val-block" style="top: 187.5mm;">
        <p class="val-text">{{ $birthDate }}</p>
    </div>

    {{-- Bát Tự Sinh Thần (label ≈ 210mm) --}}
    @if (!empty($batTu))
    <div class="val-block" style="top: 215.5mm;">
        <p class="val-text">{!! $batTu !!}</p>
    </div>
    @endif

    {{-- Địa Chỉ (label ≈ 233mm) --}}
    @if (!empty($address))
    <div class="val-block" style="top: 238.5mm;">
        <p class="val-text">{{ $address }}</p>
    </div>
    @endif

</div>
</body>
</html>
