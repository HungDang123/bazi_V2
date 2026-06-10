{{--
  Render đoạn văn — một <p> mỗi dòng logic (xuống dòng \n).
  Không tách wrapAtChars khi render: mỗi <p> một dòng ngắn thì justify không có tác dụng.
  wrapAtChars chỉ dùng trong paginator (PdfTextWrapHelper) để ước lượng chiều cao.
  @param string $text
  @param int $maxChars  (giữ để tương thích caller; không dùng khi render)
  @param bool $bulletPrefix  Thêm '– ' cho mỗi bullet (coding Phần 8)
--}}
@php
    use App\Services\Pdf\PdfTextSanitizer;

    $raw = PdfTextSanitizer::trimMultiline((string) ($text ?? ''));
    $bulletPrefix = (bool) ($bulletPrefix ?? false);
@endphp
@if ($raw !== '')
    @foreach (preg_split('/\r\n|\r|\n/', $raw) ?: [] as $line)
        @php
            $line = PdfTextSanitizer::trimString($line);
            if ($line === '') {
                continue;
            }
            if ($bulletPrefix) {
                $line = preg_match('/^-\s*/u', $line)
                    ? '– '.PdfTextSanitizer::trimString(preg_replace('/^-\s*/u', '', $line))
                    : '– '.$line;
            }
        @endphp
        <p class="pdf-justify" style="text-align: justify; text-align-last: justify;">{{ $line }}</p>
    @endforeach
@endif
