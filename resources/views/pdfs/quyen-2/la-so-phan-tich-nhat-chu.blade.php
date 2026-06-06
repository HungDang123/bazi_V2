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

        /* Vùng nội dung – trong khoảng trắng phía trên (0–149mm) */
        .content-wrap {
            position: absolute;
            left: 28mm;
            width: 154mm;
            overflow: hidden;
        }

        /* Tiêu đề chapter */
        .chapter-title {
            color: #6E0101;
            text-transform: uppercase;
            border-bottom: 1px solid #C9A227;
            padding-bottom: 2px;
            margin-bottom: 6px;
        }

        .sub-title {
            color: #333;
            margin-top: 5px;
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
        // Trang 18: chỉ hiện sub_sections có sub_title chứa "phân tích hình ảnh" (mục 2)
        $includeSubKw = ['phân tích hình ảnh', 'phan tich hinh anh', 'hình ảnh ẩn dụ'];

        $targetChapter = null;
        foreach ($chapters as $ch) {
            $filteredSubs = [];
            foreach ($ch['sub_sections'] as $sub) {
                $stLower = mb_strtolower(trim($sub['sub_title'] ?? ''), 'UTF-8');
                foreach ($includeSubKw as $kw) {
                    if (str_contains($stLower, $kw)) {
                        $filteredSubs[] = $sub;
                        break;
                    }
                }
            }
            if (!empty($filteredSubs)) {
                $targetChapter = array_merge($ch, ['sub_sections' => $filteredSubs]);
                break;
            }
        }
        // Fallback: lấy sub_section thứ 2 của chapter đầu tiên
        if (!$targetChapter && !empty($chapters[0]['sub_sections'])) {
            $subs = $chapters[0]['sub_sections'];
            if (count($subs) >= 2) {
                $targetChapter = array_merge($chapters[0], ['sub_sections' => [array_slice($subs, 1)]]);
            }
        }
    @endphp

    @if ($targetChapter)
    {{-- Nội dung vào vùng trắng trên (top ≈ 18mm, max-height ≈ 124mm trước khi gặp illustration) --}}
    <div class="content-wrap" style="top: 18mm; height: 124mm;">

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
