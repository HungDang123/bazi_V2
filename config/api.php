<?php

return [

    /** Giới hạn thời gian xử lý mỗi request API (giây). */
    'max_execution_seconds' => (int) env('API_MAX_EXECUTION_SECONDS', 30),

    /** Thời gian chờ tối đa phía trình duyệt khi gọi /api/* (millisecond). */
    'client_timeout_ms' => (int) env('API_CLIENT_TIMEOUT_MS', 30000),

];
