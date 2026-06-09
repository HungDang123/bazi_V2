{{--
  Render text thành nhiều <p> ngắn — chỉ tách khi dòng vượt maxChars.
  @param string $text
  @param int $maxChars
  @param bool $bulletPrefix  Thêm '– ' cho mỗi bullet (coding Phần 8)
--}}
@php
    use App\Services\Pdf\PdfTextSanitizer;
    use App\Services\Pdf\PdfTextWrapHelper;

    $raw = PdfTextSanitizer::trimMultiline((string) ($text ?? ''));
    $maxChars = (int) ($maxChars ?? 72);
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
            $chunks = PdfTextWrapHelper::wrapAtChars($line, $maxChars);
        @endphp
        @foreach ($chunks as $chunk)
        <p>{{ $chunk }}</p>
        @endforeach
    @endforeach
@endif
