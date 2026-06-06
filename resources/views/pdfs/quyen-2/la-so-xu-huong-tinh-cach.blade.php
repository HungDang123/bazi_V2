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

        /* Vùng nội dung – toàn bộ trang trắng (0–277.8mm) */
        .content-wrap {
            position: absolute;
            left: 28mm;
            width: 154mm;
            overflow: hidden;
        }

        .chapter-title {
            color: #6E0101;
            text-transform: uppercase;
            border-bottom: 1px solid #C9A227;
            padding-bottom: 2px;
            margin-bottom: 6px;
        }

        .sub-title {
            color: #333;
            margin-top: 6px;
            margin-bottom: 2px;
        }

        .para-text {
            color: #1A1A1A;
        }

        .sub-section {
            margin-bottom: 6px;
        }
    </style>
</head>
<body>
<div class="page">

    <img class="bg-img" src="{{ $templatePath }}">

    @php
        // Tìm chapter "II. Xu hướng tính cách"
        $targetChapter = null;
        $searchKw = ['xu hướng', 'xu huong', 'tính cách', 'tinh cach'];

        foreach ($chapters as $ch) {
            $chLower = mb_strtolower(trim($ch['chapter'] ?? ''), 'UTF-8');
            foreach ($searchKw as $kw) {
                if (str_contains($chLower, $kw)) {
                    $targetChapter = $ch;
                    break 2;
                }
            }
        }

        // Fallback: chapter thứ 2 (index 1)
        if (!$targetChapter && count($chapters) >= 2) {
            $targetChapter = $chapters[1];
        }
    @endphp

    @if ($targetChapter)
    {{-- Content area: top=18mm, height=252mm (trước footer 277.8mm) --}}
    <div class="content-wrap" style="top: 18mm; height: 252mm;">

        <div class="chapter-title">{{ $targetChapter['chapter'] }}</div>

        @foreach ($targetChapter['sub_sections'] as $sub)
        <div class="sub-section">

            @if (!empty($sub['sub_title']))
            <div class="sub-title">{{ $sub['sub_title'] }}</div>
            @endif

            @php
                $rawText    = $sub['content'] ?? '';
                $paragraphs = array_filter(array_map('trim', preg_split('/\n{2,}/', $rawText)));
                if (empty($paragraphs)) {
                    $paragraphs = array_filter(array_map('trim', explode("\n", $rawText)));
                }
            @endphp

            <div class="para-text">
                @foreach ($paragraphs as $para)
                    <p>{{ $para }}</p>
                @endforeach
            </div>

        </div>
        @endforeach

    </div>
    @endif

</div>
</body>
</html>
