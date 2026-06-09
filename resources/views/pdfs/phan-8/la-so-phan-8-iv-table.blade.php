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
        .page:last-child { page-break-after: auto; }

        .bg-img {
            position: absolute;
            top: 0; left: 0;
            width: 210mm; height: 297mm;
        }

        @include('pdfs.partials.content-zone-styles')

        .content-zone {
            left: 16mm;
            width: 178mm;
            top: 15mm;
            height: 207.9mm;
        }

        .iv-section {
            margin-bottom: 6mm;
        }

        .iv-grid {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 9px;
            line-height: 1.2;
        }

        .iv-grid th,
        .iv-grid td {
            border: 1px solid #000;
            padding: 3px 2px;
            vertical-align: middle;
            text-align: center;
            word-wrap: break-word;
        }

        .iv-head {
            background: #6E0101;
            color: #fff;
            font-weight: bold;
            font-size: 10px;
        }

        .iv-label {
            background: #f3f4f6;
            font-weight: bold;
            text-align: left;
            width: 18mm;
        }

        .iv-chu-y-cell {
            text-align: left;
            font-size: 8px;
            line-height: 1.15;
        }

        .iv-depth-1  { background-color: #fffbeb; }
        .iv-depth-2  { background-color: #fef3c7; }
        .iv-depth-3  { background-color: #fde68a; }
        .iv-depth-4  { background-color: #fcd34d; }
        .iv-depth-5  { background-color: #fbbf24; }
        .iv-depth-6  { background-color: #f59e0b; }
        .iv-depth-7  { background-color: #d97706; }
        .iv-depth-8  { background-color: #b45309; }
        .iv-depth-9  { background-color: #92400e; color: #fff; }
        .iv-depth-10 { background-color: #78350f; color: #fff; }
        .iv-depth-11 { background-color: #451a03; color: #fff; }
        .iv-depth-12 { background-color: #292524; color: #fff; }
    </style>
</head>
<body>

<div class="page">
    <img class="bg-img" src="{{ $bgPath }}">
    <div class="content-zone">

        @php
        $depthClass = static function (array $yr): string {
            $chuY = is_array($yr['chu_y'] ?? null) ? $yr['chu_y'] : [];
            $n = count($chuY);
            return $n > 0 ? 'iv-depth-' . min($n, 12) : '';
        };
        @endphp

        @foreach ($tables as $table)
        @php
            $years    = is_array($table['years'] ?? null) ? $table['years'] : [];
            $ageLabel = $table['age'] ?? '';
            $colSpan  = count($years) + 1;
        @endphp

        @if ($years !== [])
        <div class="iv-section">
            <table class="iv-grid">
                <thead>
                    <tr>
                        <th class="iv-head" colspan="{{ $colSpan }}">Đại Vận {{ $ageLabel }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="iv-label">Niên Vận</td>
                        @foreach ($years as $yr)
                        <td class="{{ $depthClass($yr) }}">{{ $yr['nam'] ?? '' }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="iv-label">Thiên Can</td>
                        @foreach ($years as $yr)
                        <td class="{{ $depthClass($yr) }}">{{ $yr['thien_can'] ?? '' }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="iv-label">Địa Chi</td>
                        @foreach ($years as $yr)
                        <td class="{{ $depthClass($yr) }}">{{ $yr['dia_chi'] ?? '' }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="iv-label">Tàng Can</td>
                        @foreach ($years as $yr)
                        <td class="{{ $depthClass($yr) }}">{{ $yr['tang_can'] ?? '' }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="iv-label">Chú ý</td>
                        @foreach ($years as $yr)
                        @php $chuY = is_array($yr['chu_y'] ?? null) ? $yr['chu_y'] : []; @endphp
                        <td class="iv-chu-y-cell {{ $depthClass($yr) }}">
                            @foreach ($chuY as $lb)
                            <div>{{ $lb }}</div>
                            @endforeach
                        </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
        @endif

        @endforeach

    </div>
</div>

</body>
</html>
