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

        .section-title {
            text-align: center;
            color: #6E0101;
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 5mm;
            text-transform: uppercase;
        }

        .muc-label {
            color: #6E0101;
            font-weight: bold;
            font-style: italic;
            font-size: 12px;
            margin-bottom: 3mm;
        }

        .para-text {
            color: #1A1A1A;
            font-size: 12px;
            line-height: 100%;
            margin-bottom: 5mm;
        }

        .content-img {
            display: block;
            width: 162mm;
            height: auto;
            margin: 4mm auto 0;
        }

        @include('pdfs.phan-5.partials.bat-tu-table-styles')
    </style>
</head>
<body>

@foreach ($pages ?? [] as $page)
<div class="page">

    <img class="bg-img" src="{{ $page['bgPath'] }}">

    <div class="content-zone" style="top: {{ $page['contentZoneTopMm'] ?? 15 }}mm; height: {{ $page['contentZoneHeightMm'] ?? 207.9 }}mm; left: {{ $page['contentLeftMm'] ?? 24 }}mm; width: {{ $page['contentWidthMm'] ?? 162 }}mm;">
@include('pdfs.phan-5.partials.paginated-blocks', [
            'blocks' => $page['blocks'] ?? [],
            'imageClass' => 'vi-tri-img',
        ])
        </div>
</div>
@endforeach

</body>
</html>
