@foreach ($blocks as $block)
    @php $type = $block['type'] ?? 'para'; @endphp

    @if ($type === 'item_title')
    <div class="item-title">{{ $block['text'] ?? '' }}</div>
    @elseif ($type === 'section_title')
    <div class="section-title">{{ $block['text'] ?? '' }}</div>
    @elseif ($type === 'sub_title')
    <div class="sub-title">{{ $block['text'] ?? '' }}</div>
    @elseif ($type === 'muc_label' || $type === 'chien_luoc_title')
    <div class="muc-label">{{ $block['text'] ?? '' }}</div>
    @elseif ($type === 'keywords')
    @include('pdfs.phan-5.partials.keyword-boxes', [
        'keywords' => $block['keywords'] ?? [],
        'keywordFramePath' => $block['keywordFramePath'] ?? ($keywordFramePath ?? ''),
        'label' => $block['label'] ?? 'Ba từ khóa cốt lõi',
    ])
    @elseif ($type === 'table')
    @include('pdfs.phan-5.partials.bat-tu-table', [
        'batTu' => $block['batTu'] ?? [],
        'highlightPillars' => $block['highlightPillars'] ?? [],
    ])
    @elseif ($type === 'image')
    @php
        if (! empty($block['renderWidthMm']) && ! empty($block['renderHeightMm'])) {
            $imgStyle = sprintf(
                'display:block;width:%smm;height:%smm;margin:0 auto 4mm;',
                $block['renderWidthMm'],
                $block['renderHeightMm']
            );
        } else {
            $imgStyle = 'display:block;width:100%;height:auto;margin:0 auto 4mm;';
        }
    @endphp
    <img class="content-img{{ ($imageClass ?? '') !== '' ? ' '.$imageClass : '' }}"
         style="{{ $imgStyle }}"
         src="{{ $block['path'] }}"
         alt="">
    @elseif ($type === 'traits')
    @include('pdfs.phan-5.partials.traits-row', [
        'tichCuc' => $block['tichCuc'] ?? '',
        'tieuCuc' => $block['tieuCuc'] ?? '',
    ])
    @else
    <div class="para-text">
        @include('pdfs.partials.pdf-text-chunks', [
            'text' => $block['text'] ?? '',
            'maxChars' => 68,
            'bulletPrefix' => false,
        ])
    </div>
    @endif
@endforeach
