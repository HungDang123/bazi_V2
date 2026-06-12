{{--
  Render đoạn văn — một <p> mỗi dòng logic (xuống dòng \n).
  Không tách wrapAtChars khi render: mỗi <p> một dòng ngắn thì justify không có tác dụng.
  wrapAtChars chỉ dùng trong paginator (PdfTextWrapHelper) để ước lượng chiều cao.
  @param string $text
  @param int $maxChars  (giữ để tương thích caller; không dùng khi render)
  @param bool $bulletPrefix  (giữ để tương thích caller; không thêm prefix khi render)
--}}
@php
    use App\Services\Pdf\PdfTextSanitizer;

    $raw = PdfTextSanitizer::trimMultiline((string) ($text ?? ''));
    $phan9SubLabels = ! empty($phan9SubLabels);
@endphp
@if ($raw !== '')
    @foreach (preg_split('/\r\n|\r|\n/', $raw) ?: [] as $line)
        @php
            $line = PdfTextSanitizer::trimString($line);
            if ($line === '') {
                continue;
            }
            $isSubLabel = $phan9SubLabels && PdfTextSanitizer::isPhan9SubLabelLine($line);
        @endphp
        @if ($isSubLabel)
        <p class="sub-label-line pdf-justify" style="text-align: justify; text-align-last: justify; color: #6E0101; font-weight: bold;">{{ $line }}</p>
        @else
        <p class="pdf-justify" style="text-align: justify; text-align-last: justify;">{{ $line }}</p>
        @endif
    @endforeach
@endif
