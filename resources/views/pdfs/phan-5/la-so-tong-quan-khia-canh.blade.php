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

        .content-wrap {
            position: absolute;
            left: 28mm;
            width: 154mm;
            top: 79mm;
            height: 192mm;
            padding-top: 5mm;
            overflow: hidden;
        }

        .sub-title {
            color: #6E0101;
            font-weight: bold;
            font-size: 12px;
            margin-top: 5mm;
            margin-bottom: 2mm;
        }

        .sub-title:first-child {
            margin-top: 0;
        }

        .para-text {
            color: #1A1A1A;
            font-size: 12px;
            line-height: 100%;
        }

        .para-text p {
            margin-bottom: 3mm;
        }

        .content-img {
            display: block;
            width: 154mm;
            height: auto;
            margin: 6px 0 8px;
        }
    </style>
</head>
<body>

@foreach ($pages ?? [] as $page)
<div class="page">

    <img class="bg-img" src="{{ $page['bgPath'] }}">

    <div class="content-wrap">
        @include('pdfs.phan-5.partials.paginated-blocks', [
            'blocks' => $page['blocks'] ?? [],
            'imageClass' => '',
        ])
    </div>

</div>
@endforeach

</body>
</html>
