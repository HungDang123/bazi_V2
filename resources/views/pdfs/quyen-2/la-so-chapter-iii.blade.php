<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; padding: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            width: 210mm; height: 297mm;
            font-family: 'svn-poppins', sans-serif;
            font-size: 12px;
            font-weight: normal;
            line-height: 100%;
            text-align: justify;
            letter-spacing: 0;
        }

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

        .content-wrap {
            position: absolute;
            left: 28mm;
            width: 154mm;
            overflow: hidden;
        }

        .chapter-title {
            font-family: 'svn-poppins', sans-serif;
            font-size: 14px;
            font-weight: bold;
            line-height: 100%;
            letter-spacing: 0;
            color: #6E0101;
            text-transform: uppercase;
            border-bottom: 1px solid #C9A227;
            padding-bottom: 2px;
            margin-bottom: 6px;
        }

        .sub-title {
            font-family: 'svn-poppins', sans-serif;
            font-size: 14px;
            font-weight: bold;
            line-height: 100%;
            letter-spacing: 0;
            color: #6E0101;
            margin-top: 6px;
            margin-bottom: 2px;
        }

        .para-text {
            font-family: 'svn-poppins', sans-serif;
            font-size: 12px;
            font-weight: normal;
            line-height: 100%;
            letter-spacing: 0;
            text-align: justify;
            color: #1A1A1A;
        }

        .para-text p {
            margin-bottom: 4px;
        }

        .sub-section { margin-bottom: 6px; }
    </style>
</head>
<body>
<div class="page">

    <img class="bg-img" src="{{ $templatePath }}">

    @php
        // Tìm chapter "III." (mục 3 la mã)
        $targetChapter = null;

        foreach ($chapters as $ch) {
            $chLower = mb_strtolower(trim($ch['chapter'] ?? ''), 'UTF-8');
            // Match "iii" hoặc bắt đầu bằng "3."
            if (preg_match('/\biii\b/', $chLower) || str_starts_with(ltrim($chLower), '3.')) {
                $targetChapter = $ch;
                break;
            }
        }

        // Fallback: chapter thứ 3 (index 2)
        if (!$targetChapter && count($chapters) >= 3) {
            $targetChapter = $chapters[2];
        }
    @endphp

    @if ($targetChapter)
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
