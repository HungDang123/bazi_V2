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

        .item-title {
            text-align: center;
            color: #6E0101;
            font-weight: bold;
            font-size: 16px;
            line-height: 130%;
            margin-bottom: 4mm;
            text-transform: uppercase;
        }

        .muc-label {
            color: #6E0101;
            font-weight: bold;
            font-style: italic;
            font-size: 14px;
            line-height: 130%;
            margin-bottom: 3mm;
        }

        .content-img {
            display: block;
            width: 100%;
            height: auto;
            margin: 0 auto 4mm;
        }

        .para-text {
            color: #1A1A1A;
            margin-bottom: 2mm;
            background: transparent;
        }
        .para-text p {
            font-size: 14px;
            line-height: 15pt;
            display: block;
            margin: 0;
            padding-bottom: 2mm;
            color: #1A1A1A;
            background: transparent;
        }
        .para-text p:last-child { padding-bottom: 0; }

        @include('pdfs.phan-5.partials.keyword-box-styles')

        @include('pdfs.phan-5.partials.traits-row-styles')
    </style>
</head>
<body>

@foreach ($pages ?? [] as $page)
<div class="page">

    <img class="bg-img" src="{{ $page['bgPath'] }}">

    <div class="content-zone" style="top: {{ $page['contentZoneTopMm'] ?? 18 }}mm; height: {{ $page['contentZoneHeightMm'] ?? 240 }}mm; left: {{ $page['contentLeftMm'] ?? 22 }}mm; width: {{ $page['contentWidthMm'] ?? 166 }}mm;">
@include('pdfs.phan-5.partials.paginated-blocks', [
            'blocks' => $page['blocks'] ?? [],
            'keywordFramePath' => $keywordFramePath ?? '',
            'imageClass' => 'minh-hoa',
        ])
        </div>
</div>
@endforeach

</body>
</html>
