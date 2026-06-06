<?php

return [

    /** Cache kết quả BaZiServiceV2::calc theo ngày giờ sinh (giảm tính lại khi gọi nhiều API song song). */
    'calc_cache_enabled' => (bool) env('BAZI_CALC_CACHE', true),

    /** TTL cache calc (giây). Mặc định 6 giờ. */
    'calc_cache_ttl_seconds' => (int) env('BAZI_CALC_CACHE_TTL', 21600),

];
