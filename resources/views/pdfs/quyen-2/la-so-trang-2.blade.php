<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
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

        /* Vùng parchment cuộn lịch LBTV-143 */
        .scroll-content {
            position: absolute;
            left: 28mm;
            top: 114mm;
            width: 154mm;
            height: 148mm;
        }

        .scroll-table {
            width: 100%;
            height: 148mm;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .scroll-table td {
            vertical-align: middle;
            text-align: center;
            padding: 0;
        }

        .field-label-img,
        .val-kw-img {
            display: block;
            margin: 0 auto;
        }

        .field-label-img {
            margin-bottom: 1.2mm;
        }
    </style>
</head>
<body>
@php
    use App\Services\NguHanhTitleRenderer;

    $kwPlain = static function (string $text): string {
        return trim(strip_tags($text));
    };

    $batTuPlain = trim(strip_tags(str_replace(['<strong>', '</strong>'], '', $batTu ?? '')));

    /** Chiều ngang tối đa trong vùng kem cuộn lịch. */
    $valueW = 128.0;

    $rows = [
        [
            'label' => 'Họ & Tên',
            'value' => $fullName ?? '',
            'valPx' => 20,
            'minH'  => 14.0,
        ],
        [
            'label' => 'Giới tính',
            'value' => $gender ?? '',
            'valPx' => 20,
            'minH'  => 12.0,
        ],
        [
            'label' => 'Ngày sinh dương lịch',
            'value' => $birthDate ?? '',
            'valPx' => 18,
            'minH'  => 14.0,
        ],
    ];

    if ($batTuPlain !== '') {
        $rows[] = [
            'label' => 'Bát tự sinh thần',
            'value' => $batTuPlain,
            'valPx' => 14,
            'minH'  => 14.0,
        ];
    }

    if (trim((string) ($address ?? '')) !== '') {
        $rows[] = [
            'label' => 'Địa chỉ',
            'value' => $address,
            'valPx' => 18,
            'minH'  => 14.0,
        ];
    }

    $rowCount  = max(1, count($rows));
    $rowHeight = round(148 / $rowCount, 2);
    $labelW    = 145.0;
    $labelH    = 6.0;
    $labelPx   = 14;
@endphp
<div class="page">

    <img class="bg-img" src="{{ $templatePath }}">

    <div class="scroll-content">
        <table class="scroll-table">
            @foreach ($rows as $row)
            @php
                $labelImg = NguHanhTitleRenderer::scrollLabelImagePath($row['label'], $labelW, $labelH, $labelPx);
                $labelSrc = NguHanhTitleRenderer::embedPath($labelImg);
                $valueText = $kwPlain($row['value']);
                $valueMeta = $valueText !== ''
                    ? NguHanhTitleRenderer::scrollValueImageMetrics($valueText, $valueW, $row['valPx'], $row['minH'])
                    : ['path' => '', 'widthMm' => $valueW, 'heightMm' => $row['minH']];
                $valueSrc = NguHanhTitleRenderer::embedPath((string) ($valueMeta['path'] ?? ''));
            @endphp
            <tr style="height: {{ $rowHeight }}mm;">
                <td>
                    @if ($labelSrc !== '')
                    <img class="field-label-img" style="width: {{ $labelW }}mm; height: {{ $labelH }}mm;" src="{!! $labelSrc !!}" alt="">
                    @endif
                    @if ($valueSrc !== '')
                    <img class="val-kw-img" style="width: {{ $valueMeta['widthMm'] }}mm; height: {{ $valueMeta['heightMm'] }}mm;" src="{!! $valueSrc !!}" alt="">
                    @endif
                </td>
            </tr>
            @endforeach
        </table>
    </div>

</div>
</body>
</html>
