/* Căn đều — CHỈ vùng prose, không dùng selector `p` / `.page *` (đụng TOC, bảng, pill). */
.para-text,
.para-text p,
.content-wrap p,
.content-wrap .para-text,
.content-wrap .para-text p,
.content-zone .para-text,
.content-zone .para-text p,
.content-zone .trait-body-cell p,
.preamble-para,
.preamble-para p,
.section-box p,
.section-block p,
.section-box li,
.iv-intro-para,
.iv-intro-para p,
.trait-body-cell p,
.pdf-justify {
    text-align: justify !important;
    text-align-last: justify;
}

.para-text p,
.content-zone .para-text p,
.content-wrap .para-text p {
    width: 100%;
}
