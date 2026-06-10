<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PDF merge driver
    |--------------------------------------------------------------------------
    |
    | auto  — dùng qpdf nếu có trong PATH, không thì FPDI
    | qpdf  — bắt buộc qpdf (fallback FPDI nếu lệnh thất bại)
    | fpdi  — PHP FPDI (chậm hơn, không cần binary)
    |
    */
    'merge_driver' => env('PDF_MERGE_DRIVER', 'auto'),

    /** Đường dẫn tuyệt đối tới qpdf (tùy chọn, vd. C:\Program Files\qpdf\bin\qpdf.exe) */
    'qpdf_binary' => env('PDF_QPDF_BINARY'),

    /** Đường dẫn tuyệt đối tới pdftk (tùy chọn) */
    'pdftk_binary' => env('PDF_PDFTK_BINARY'),

    /** Ghi log thời gian render/merge/footer mỗi lần xuất PDF */
    'log_timing' => env('PDF_LOG_TIMING', true),

    /** Queue riêng cho job tạo PDF theo quyển */
    'queue_q1' => env('PDF_QUEUE_Q1', 'pdf-q1'),
    'queue_q2' => env('PDF_QUEUE_Q2', 'pdf-q2'),

];
