<?php

namespace App\Console\Commands;

use App\Models\Phan5KhiaCanh;
use App\Models\Phan5ThapThanHinhAnh;
use App\Models\Phan5Trang;
use App\Services\Phan5AssetService;
use Illuminate\Console\Command;

class ImportPhan5KhiaCanh extends Command
{
    protected $signature = 'import:phan5-khia-canh {--fresh : Xóa dữ liệu cũ trước khi import}';

    protected $description = 'Import layout Phần 5 (bìa, trang I, khía cạnh, ảnh Thập Thần)';

    /** @var array<int, array{slug: string, title: string, image: string, sort_order: int}> */
    protected array $trangRows = [
        [
            'slug' => 'bia',
            'title' => 'Bìa Phần 5',
            'image' => 'resources/views/pdfs/phan-5/bia-phan-5.png',
            'sort_order' => 1,
        ],
        [
            'slug' => 'tong_quan',
            'title' => 'I. Tổng quan',
            'image' => 'resources/views/pdfs/phan-5/tong-quan-bg.png',
            'sort_order' => 2,
        ],
        [
            'slug' => 'su_nghiep',
            'title' => 'II. Sự nghiệp',
            'image' => 'resources/views/pdfs/phan-5/su-nghiep-bg.png',
            'sort_order' => 3,
        ],
        [
            'slug' => 'su_nghiep_item',
            'title' => 'Nền trang mục Thập Thần',
            'image' => 'resources/views/pdfs/phan-5/su-nghiep-item-bg.png',
            'sort_order' => 4,
        ],
        [
            'slug' => 'anh_tu_khoa',
            'title' => 'Khung từ khóa cốt lõi',
            'image' => 'resources/views/pdfs/phan-5/anh-tu-khoa-frame.png',
            'sort_order' => 5,
        ],
        [
            'slug' => 'page_content',
            'title' => 'Nền trang mục III trở đi (LBTV-119)',
            'image' => 'resources/views/pdfs/phan-5/page-content-bg.png',
            'sort_order' => 6,
        ],
    ];

    /** @var array<int, array{slug: string, section_code: string, title: string, tong_quan: string, image_vi_tri: string, sort_order: int}> */
    protected array $khiaCanhRows = [
        [
            'slug' => 'su_nghiep',
            'section_code' => 'II',
            'title' => 'II. SỰ NGHIỆP',
            'tong_quan' => 'Năng lượng về tính kỷ luật, định hướng và khả năng thực thi của bạn được lưu trữ tại khu vực mặt tiền của La Bàn Thịnh Vượng. Khu vực này tương ứng với vị trí Thiên Can của Trụ Tháng và Thiên Can của Trụ Năm. Đây là bức tranh phản ánh rõ nét nhất cách bạn tương tác với công việc, cách bạn khẳng định uy tín cá nhân cũng như lộ trình thăng tiến của bạn trước cộng đồng và xã hội.',
            'image_vi_tri' => 'resources/views/pdfs/phan-5/vi-tri/su-nghiep.png',
            'sort_order' => 1,
        ],
        [
            'slug' => 'tai_chinh',
            'section_code' => 'III',
            'title' => 'III. TÀI CHÍNH',
            'tong_quan' => 'Khả năng hấp thụ và sự vun vén các giá trị vật chất của bạn được lưu giữ tại khu vực nền tảng của La Bàn Thịnh Vượng. Khu vực này tương ứng với vị trí Tàng Can nằm trong Địa Chi của Trụ Tháng cùng Trụ Giờ. Khác với sự nghiệp là vẻ bề ngoài tỏa sáng, khía cạnh tài chính cần sự ổn định từ mạch ngầm bên trong gốc rễ. Đây là bức tranh phản ánh rõ nét nhất năng lực quản trị nguồn lực, cách bạn biến các cơ hội thành tài sản thực tế và xây dựng sự thịnh vượng lâu dài cho cuộc sống của mình.',
            'image_vi_tri' => 'resources/views/pdfs/phan-5/vi-tri/tai-chinh.png',
            'sort_order' => 2,
        ],
        [
            'slug' => 'tinh_duyen',
            'section_code' => 'IV',
            'title' => 'IV. TÌNH DUYÊN',
            'tong_quan' => 'Năng lượng về sự thấu hiểu cùng sự hòa hợp, gắn kết cá nhân được chứa đựng tại nền tảng Tàng Can nằm trong Địa Chi của Trụ Ngày. Đây là vị trí quan trọng nhất để xem xét chất lượng các mối quan hệ thân cận nhất, giúp bạn nhìn rõ cách kết nối và nuôi dưỡng tình yêu thương nhằm kiến tạo nên một mái ấm bền vững và hòa hợp.',
            'image_vi_tri' => 'resources/views/pdfs/phan-5/vi-tri/tinh-duyen.png',
            'sort_order' => 3,
        ],
        [
            'slug' => 'phat_trien_ban_than',
            'section_code' => 'VI',
            'title' => 'VI. PHÁT TRIỂN BẢN THÂN',
            'tong_quan' => 'Những khát khao, hoài bão, sức sáng tạo và kết quả dài hạn của bạn nằm tại khu vực tiềm năng của La Bàn Thịnh Vượng. Khu vực này tương ứng với vị trí Thiên Can và Tàng Can nằm trong Địa Chi của Trụ Giờ. Đây là bức tranh phản ánh rõ nét nhất những tư tưởng đổi mới, khả năng tự trau dồi và những giá trị tinh thần sâu sắc mà bạn mong muốn xây dựng để để lại cho các thế hệ mai sau.',
            'image_vi_tri' => 'resources/views/pdfs/phan-5/vi-tri/phat-trien-ban-than.png',
            'sort_order' => 4,
        ],
        [
            'slug' => 'ket_noi_xa_hoi',
            'section_code' => 'VII',
            'title' => 'VII. KẾT NỐI XÃ HỘI',
            'tong_quan' => 'Môi trường bạn bè và mạng lưới cộng đồng xung quanh được xem xét tại khu vực gốc rễ xã hội của La Bàn Thịnh Vượng. Khu vực này tương ứng với vị trí Thiên Can và Tàng Can nằm trong Địa Chi của Trụ Năm. Đây là bức tranh phản ánh rõ nét nhất xu hướng bạn cộng tác, xây dựng tầm ảnh hưởng xã hội và đón nhận sự hỗ trợ từ những người có cùng hệ giá trị hoặc tần số năng lượng với bản thân.',
            'image_vi_tri' => 'resources/views/pdfs/phan-5/vi-tri/ket-noi-xa-hoi.png',
            'sort_order' => 5,
        ],
    ];

    /** @var array<string, string> */
    protected array $thapThanImages = [
        'Tỷ Kiên' => 'resources/views/pdfs/phan-5/thap-than/ty-kien.png',
        'Kiếp Tài' => 'resources/views/pdfs/phan-5/thap-than/kiep-tai.png',
        'Thương Quan' => 'resources/views/pdfs/phan-5/thap-than/thuong-quan.png',
        'Thực Thần' => 'resources/views/pdfs/phan-5/thap-than/thuc-than.png',
        'Chính Tài' => 'resources/views/pdfs/phan-5/thap-than/chinh-tai.png',
        'Thiên Tài' => 'resources/views/pdfs/phan-5/thap-than/thien-tai.png',
        'Chính Quan' => 'resources/views/pdfs/phan-5/thap-than/chinh-quan.png',
        'Thất Sát' => 'resources/views/pdfs/phan-5/thap-than/that-sat.png',
        'Chính Ấn' => 'resources/views/pdfs/phan-5/thap-than/chinh-an.png',
        'Thiên Ấn' => 'resources/views/pdfs/phan-5/thap-than/thien-an.png',
    ];

    public function handle(): int
    {
        if ($this->option('fresh')) {
            Phan5KhiaCanh::truncate();
            Phan5ThapThanHinhAnh::truncate();
            Phan5Trang::truncate();
        }

        foreach ($this->khiaCanhRows as $row) {
            Phan5KhiaCanh::updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'section_code' => $row['section_code'],
                    'title' => $row['title'],
                    'tong_quan' => $row['tong_quan'],
                    'image_vi_tri' => $row['image_vi_tri'],
                    'sort_order' => $row['sort_order'],
                ]
            );
        }

        foreach ($this->thapThanImages as $thapThan => $image) {
            Phan5ThapThanHinhAnh::updateOrCreate(
                ['thap_than' => $thapThan],
                ['image' => $image]
            );
        }

        foreach ($this->trangRows as $row) {
            Phan5Trang::updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'title' => $row['title'],
                    'image' => $row['image'],
                    'sort_order' => $row['sort_order'],
                ]
            );
        }

        $assetPaths = array_merge(
            array_column($this->trangRows, 'image'),
            array_column($this->khiaCanhRows, 'image_vi_tri'),
            array_values($this->thapThanImages)
        );
        $check = Phan5AssetService::verifyAndSync($assetPaths);

        $this->info('Import thành công '.count($this->trangRows).' trang, '
            .count($this->khiaCanhRows).' khía cạnh và '.count($this->thapThanImages).' ảnh Thập Thần.');

        if ($check['synced'] !== []) {
            $this->info('Đã đồng bộ '.count($check['synced']).' ảnh vào public/images/pdfs/phan-5/.');
        }

        if ($check['missing'] !== []) {
            $this->warn('Thiếu '.count($check['missing']).' ảnh trong dự án (cần lưu vào resources/views/pdfs/phan-5/):');
            foreach ($check['missing'] as $missing) {
                $this->line('  - '.$missing);
            }
        }

        return 0;
    }
}
