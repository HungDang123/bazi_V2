/* SVN-Poppins – đăng ký qua PdfFontService::registerWithDompdf()
 * Typography:
 *   Body : 14px, weight 400, line-height 140%, justify
 *   Bold : 14px, weight 700, line-height 140%, justify
 */

body,
.page,
.page * {
    font-family: 'svn-poppins', sans-serif;
    font-style: normal;
    letter-spacing: 0;
}

body {
    font-weight: normal;
    font-size: 14px;
    line-height: 140%;
    text-align: justify;
}

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
    text-align: justify;
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
    text-align: justify;
}

@include('pdfs.partials.pdf-justify-styles')
