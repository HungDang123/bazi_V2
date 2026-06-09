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

        .traits-row {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 8mm;
        }

        .traits-row > tbody > tr > td {
            width: 50%;
            vertical-align: top;
            padding: 0 2mm;
        }

        .traits-row > tbody > tr > td:first-child { padding-left: 0; }
        .traits-row > tbody > tr > td:last-child { padding-right: 0; }

        .trait-box {
            width: 100%;
            border-collapse: collapse;
            border: 0.45mm solid;
        }

        .trait-box.tich-cuc { border-color: #4CAF50; }
        .trait-box.tieu-cuc { border-color: #C62828; }

        .trait-pill-cell { text-align: center; padding: 2.5mm 2mm 3mm; }

        .trait-pill {
            display: inline-block;
            padding: 1.2mm 7mm;
            border-radius: 99mm;
            color: #FFFFFF;
            font-weight: bold;
            font-size: 12px;
            line-height: 120%;
        }

        .trait-box.tich-cuc .trait-pill { background: #4CAF50; }
        .trait-box.tieu-cuc .trait-pill { background: #8B1A1A; }

        .trait-body-cell {
            color: #1A1A1A;
            font-size: 14px;
            line-height: 140%;
            padding: 0 3.5mm 3.5mm;
            vertical-align: top;
        }

        .trait-body-cell p { margin-bottom: 2.5mm; }
        .trait-body-cell p:last-child { margin-bottom: 0; }

        .muc-label {
            color: #6E0101;
            font-weight: bold;
            font-style: italic;
            font-size: 14px;
            line-height: 130%;
            margin-bottom: 3mm;
        }

        .para-text {
            color: #1A1A1A;
            font-size: 14px;
            line-height: 140%;
        }

        .para-text p { margin-bottom: 2.5mm; }
    </style>
</head>
<body>

@foreach ($pages ?? [] as $page)
<div class="page">

    <img class="bg-img" src="{{ $page['bgPath'] }}">

    <div class="content-zone" style="top: {{ $page['contentZoneTopMm'] ?? 18 }}mm; height: {{ $page['contentZoneHeightMm'] ?? 187.1 }}mm; left: {{ $page['contentLeftMm'] ?? 22 }}mm; width: {{ $page['contentWidthMm'] ?? 166 }}mm;">
@include('pdfs.phan-5.partials.paginated-blocks', [
            'blocks' => $page['blocks'] ?? [],
        ])
        </div>
</div>
@endforeach

</body>
</html>
