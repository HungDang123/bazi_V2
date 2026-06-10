.content-zone {
    position: absolute;
    overflow: hidden;
    background: transparent;
    z-index: 2;
    color: #1A1A1A;
}

/* DomPDF: tránh overlay trắng — chỉ set transparent cho text, không ép toàn bộ con */
.content-zone .para-text,
.content-zone .section-box,
.content-zone .section-block,
.content-zone .section-title,
.content-zone .kw-section,
.content-zone .muc-label,
.content-zone .item-title,
.content-zone .preamble-para,
.content-zone .trait-body-cell {
    position: relative;
    z-index: 1;
    background: transparent;
}

.content-zone p {
    color: #1A1A1A;
    background: transparent;
}

@include('pdfs.partials.pdf-justify-styles')
