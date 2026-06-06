<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết SIM {{ $sim->phone_number }} - Vương Kim Bảo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .five-element-Kim { background-color: #fef3c7; color: #92400e; }
        .five-element-Mộc { background-color: #d1fae5; color: #065f46; }
        .five-element-Thủy { background-color: #dbeafe; color: #1e3a8a; }
        .five-element-Hỏa { background-color: #fee2e2; color: #991b1b; }
        .five-element-Thổ { background-color: #fef3c7; color: #78350f; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-3xl font-bold text-gray-900">📱 Chi tiết SIM</h1>
                    <a href="{{ route('sims.index') }}" class="text-blue-600 hover:text-blue-800">← Quay lại danh sách</a>
                </div>
            </div>
        </header>

        <!-- Content -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <!-- Phone Number Header -->
                <div class="bg-gradient-to-r from-blue-500 to-purple-600 px-6 py-8 text-center">
                    <h2 class="text-4xl font-bold text-white mb-2">{{ $sim->phone_number }}</h2>
                    <p class="text-blue-100">Số điện thoại {{ ucfirst($sim->network_operator) }}</p>
                </div>

                <!-- Details -->
                <div class="px-6 py-6 space-y-6">
                    <!-- Status & Network -->
                    <div class="border-b border-gray-200 pb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Trạng thái</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600 mb-2">Nhà mạng</p>
                                <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full 
                                    {{ $sim->network_operator == 'vinaphone' ? 'bg-purple-100 text-purple-800' : '' }}
                                    {{ $sim->network_operator == 'mobifone' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $sim->network_operator == 'viettel' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ ucfirst($sim->network_operator) }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 mb-2">Trạng thái</p>
                                <span class="px-3 py-1 inline-flex text-sm font-semibold rounded-full 
                                    {{ $sim->status == 'available' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $sim->status == 'sold' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ $sim->status == 'available' ? 'Chưa bán' : 'Đã bán' }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-4">
                            <p class="text-sm text-gray-600 mb-2">Giá bán</p>
                            <p class="text-2xl font-bold text-green-600">
                                {{ $sim->selling_price ? number_format($sim->selling_price, 0, ',', '.') . ' đ' : 'Chưa có giá' }}
                            </p>
                        </div>
                    </div>

                    <!-- Feng Shui Information -->
                    <div class="border-b border-gray-200 pb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">🔮 Thông tin Phong Thủy</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Ngũ hành:</span>
                                <span class="px-3 py-1 text-sm font-semibold rounded-full five-element-{{ $sim->five_element }}">
                                    {{ $sim->five_element }}
                                </span>
                            </div>
                            
                            @if($sim->que64)
                            <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Quẻ:</span>
                                    <button data-que-id="{{ $sim->que64->id }}" class="view-que-detail font-semibold text-gray-900 hover:text-blue-600 underline cursor-pointer transition">
                                        {{ $sim->que64->name }}
                                    </button>
                                </div>
                                @if($sim->que64->chinese_name)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Tên Trung:</span>
                                    <span class="text-gray-700">{{ $sim->que64->chinese_name }}</span>
                                </div>
                                @endif
                            </div>
                            @endif

                            @if($sim->queBien)
                            <div class="bg-blue-50 rounded-lg p-4 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Quẻ Biến:</span>
                                    <button data-que-id="{{ $sim->queBien->id }}" class="view-que-detail font-semibold text-blue-900 hover:text-blue-600 underline cursor-pointer transition">
                                        {{ $sim->queBien->name }}
                                    </button>
                                </div>
                                @if($sim->queBien->chinese_name)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Tên Trung (Quẻ Biến):</span>
                                    <span class="text-blue-700">{{ $sim->queBien->chinese_name }}</span>
                                </div>
                                @endif
                            </div>
                            @endif

                            <div class="grid grid-cols-2 gap-4">
                                @if($sim->upper_trigram)
                                <div>
                                    <p class="text-sm text-gray-600">Quẻ Thượng</p>
                                    <p class="font-semibold text-gray-900">{{ $sim->upper_trigram_name ?? $sim->upper_trigram }}</p>
                                </div>
                                @endif
                                
                                @if($sim->lower_trigram)
                                <div>
                                    <p class="text-sm text-gray-600">Quẻ Hạ</p>
                                    <p class="font-semibold text-gray-900">{{ $sim->lower_trigram_name ?? $sim->lower_trigram }}</p>
                                </div>
                                @endif
                            </div>

                            @if($sim->moving_line)
                            <div>
                                <p class="text-sm text-gray-600">Động Hào</p>
                                <p class="font-semibold text-gray-900">{{ $sim->moving_line }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Metadata -->
                    <div class="text-sm text-gray-500 space-y-2">
                        <div class="flex justify-between">
                            <span>Ngày tạo:</span>
                            <span>{{ $sim->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Cập nhật:</span>
                            <span>{{ $sim->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-gray-50 px-6 py-4 flex gap-4">
                    <a href="{{ route('sims.index') }}" class="flex-1 text-center px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        ← Quay lại
                    </a>
                </div>
            </div>

            <!-- Phần chấm điểm SIM VKB -->
            <div class="bg-white rounded-lg shadow overflow-hidden mt-8">
                <div class="bg-gradient-to-r from-green-500 to-teal-600 px-6 py-4">
                    <h2 class="text-2xl font-bold text-white">
                        <i class="fas fa-star mr-2"></i>
                        CHẤM ĐIỂM SIM VKB
                    </h2>
                    <p class="text-green-100 text-sm mt-1">Nhập thông tin khách hàng để chấm điểm sim</p>
                </div>

                <div class="px-6 py-6">
                    <!-- Form nhập thông tin -->
                    <form id="diemSimVKBForm" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Ngày sinh -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-calendar-day mr-1"></i>
                                    Ngày sinh
                                </label>
                                <input type="number" name="d" min="1" max="31" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500"
                                    placeholder="Ngày (1-31)">
                            </div>

                            <!-- Tháng sinh -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-calendar-alt mr-1"></i>
                                    Tháng sinh
                                </label>
                                <input type="number" name="m" min="1" max="12" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500"
                                    placeholder="Tháng (1-12)">
                            </div>

                            <!-- Năm sinh -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-calendar mr-1"></i>
                                    Năm sinh
                                </label>
                                <input type="number" name="y" min="1940" max="2031" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500"
                                    placeholder="Năm (VD: 1990)">
                            </div>

                            <!-- Giờ sinh -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-clock mr-1"></i>
                                    Giờ sinh
                                </label>
                                <input type="number" name="h" min="0" max="23"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500"
                                    placeholder="Giờ (0-23, tùy chọn)">
                            </div>

                            <!-- Phút sinh -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-clock mr-1"></i>
                                    Phút sinh
                                </label>
                                <input type="number" name="minute" min="0" max="59"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500"
                                    placeholder="Phút (0-59, tùy chọn)">
                            </div>

                            <!-- Giới tính -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-venus-mars mr-1"></i>
                                    Giới tính
                                </label>
                                <select name="g" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                                    <option value="male">Nam</option>
                                    <option value="female">Nữ</option>
                                </select>
                            </div>
                        </div>

                        <!-- Nút tính điểm -->
                        <div class="text-center">
                            <button type="submit" id="btnTinhDiemVKB"
                                class="bg-gradient-to-r from-green-500 to-teal-600 text-white px-8 py-3 rounded-lg font-semibold hover:shadow-lg transition-all">
                                <i class="fas fa-calculator mr-2"></i>
                                Tính điểm sim
                            </button>
                        </div>
                    </form>

                    <!-- Kết quả chấm điểm -->
                    <div id="diemSimVKBResult" class="mt-6 hidden">
                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Kết quả chấm điểm</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Điểm số -->
                                <div class="text-center p-6 bg-gradient-to-br from-green-50 to-teal-50 rounded-xl">
                                    <i class="fas fa-award text-5xl text-green-600 mb-3"></i>
                                    <h4 class="text-lg font-semibold text-gray-700 mb-2">Điểm số</h4>
                                    <p id="diemSimVKBScore" class="text-4xl font-bold text-green-600">0</p>
                                </div>

                                <!-- Loại sim -->
                                <div class="text-center p-6 bg-gradient-to-br from-teal-50 to-blue-50 rounded-xl">
                                    <i class="fas fa-mobile-alt text-5xl text-teal-600 mb-3"></i>
                                    <h4 class="text-lg font-semibold text-gray-700 mb-2">Loại sim</h4>
                                    <p id="diemSimVKBLoai" class="text-2xl font-bold text-teal-600">-</p>
                                </div>
                            </div>

                            <!-- Nút xem chi tiết -->
                            <div class="text-center mt-6">
                                <button id="btnXemChiTietQueVKB" class="bg-gradient-to-r from-green-500 to-teal-600 text-white px-8 py-3 rounded-lg font-semibold hover:shadow-lg transition-all">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Xem chi tiết quẻ gốc
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal chi tiết quẻ -->
            <div id="modalQueDetail" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
                <div class="bg-white rounded-2xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                    <div class="sticky top-0 bg-gradient-to-r from-purple-600 to-blue-600 p-6 flex justify-between items-center">
                        <h3 class="text-2xl font-bold text-white">
                            <i class="fas fa-yin-yang mr-2"></i>
                            <span id="modalQueDetailTitle">Chi tiết quẻ</span>
                        </h3>
                        <button id="btnCloseModalQue" class="text-white hover:text-gray-200 text-2xl">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div id="modalQueDetailContent" class="p-6">
                        <div class="flex items-center justify-center py-8">
                            <i class="fas fa-spinner fa-spin text-4xl text-gray-400"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal chi tiết quẻ gốc VKB -->
            <div id="modalQueGocVKB" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
                <div class="bg-white rounded-2xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                    <div class="sticky top-0 bg-white border-b p-6 flex justify-between items-center">
                        <h3 class="text-2xl font-bold text-green-600">
                            <i class="fas fa-yin-yang mr-2"></i>
                            <span id="modalQueGocVKBTitle">Chi tiết quẻ gốc</span>
                        </h3>
                        <button id="btnCloseModalVKB" class="text-gray-500 hover:text-gray-700 text-2xl">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div id="modalQueGocVKBContent" class="p-6 prose max-w-none">
                        <!-- Nội dung sẽ được load ở đây -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            let queGocDataVKB = null;

            // Xử lý click vào tên quẻ để xem chi tiết
            $('.view-que-detail').on('click', function() {
                const queId = $(this).data('que-id');
                const queName = $(this).text();
                
                // Hiển thị modal với loading
                $('#modalQueDetailTitle').text(queName);
                $('#modalQueDetailContent').html(`
                    <div class="flex items-center justify-center py-8">
                        <i class="fas fa-spinner fa-spin text-4xl text-gray-400"></i>
                    </div>
                `);
                $('#modalQueDetail').removeClass('hidden').addClass('flex');
                
                // Gọi API lấy thông tin quẻ
                $.ajax({
                    url: '/api/que64/' + queId,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            const que = response.data;
                            let content = '<div class="space-y-6">';
                            
                            // Tên quẻ
                            content += `<div class="border-b pb-4">
                                <h4 class="text-2xl font-bold text-purple-600">${que.name}</h4>
                                ${que.chinese_name ? `<p class="text-gray-600 mt-1">${que.chinese_name}</p>` : ''}
                            </div>`;
                            
                            // Tổng quan
                            if (que.tong_quan) {
                                content += `<div class="bg-purple-50 rounded-lg p-4">
                                    <h5 class="text-lg font-semibold text-purple-700 mb-2">
                                        <i class="fas fa-info-circle mr-2"></i>Tổng quan
                                    </h5>
                                    <div class="text-gray-700 whitespace-pre-line">${que.tong_quan}</div>
                                </div>`;
                            }
                            
                            // Sự nghiệp
                            if (que.su_nghiep) {
                                content += `<div class="bg-blue-50 rounded-lg p-4">
                                    <h5 class="text-lg font-semibold text-blue-700 mb-3">
                                        <i class="fas fa-briefcase mr-2"></i>Sự nghiệp
                                    </h5>`;
                                if (que.su_nghiep.tich_cuc) {
                                    content += `<div class="mb-3">
                                        <h6 class="font-semibold text-green-600 mb-1">✅ Tích cực:</h6>
                                        <div class="text-gray-700 whitespace-pre-line">${que.su_nghiep.tich_cuc}</div>
                                    </div>`;
                                }
                                if (que.su_nghiep.tieu_cuc) {
                                    content += `<div>
                                        <h6 class="font-semibold text-red-600 mb-1">⚠️ Tiêu cực:</h6>
                                        <div class="text-gray-700 whitespace-pre-line">${que.su_nghiep.tieu_cuc}</div>
                                    </div>`;
                                }
                                content += `</div>`;
                            }
                            
                            // Tài chính
                            if (que.tai_chinh) {
                                content += `<div class="bg-yellow-50 rounded-lg p-4">
                                    <h5 class="text-lg font-semibold text-yellow-700 mb-3">
                                        <i class="fas fa-dollar-sign mr-2"></i>Tài chính
                                    </h5>`;
                                if (que.tai_chinh.tich_cuc) {
                                    content += `<div class="mb-3">
                                        <h6 class="font-semibold text-green-600 mb-1">✅ Tích cực:</h6>
                                        <div class="text-gray-700 whitespace-pre-line">${que.tai_chinh.tich_cuc}</div>
                                    </div>`;
                                }
                                if (que.tai_chinh.tieu_cuc) {
                                    content += `<div>
                                        <h6 class="font-semibold text-red-600 mb-1">⚠️ Tiêu cực:</h6>
                                        <div class="text-gray-700 whitespace-pre-line">${que.tai_chinh.tieu_cuc}</div>
                                    </div>`;
                                }
                                content += `</div>`;
                            }
                            
                            // Tình duyên
                            if (que.tinh_duyen) {
                                content += `<div class="bg-pink-50 rounded-lg p-4">
                                    <h5 class="text-lg font-semibold text-pink-700 mb-3">
                                        <i class="fas fa-heart mr-2"></i>Tình duyên
                                    </h5>`;
                                if (que.tinh_duyen.tich_cuc) {
                                    content += `<div class="mb-3">
                                        <h6 class="font-semibold text-green-600 mb-1">✅ Tích cực:</h6>
                                        <div class="text-gray-700 whitespace-pre-line">${que.tinh_duyen.tich_cuc}</div>
                                    </div>`;
                                }
                                if (que.tinh_duyen.tieu_cuc) {
                                    content += `<div>
                                        <h6 class="font-semibold text-red-600 mb-1">⚠️ Tiêu cực:</h6>
                                        <div class="text-gray-700 whitespace-pre-line">${que.tinh_duyen.tieu_cuc}</div>
                                    </div>`;
                                }
                                content += `</div>`;
                            }
                            
                            // Sức khỏe
                            if (que.suc_khoe) {
                                content += `<div class="bg-red-50 rounded-lg p-4">
                                    <h5 class="text-lg font-semibold text-red-700 mb-3">
                                        <i class="fas fa-heartbeat mr-2"></i>Sức khỏe
                                    </h5>`;
                                if (que.suc_khoe.tich_cuc) {
                                    content += `<div class="mb-3">
                                        <h6 class="font-semibold text-green-600 mb-1">✅ Tích cực:</h6>
                                        <div class="text-gray-700 whitespace-pre-line">${que.suc_khoe.tich_cuc}</div>
                                    </div>`;
                                }
                                if (que.suc_khoe.tieu_cuc) {
                                    content += `<div>
                                        <h6 class="font-semibold text-red-600 mb-1">⚠️ Tiêu cực:</h6>
                                        <div class="text-gray-700 whitespace-pre-line">${que.suc_khoe.tieu_cuc}</div>
                                    </div>`;
                                }
                                content += `</div>`;
                            }
                            
                            // Phát triển bản thân
                            if (que.phat_trien_ban_than) {
                                content += `<div class="bg-purple-50 rounded-lg p-4">
                                    <h5 class="text-lg font-semibold text-purple-700 mb-3">
                                        <i class="fas fa-user-graduate mr-2"></i>Phát triển bản thân
                                    </h5>`;
                                if (que.phat_trien_ban_than.tich_cuc) {
                                    content += `<div class="mb-3">
                                        <h6 class="font-semibold text-green-600 mb-1">✅ Tích cực:</h6>
                                        <div class="text-gray-700 whitespace-pre-line">${que.phat_trien_ban_than.tich_cuc}</div>
                                    </div>`;
                                }
                                if (que.phat_trien_ban_than.tieu_cuc) {
                                    content += `<div>
                                        <h6 class="font-semibold text-red-600 mb-1">⚠️ Tiêu cực:</h6>
                                        <div class="text-gray-700 whitespace-pre-line">${que.phat_trien_ban_than.tieu_cuc}</div>
                                    </div>`;
                                }
                                content += `</div>`;
                            }
                            
                            // Kết nối xã hội
                            if (que.ket_noi_xa_hoi) {
                                content += `<div class="bg-indigo-50 rounded-lg p-4">
                                    <h5 class="text-lg font-semibold text-indigo-700 mb-3">
                                        <i class="fas fa-users mr-2"></i>Kết nối xã hội
                                    </h5>`;
                                if (que.ket_noi_xa_hoi.tich_cuc) {
                                    content += `<div class="mb-3">
                                        <h6 class="font-semibold text-green-600 mb-1">✅ Tích cực:</h6>
                                        <div class="text-gray-700 whitespace-pre-line">${que.ket_noi_xa_hoi.tich_cuc}</div>
                                    </div>`;
                                }
                                if (que.ket_noi_xa_hoi.tieu_cuc) {
                                    content += `<div>
                                        <h6 class="font-semibold text-red-600 mb-1">⚠️ Tiêu cực:</h6>
                                        <div class="text-gray-700 whitespace-pre-line">${que.ket_noi_xa_hoi.tieu_cuc}</div>
                                    </div>`;
                                }
                                content += `</div>`;
                            }
                            
                            content += '</div>';
                            
                            $('#modalQueDetailContent').html(content);
                        } else {
                            $('#modalQueDetailContent').html(`
                                <div class="text-center py-8 text-red-600">
                                    <i class="fas fa-exclamation-circle text-4xl mb-4"></i>
                                    <p>Không thể tải thông tin quẻ</p>
                                </div>
                            `);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        $('#modalQueDetailContent').html(`
                            <div class="text-center py-8 text-red-600">
                                <i class="fas fa-exclamation-circle text-4xl mb-4"></i>
                                <p>Có lỗi xảy ra khi tải thông tin quẻ</p>
                            </div>
                        `);
                    }
                });
            });

            // Đóng modal chi tiết quẻ
            $('#btnCloseModalQue').on('click', function() {
                $('#modalQueDetail').addClass('hidden').removeClass('flex');
            });

            // Đóng modal khi click bên ngoài
            $('#modalQueDetail').on('click', function(e) {
                if (e.target === this) {
                    $(this).addClass('hidden').removeClass('flex');
                }
            });

            // Xử lý submit form chấm điểm
            $('#diemSimVKBForm').on('submit', function(e) {
                e.preventDefault();
                
                const $btn = $('#btnTinhDiemVKB');
                const originalText = $btn.html();
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Đang tính...');

                const formData = $(this).serializeArray();
                const params = {
                    sim_id: '{{ $sim->phone_number }}',
                    calc_sim: 1
                };
                
                $.each(formData, function(i, field) {
                    if (field.value) {
                        params[field.name] = field.value;
                    }
                });

                $.ajax({
                    url: '/api/sim/diem-vkb',
                    method: 'GET',
                    data: params,
                    success: function(result) {
                        if (result.success) {
                            // Hiển thị kết quả
                            $('#diemSimVKBScore').text(result.tong_diem);
                            $('#diemSimVKBLoai').text(result.type);
                            $('#diemSimVKBResult').removeClass('hidden');
                            
                            // Lưu dữ liệu quẻ gốc
                            queGocDataVKB = result.tong_quan_que_goc;
                        } else {
                            alert(result.message || 'Có lỗi xảy ra');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        alert('Có lỗi xảy ra khi tính điểm sim');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(originalText);
                    }
                });
            });

            // Xử lý nút xem chi tiết quẻ gốc
            $('#btnXemChiTietQueVKB').on('click', function() {
                if (queGocDataVKB) {
                    $('#modalQueGocVKBTitle').text(queGocDataVKB.name || 'Chi tiết quẻ gốc');
                
                let content = '<div class="space-y-6">';
                
                // Tên quẻ
                content += `<div class="border-b pb-4">
                    <h4 class="text-2xl font-bold text-green-600">${queGocDataVKB.name}</h4>
                </div>`;
                
                // Tổng quan
                if (queGocDataVKB.tong_quan) {
                    content += `<div class="bg-green-50 rounded-lg p-4">
                        <h5 class="text-lg font-semibold text-green-700 mb-2">
                            <i class="fas fa-info-circle mr-2"></i>Tổng quan
                        </h5>
                        <div class="text-gray-700 whitespace-pre-line">${queGocDataVKB.tong_quan}</div>
                    </div>`;
                }
                
                // Sự nghiệp
                if (queGocDataVKB.su_nghiep) {
                    content += `<div class="bg-blue-50 rounded-lg p-4">
                        <h5 class="text-lg font-semibold text-blue-700 mb-3">
                            <i class="fas fa-briefcase mr-2"></i>Sự nghiệp
                        </h5>`;
                    if (queGocDataVKB.su_nghiep.tich_cuc) {
                        content += `<div class="mb-3">
                            <h6 class="font-semibold text-green-600 mb-1">✅ Tích cực:</h6>
                            <div class="text-gray-700 whitespace-pre-line">${queGocDataVKB.su_nghiep.tich_cuc}</div>
                        </div>`;
                    }
                    if (queGocDataVKB.su_nghiep.tieu_cuc) {
                        content += `<div>
                            <h6 class="font-semibold text-red-600 mb-1">⚠️ Tiêu cực:</h6>
                            <div class="text-gray-700 whitespace-pre-line">${queGocDataVKB.su_nghiep.tieu_cuc}</div>
                        </div>`;
                    }
                    content += `</div>`;
                }
                
                // Tài chính
                if (queGocDataVKB.tai_chinh) {
                    content += `<div class="bg-yellow-50 rounded-lg p-4">
                        <h5 class="text-lg font-semibold text-yellow-700 mb-3">
                            <i class="fas fa-dollar-sign mr-2"></i>Tài chính
                        </h5>`;
                    if (queGocDataVKB.tai_chinh.tich_cuc) {
                        content += `<div class="mb-3">
                            <h6 class="font-semibold text-green-600 mb-1">✅ Tích cực:</h6>
                            <div class="text-gray-700 whitespace-pre-line">${queGocDataVKB.tai_chinh.tich_cuc}</div>
                        </div>`;
                    }
                    if (queGocDataVKB.tai_chinh.tieu_cuc) {
                        content += `<div>
                            <h6 class="font-semibold text-red-600 mb-1">⚠️ Tiêu cực:</h6>
                            <div class="text-gray-700 whitespace-pre-line">${queGocDataVKB.tai_chinh.tieu_cuc}</div>
                        </div>`;
                    }
                    content += `</div>`;
                }
                
                // Tình duyên
                if (queGocDataVKB.tinh_duyen) {
                    content += `<div class="bg-pink-50 rounded-lg p-4">
                        <h5 class="text-lg font-semibold text-pink-700 mb-3">
                            <i class="fas fa-heart mr-2"></i>Tình duyên
                        </h5>`;
                    if (queGocDataVKB.tinh_duyen.tich_cuc) {
                        content += `<div class="mb-3">
                            <h6 class="font-semibold text-green-600 mb-1">✅ Tích cực:</h6>
                            <div class="text-gray-700 whitespace-pre-line">${queGocDataVKB.tinh_duyen.tich_cuc}</div>
                        </div>`;
                    }
                    if (queGocDataVKB.tinh_duyen.tieu_cuc) {
                        content += `<div>
                            <h6 class="font-semibold text-red-600 mb-1">⚠️ Tiêu cực:</h6>
                            <div class="text-gray-700 whitespace-pre-line">${queGocDataVKB.tinh_duyen.tieu_cuc}</div>
                        </div>`;
                    }
                    content += `</div>`;
                }
                
                // Sức khỏe
                if (queGocDataVKB.suc_khoe) {
                    content += `<div class="bg-red-50 rounded-lg p-4">
                        <h5 class="text-lg font-semibold text-red-700 mb-3">
                            <i class="fas fa-heartbeat mr-2"></i>Sức khỏe
                        </h5>`;
                    if (queGocDataVKB.suc_khoe.tich_cuc) {
                        content += `<div class="mb-3">
                            <h6 class="font-semibold text-green-600 mb-1">✅ Tích cực:</h6>
                            <div class="text-gray-700 whitespace-pre-line">${queGocDataVKB.suc_khoe.tich_cuc}</div>
                        </div>`;
                    }
                    if (queGocDataVKB.suc_khoe.tieu_cuc) {
                        content += `<div>
                            <h6 class="font-semibold text-red-600 mb-1">⚠️ Tiêu cực:</h6>
                            <div class="text-gray-700 whitespace-pre-line">${queGocDataVKB.suc_khoe.tieu_cuc}</div>
                        </div>`;
                    }
                    content += `</div>`;
                }
                
                // Phát triển bản thân
                if (queGocDataVKB.phat_trien_ban_than) {
                    content += `<div class="bg-purple-50 rounded-lg p-4">
                        <h5 class="text-lg font-semibold text-purple-700 mb-3">
                            <i class="fas fa-user-graduate mr-2"></i>Phát triển bản thân
                        </h5>`;
                    if (queGocDataVKB.phat_trien_ban_than.tich_cuc) {
                        content += `<div class="mb-3">
                            <h6 class="font-semibold text-green-600 mb-1">✅ Tích cực:</h6>
                            <div class="text-gray-700 whitespace-pre-line">${queGocDataVKB.phat_trien_ban_than.tich_cuc}</div>
                        </div>`;
                    }
                    if (queGocDataVKB.phat_trien_ban_than.tieu_cuc) {
                        content += `<div>
                            <h6 class="font-semibold text-red-600 mb-1">⚠️ Tiêu cực:</h6>
                            <div class="text-gray-700 whitespace-pre-line">${queGocDataVKB.phat_trien_ban_than.tieu_cuc}</div>
                        </div>`;
                    }
                    content += `</div>`;
                }
                
                // Kết nối xã hội
                if (queGocDataVKB.ket_noi_xa_hoi) {
                    content += `<div class="bg-indigo-50 rounded-lg p-4">
                        <h5 class="text-lg font-semibold text-indigo-700 mb-3">
                            <i class="fas fa-users mr-2"></i>Kết nối xã hội
                        </h5>`;
                    if (queGocDataVKB.ket_noi_xa_hoi.tich_cuc) {
                        content += `<div class="mb-3">
                            <h6 class="font-semibold text-green-600 mb-1">✅ Tích cực:</h6>
                            <div class="text-gray-700 whitespace-pre-line">${queGocDataVKB.ket_noi_xa_hoi.tich_cuc}</div>
                        </div>`;
                    }
                    if (queGocDataVKB.ket_noi_xa_hoi.tieu_cuc) {
                        content += `<div>
                            <h6 class="font-semibold text-red-600 mb-1">⚠️ Tiêu cực:</h6>
                            <div class="text-gray-700 whitespace-pre-line">${queGocDataVKB.ket_noi_xa_hoi.tieu_cuc}</div>
                        </div>`;
                    }
                    content += `</div>`;
                }
                
                content += '</div>';
                
                $('#modalQueGocVKBContent').html(content);
                $('#modalQueGocVKB').removeClass('hidden').addClass('flex');
                }
            });

            // Đóng modal
            $('#btnCloseModalVKB').on('click', function() {
                $('#modalQueGocVKB').addClass('hidden').removeClass('flex');
            });

            // Đóng modal khi click bên ngoài
            $('#modalQueGocVKB').on('click', function(e) {
                if (e.target === this) {
                    $(this).addClass('hidden').removeClass('flex');
                }
            });
        });
    </script>
</body>
</html>
