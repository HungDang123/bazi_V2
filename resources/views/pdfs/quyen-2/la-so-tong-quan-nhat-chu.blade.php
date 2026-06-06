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

        /* Phủ nền đỏ lên scroll để che text cũ trong ảnh (nếu có) */
        .scroll-cover {
            position: absolute;
            left: 14mm;
            width: 182mm;
            background: #6E0101;
        }

        /* Dòng phụ (nạp âm ngắn – nếu title có 2 dòng) */
        .scroll-sub {
            position: absolute;
            left: 14mm;
            width: 182mm;
            text-align: center;
            font-weight: bold;
            color: #E8C97A;
        }

        .scroll-main {
            position: absolute;
            left: 14mm;
            width: 182mm;
            text-align: center;
            font-weight: bold;
            color: #D4AF37;
        }

        /* Vùng nội dung (bên trong khung trắng, bắt đầu ~66mm) */
        .content-wrap {
            position: absolute;
            left: 28mm;
            width: 154mm;
            overflow: hidden;
        }

        /* Tên chapter: "Lý tổng quan", "1. Ý nghĩa trụ ngày" */
        .chapter-title {
            color: #6E0101;
            text-transform: uppercase;
            border-bottom: 1px solid #C9A227;
            padding-bottom: 2px;
            margin-bottom: 5px;
        }

        .sub-title {
            color: #333;
            margin-top: 5px;
            margin-bottom: 2px;
        }

        .para-text {
            color: #1A1A1A;
        }

        .chapter-block {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="page">

    <img class="bg-img" src="{{ $templatePath }}">

    @php
        // Tách nhatChuTitle thành sub + main nếu có newline
        $titleLines = array_filter(array_map('trim', preg_split('/[\r\n]+/', $nhatChuTitle)));
        $mainTitle  = mb_strtoupper(array_pop($titleLines) ?? '', 'UTF-8');
        $subTitle   = !empty($titleLines) ? mb_strtoupper(implode(' – ', $titleLines), 'UTF-8') : '';

        // Trang 17: chỉ hiện sub_sections có sub_title chứa "ý nghĩa" (mục 1)
        // "2. Phân tích hình ảnh ẩn dụ" được dành cho trang 18
        $includeSubKw = ['ý nghĩa', 'y nghia'];
        $excludeSubKw = ['phân tích hình ảnh', 'phan tich hinh anh', 'hình ảnh ẩn dụ'];

        $targetChapters = [];
        foreach ($chapters as $ch) {
            $filteredSubs = [];
            foreach ($ch['sub_sections'] as $sub) {
                $stLower = mb_strtolower(trim($sub['sub_title'] ?? ''), 'UTF-8');

                // Loại bỏ sub_section thuộc "2. Phân tích..."
                $excluded = false;
                foreach ($excludeSubKw as $kw) {
                    if (str_contains($stLower, $kw)) { $excluded = true; break; }
                }
                if ($excluded) continue;

                // Nếu sub_title trống hoặc khớp "ý nghĩa" → giữ lại
                $isMatch = empty(trim($sub['sub_title'] ?? ''));
                foreach ($includeSubKw as $kw) {
                    if (str_contains($stLower, $kw)) { $isMatch = true; break; }
                }
                if ($isMatch) {
                    $filteredSubs[] = $sub;
                }
            }
            if (!empty($filteredSubs)) {
                $targetChapters[] = array_merge($ch, ['sub_sections' => $filteredSubs]);
            }
        }
    @endphp

    {{-- Phủ nền lên scroll --}}
    <div class="scroll-cover" style="top: 24mm; height: 30mm;"></div>

    {{-- Sub-title (dòng nhỏ trên) --}}
    @if ($subTitle)
    <div class="scroll-sub" style="top: 27mm;">{{ $subTitle }}</div>
    @endif

    {{-- Main title (dòng lớn) --}}
    @if ($mainTitle)
    <div class="scroll-main" style="top: {{ $subTitle ? '33mm' : '35mm' }};">{{ $mainTitle }}</div>
    @endif

    {{-- Danh sách chapters --}}
    <div class="content-wrap" style="top: 66mm; height: 206mm;">
        @foreach ($targetChapters as $chapter)
        <div class="chapter-block">

            {{-- Tên chapter --}}
            <div class="chapter-title">{{ $chapter['chapter'] }}</div>

            {{-- Các sub-section --}}
            @foreach ($chapter['sub_sections'] as $sub)

                @if (!empty($sub['sub_title']))
                <div class="sub-title">{{ $sub['sub_title'] }}</div>
                @endif

                @php
                    $rawText   = $sub['content'] ?? '';
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

            @endforeach
        </div>
        @endforeach
    </div>

</div>
</body>
</html>
