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
            left: 22mm;
            width: 166mm;
            top: 24mm;
            height: 248mm;
            overflow: hidden;
        }

        .content-wrap.content-wrap-lbtv119 {
            left: 24mm;
            width: 162mm;
            top: 18mm;
            height: 258mm;
        }

        .item-title {
            text-align: center;
            color: #6E0101;
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 4mm;
            text-transform: uppercase;
        }

        .muc-label {
            color: #6E0101;
            font-weight: bold;
            font-style: italic;
            font-size: 12px;
            margin-bottom: 3mm;
        }

        .content-img {
            display: block;
            width: 166mm;
            height: auto;
            margin: 0 auto 4mm;
        }

        .content-wrap-lbtv119 .content-img {
            width: 162mm;
        }

        .para-text {
            color: #1A1A1A;
            font-size: 12px;
            line-height: 100%;
            margin-bottom: 2mm;
        }

        .kw-grid { width: 100%; margin-bottom: 5mm; }
        .kw-grid table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .kw-grid td { width: 33.33%; text-align: center; vertical-align: middle; padding: 0; }
        .kw-box { position: relative; width: 36.78mm; height: 59.71mm; margin: 0 auto; }
        .kw-frame { position: absolute; top: 0; left: 0; width: 36.78mm; height: 59.71mm; display: block; }
        .kw-text { position: absolute; top: 11mm; left: 5mm; width: 26.78mm; height: 36mm; }
        .kw-text table { width: 100%; height: 100%; border-collapse: collapse; table-layout: fixed; }
        .kw-text-cell {
            width: 26.78mm; height: 36mm; vertical-align: middle; text-align: center; padding: 0;
        }
        .kw-text-cell span {
            display: inline-block; max-width: 100%; color: #D4AF37; font-weight: bold;
            font-size: 12px; line-height: 120%; text-align: center;
            word-wrap: break-word; word-break: break-word; white-space: normal;
        }
    </style>
</head>
<body>

@foreach ($pages ?? [] as $page)
@php $isLbtv119 = ($page['layoutVariant'] ?? '') === 'lbtv119'; @endphp
<div class="page">

    <img class="bg-img" src="{{ $page['bgPath'] }}">

    <div class="content-wrap{{ $isLbtv119 ? ' content-wrap-lbtv119' : '' }}">
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
