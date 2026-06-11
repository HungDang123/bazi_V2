<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; padding: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @include('pdfs.partials.pdf-justify-styles')
        body {
            width: 210mm; height: 297mm;
            font-family: 'svn-poppins', sans-serif;
            font-size: 14px;
            font-weight: normal;
            line-height: 140%;
            text-align: justify;
            letter-spacing: 0;
        }

        .page {
            position: relative;
            width: 210mm;
            min-height: 297mm;
        }

        .bg-img {
            position: absolute;
            top: 0; left: 0;
            width: 210mm; height: 297mm;
        }

        /* Vùng nội dung – trong khoảng trắng phía trên (0–149mm) */
        .content-wrap {
            position: relative;
            z-index: 1;
            margin-left: 28mm;
            margin-top: 18mm;
            width: 154mm;
        }

        /* Tiêu đề chapter */
        .chapter-title {
            font-family: 'svn-poppins', sans-serif;
            font-size: 16px;
            font-weight: bold;
            line-height: 130%;
            letter-spacing: 0;
            color: #6E0101;
            text-transform: uppercase;
            border-bottom: 1px solid #C9A227;
            padding-bottom: 3px;
            margin-bottom: 8px;
        }

        .sub-title {
            font-family: 'svn-poppins', sans-serif;
            font-size: 16px;
            font-weight: bold;
            line-height: 130%;
            letter-spacing: 0;
            color: #6E0101;
            margin-top: 8px;
            margin-bottom: 4px;
        }

        .para-text {
            font-family: 'svn-poppins', sans-serif;
            font-size: 14px;
            font-weight: normal;
            line-height: 140%;
            letter-spacing: 0;
            text-align: justify;
            color: #1A1A1A;
        }

        .para-text p {
            margin-bottom: 5px;
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
    <div class="content-wrap" style="max-height: 124mm; overflow: hidden;">

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

    {{-- Ảnh Tuy Hỷ dưới nội dung --}}
    @if (!empty($tuyHyPath))
    <img src="{{ $tuyHyPath }}" style="
        position: relative;
        z-index: 1;
        display: block;
        width: 160mm;
        margin-left: 25mm;
        margin-top: 6mm;
    ">
    @endif

</div>
</body>
</html>
