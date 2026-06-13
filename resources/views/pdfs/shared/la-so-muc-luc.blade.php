<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @include('pdfs.partials.pdf-fonts')
        @page { margin: 0; padding: 0; }
        * { margin: 0; padding: 0; }

        .page {
            position: relative;
            width: 210mm;
            height: 297mm;
            overflow: hidden;
            page-break-after: always;
        }
        .page:last-child { page-break-after: auto; }

        .bg-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 210mm;
            height: 297mm;
            z-index: 1;
        }

        .toc-box {
            position: absolute;
            left: 14mm;
            width: 172mm;
            top: 50mm;
            z-index: 2;
        }

        .toc-box--continued { top: 18mm; }

        .toc-continued-label {
            text-align: center;
            color: #6E0101;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 4mm;
        }

        .toc-frame {
            width: 172mm;
            border: 1.2px solid #C9A227;
            border-radius: 10mm;
            padding: 8mm 9mm 12mm 9mm;
            box-sizing: border-box;
            background: transparent;
        }

        .toc-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .toc-grid td {
            vertical-align: bottom;
            padding: 0;
            border: 0;
            font-style: normal;
            letter-spacing: 0;
            background: transparent;
        }

        .toc-row-part td { padding-top: 3.5mm; padding-bottom: 1.8mm; }
        .toc-row-part:first-child td { padding-top: 0; }
        .toc-row-item td { padding-bottom: 2.2mm; }

        .toc-line-title {
            width: 1px;
            white-space: nowrap;
            text-align: left;
            padding-right: 2mm;
        }

        .toc-row-item .toc-line-title {
            padding-left: 4mm;
        }

        .toc-line-leader {
            border-bottom: 1px dotted #888888;
            padding-bottom: 1.4mm;
        }

        .toc-line-page {
            width: 1px;
            white-space: nowrap;
            text-align: right;
            padding-left: 2mm;
            padding-bottom: 1.4mm;
            border-bottom: 1px dotted #888888;
        }

        .toc-part-text {
            color: #6E0101;
            font-weight: bold;
            font-size: 10.5pt;
            line-height: 130%;
            text-transform: uppercase;
        }

        .toc-item-text {
            color: #444444;
            font-size: 9.5pt;
            line-height: 130%;
            text-transform: uppercase;
        }

        .toc-page-part {
            color: #6E0101;
            font-weight: bold;
            font-size: 10.5pt;
        }

        .toc-page-item {
            color: #444444;
            font-size: 9.5pt;
        }

        /* Thuyền rồng — lớp trên, nền PNG trong suốt */
        .toc-dragon {
            position: absolute;
            right: 0;
            bottom: 0;
            width: 118mm;
            height: auto;
            z-index: 4;
        }
    </style>
</head>
<body>

@php use App\Services\Pdf\PdfImageEmbed; @endphp

@foreach ($pages as $pageIndex => $page)
@php
    $bgSrc = PdfImageEmbed::src((string) ($page['templatePath'] ?? ''));
    $dragonSrc = PdfImageEmbed::src((string) ($page['dragonOverlayPath'] ?? ''));
@endphp
<div class="page">
    @if ($bgSrc !== '')
    <img class="bg-img" src="{!! $bgSrc !!}">
    @endif
    <div class="toc-box{{ $pageIndex > 0 ? ' toc-box--continued' : '' }}">
        @if ($pageIndex > 0)
        <div class="toc-continued-label">Mục lục (tiếp theo)</div>
        @endif
        <div class="toc-frame">
            <table class="toc-grid" cellpadding="0" cellspacing="0">
                @foreach ($page['rows'] ?? [] as $row)
                    @php $isPart = ($row['type'] ?? '') === 'part'; @endphp
                    <tr class="{{ $isPart ? 'toc-row-part' : 'toc-row-item' }}">
                        <td class="toc-line-title">
                            @if ($isPart)
                            <span class="toc-part-text">{{ $row['title'] ?? '' }}</span>
                            @else
                            <span class="toc-item-text">{{ $row['title'] ?? '' }}</span>
                            @endif
                        </td>
                        <td class="toc-line-leader">&nbsp;</td>
                        <td class="toc-line-page">
                            @if ($isPart)
                            <span class="toc-page-part">{{ $row['page'] ?? '' }}</span>
                            @else
                            <span class="toc-page-item">{{ $row['page'] ?? '' }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
    @if ($dragonSrc !== '')
    <img class="toc-dragon" src="{!! $dragonSrc !!}">
    @endif
</div>
@endforeach

</body>
</html>
