@php
    $footerBannerPath = $footerBannerPath ?? null;
    $pageNumber = $pageNumber ?? '';
    $fullName = $fullName ?? '';
@endphp

@if (!empty($footerBannerPath))
<div class="page-footer">
    <div class="footer-banner-wrap">
        <img class="footer-banner" src="{{ $footerBannerPath }}">
        @if ($pageNumber !== '')
        <span class="footer-page-number">{{ $pageNumber }}</span>
        @endif
    </div>
    @if ($fullName !== '')
    <div class="footer-name">{{ $fullName }}</div>
    @endif
</div>
@endif
