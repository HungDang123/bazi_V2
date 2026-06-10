/* Căn lề 2 bên — DomPDF cần khai báo trực tiếp trên <p>, không kế thừa từ body */
p,
.para-text,
.para-text p,
.content-wrap p,
.content-wrap .para-text,
.content-wrap .para-text p,
.content-zone p,
.content-zone .para-text,
.content-zone .para-text p,
.preamble-para,
.preamble-para p,
.section-box p,
.section-block p,
.section-box li,
.iv-intro-para,
.iv-intro-para p,
.trait-body-cell p {
    text-align: justify !important;
    text-align-last: justify;
}

.para-text p,
.content-zone .para-text p,
.content-wrap .para-text p {
    width: 100%;
}

.pdf-justify {
    text-align: justify !important;
}
