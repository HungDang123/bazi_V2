/* SVN-Poppins — đăng ký qua PdfFontService::registerWithDompdf()
 * Chỉ typography chữ; căn đều nằm ở pdf-justify-styles (include qua content-zone-styles).
 */
@include('pdfs.partials.pdf-fonts')

.para-text,
.item-title,
.chapter-title,
.sub-title,
.val-text,
.val-name,
.scroll-sub,
.scroll-main {
    font-family: 'svn-poppins', sans-serif;
    font-weight: normal;
    font-style: normal;
    font-size: 14px;
    line-height: 140%;
    letter-spacing: 0;
}

strong,
b,
.text-bold {
    font-family: 'svn-poppins', sans-serif;
    font-weight: bold;
    font-style: normal;
    font-size: 14px;
    line-height: 140%;
    letter-spacing: 0;
}

.para-text p {
    margin-bottom: 5px;
}
