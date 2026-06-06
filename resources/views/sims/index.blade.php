<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách SIM - Vương Kim Bảo</title>
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
                    <h1 class="text-3xl font-bold text-gray-900">📱 Danh Sách SIM</h1>
                    <div class="flex gap-4">
                        <a href="{{ route('index') }}" class="text-gray-600 hover:text-gray-900">← Trang chủ</a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-red-600 hover:text-red-800">Đăng xuất</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Statistics Bar -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Thông báo lỗi -->
            @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Lỗi!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
            @endif

            <!-- Thông báo thành công chấm điểm -->
            @if(request('score_d') && request('score_m') && request('score_y') && request('score_h') !== null && request('score_minute') !== null && request('score_g'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">
                    <i class="fas fa-check-circle mr-2"></i>Đã chấm điểm!
                </strong>
                <span class="block sm:inline">Danh sách sim đã được sắp xếp theo điểm VKB từ cao xuống thấp.</span>
            </div>
            @endif

            {{-- <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-sm text-gray-600">Tổng SIM</p>
                    <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-sm text-gray-600">Vinaphone</p>
                    <p class="text-2xl font-bold text-purple-600">{{ number_format($stats['vinaphone']) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-sm text-gray-600">Mobifone</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($stats['mobifone']) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-sm text-gray-600">Viettel</p>
                    <p class="text-2xl font-bold text-red-600">{{ number_format($stats['viettel']) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-sm text-gray-600">Chưa bán</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($stats['available']) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <p class="text-sm text-gray-600">Đã bán</p>
                    <p class="text-2xl font-bold text-red-600">{{ number_format($stats['sold']) }}</p>
                </div>
            </div>

            <!-- Five Elements Stats -->
            <div class="mt-4 grid grid-cols-5 gap-4">
                @foreach($fiveElementsCount as $element => $count)
                <div class="bg-white rounded-lg shadow p-4 five-element-{{ $element }}">
                    <p class="text-sm font-medium">{{ $element }}</p>
                    <p class="text-xl font-bold">{{ number_format($count) }}</p>
                </div>
                @endforeach
            </div> --}}
        </div>

        <!-- Filters -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <!-- Form chấm điểm SIM VKB -->
            <div class="bg-white rounded-lg shadow overflow-hidden mb-4">
                <div class="bg-gradient-to-r from-green-500 to-teal-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white">
                        <i class="fas fa-star mr-2"></i>
                        CHẤM ĐIỂM VÀ SẮP XẾP SIM VKB
                    </h2>
                    <p class="text-green-100 text-sm mt-1">Nhập đủ thông tin để chấm điểm và sắp xếp sim theo điểm số</p>
                </div>
                <div class="px-6 py-6">
                    <form id="scoringForm" method="GET" action="{{ route('sims.index') }}" class="space-y-4">
                        <!-- Giữ lại các filter hiện tại -->
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <input type="hidden" name="network_operator" value="{{ request('network_operator') }}">
                        <input type="hidden" name="status" value="{{ request('status') }}">
                        <input type="hidden" name="five_element" value="{{ request('five_element') }}">
                        <input type="hidden" name="que_id" value="{{ request('que_id') }}">
                        <input type="hidden" name="min_price" value="{{ request('min_price') }}">
                        <input type="hidden" name="max_price" value="{{ request('max_price') }}">

                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                            <!-- Ngày sinh -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-calendar-day mr-1"></i>
                                    Ngày sinh <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="score_d" min="1" max="31" value="{{ request('score_d') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500"
                                    placeholder="1-31">
                            </div>

                            <!-- Tháng sinh -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-calendar-alt mr-1"></i>
                                    Tháng sinh <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="score_m" min="1" max="12" value="{{ request('score_m') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500"
                                    placeholder="1-12">
                            </div>

                            <!-- Năm sinh -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-calendar mr-1"></i>
                                    Năm sinh <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="score_y" min="1940" max="2031" value="{{ request('score_y') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500"
                                    placeholder="VD: 1990">
                            </div>

                            <!-- Giờ sinh -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-clock mr-1"></i>
                                    Giờ sinh <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="score_h" min="0" max="23" value="{{ request('score_h') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500"
                                    placeholder="0-23">
                            </div>

                            <!-- Phút sinh -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-clock mr-1"></i>
                                    Phút sinh <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="score_minute" min="0" max="59" value="{{ request('score_minute') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500"
                                    placeholder="0-59">
                            </div>

                            <!-- Giới tính -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    <i class="fas fa-venus-mars mr-1"></i>
                                    Giới tính <span class="text-red-500">*</span>
                                </label>
                                <select name="score_g"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                                    <option value="male" {{ request('score_g') == 'male' ? 'selected' : '' }}>Nam</option>
                                    <option value="female" {{ request('score_g') == 'female' ? 'selected' : '' }}>Nữ</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <button type="submit" class="px-6 py-2 bg-gradient-to-r from-green-500 to-teal-600 text-white rounded-md hover:shadow-lg transition-all">
                                <i class="fas fa-calculator mr-2"></i>
                                Chấm điểm và sắp xếp
                            </button>
                            <a href="{{ route('sims.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                                <i class="fas fa-times mr-2"></i>
                                Bỏ chấm điểm
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-filter mr-2"></i>Bộ lọc tìm kiếm
                </h3>
                <form method="GET" action="{{ route('sims.index') }}" class="space-y-4">
                    <!-- Giữ lại các tham số chấm điểm nếu có -->
                    @if(request('score_d'))
                        <input type="hidden" name="score_d" value="{{ request('score_d') }}">
                        <input type="hidden" name="score_m" value="{{ request('score_m') }}">
                        <input type="hidden" name="score_y" value="{{ request('score_y') }}">
                        <input type="hidden" name="score_h" value="{{ request('score_h') }}">
                        <input type="hidden" name="score_minute" value="{{ request('score_minute') }}">
                        <input type="hidden" name="score_g" value="{{ request('score_g') }}">
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Search -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tìm số SIM</label>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Nhập số SIM..." 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Network Operator -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nhà mạng</label>
                            <select name="network_operator" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Tất cả</option>
                                <option value="vinaphone" {{ request('network_operator') == 'vinaphone' ? 'selected' : '' }}>Vinaphone</option>
                                <option value="mobifone" {{ request('network_operator') == 'mobifone' ? 'selected' : '' }}>Mobifone</option>
                                <option value="viettel" {{ request('network_operator') == 'viettel' ? 'selected' : '' }}>Viettel</option>
                            </select>
                        </div>

                        <!-- Five Element -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ngũ hành</label>
                            <select name="five_element" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Tất cả</option>
                                <option value="Kim" {{ request('five_element') == 'Kim' ? 'selected' : '' }}>Kim</option>
                                <option value="Mộc" {{ request('five_element') == 'Mộc' ? 'selected' : '' }}>Mộc</option>
                                <option value="Thủy" {{ request('five_element') == 'Thủy' ? 'selected' : '' }}>Thủy</option>
                                <option value="Hỏa" {{ request('five_element') == 'Hỏa' ? 'selected' : '' }}>Hỏa</option>
                                <option value="Thổ" {{ request('five_element') == 'Thổ' ? 'selected' : '' }}>Thổ</option>
                            </select>
                        </div>

                        <!-- Que -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quẻ</label>
                            <select name="que_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Tất cả</option>
                                @foreach($ques as $que)
                                <option value="{{ $que->id }}" {{ request('que_id') == $que->id ? 'selected' : '' }}>
                                    {{ $que->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Min Price -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Giá bán từ</label>
                            <input type="number" name="min_price" value="{{ request('min_price') }}" 
                                   placeholder="0" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Max Price -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Giá bán đến</label>
                            <input type="number" name="max_price" value="{{ request('max_price') }}" 
                                   placeholder="999999999" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Sort -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sắp xếp theo</label>
                            <select name="sort_by" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Ngày tạo</option>
                                <option value="phone_number" {{ request('sort_by') == 'phone_number' ? 'selected' : '' }}>Số SIM</option>
                                <option value="selling_price" {{ request('sort_by') == 'selling_price' ? 'selected' : '' }}>Giá bán</option>
                                <option value="network_operator" {{ request('sort_by') == 'network_operator' ? 'selected' : '' }}>Nhà mạng</option>
                                <option value="status" {{ request('sort_by') == 'status' ? 'selected' : '' }}>Trạng thái</option>
                                <option value="five_element" {{ request('sort_by') == 'five_element' ? 'selected' : '' }}>Ngũ hành</option>
                            </select>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-4">
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            🔍 Lọc
                        </button>
                        <a href="{{ route('sims.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                            🔄 Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">
                        Kết quả: {{ number_format($sims->total()) }} SIM
                    </h2>
                </div>

                @if($sims->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số SIM</th>
                                @if(request('score_d') && request('score_m') && request('score_y') && request('score_h') !== null && request('score_minute') !== null && request('score_g'))
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <i class="fas fa-star text-yellow-500 mr-1"></i>Điểm VKB
                                </th>
                                @endif
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nhà mạng</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quẻ</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quẻ Biến</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngũ hành</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giá bán</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($sims as $sim)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $sim->phone_number }}</div>
                                </td>
                                @if(request('score_d') && request('score_m') && request('score_y') && request('score_h') !== null && request('score_minute') !== null && request('score_g'))
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if(isset($sim->vkb_score))
                                    <div class="flex items-center">
                                        <span class="px-3 py-1 text-sm font-bold rounded-full 
                                            {{ $sim->vkb_score >= 9 ? 'bg-purple-100 text-purple-800' : '' }}
                                            {{ $sim->vkb_score >= 8 && $sim->vkb_score < 9 ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $sim->vkb_score >= 7 && $sim->vkb_score < 8 ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $sim->vkb_score >= 5 && $sim->vkb_score < 7 ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $sim->vkb_score >= 3 && $sim->vkb_score < 5 ? 'bg-orange-100 text-orange-800' : '' }}
                                            {{ $sim->vkb_score < 3 ? 'bg-red-100 text-red-800' : '' }}">
                                            {{ number_format($sim->vkb_score, 2) }}
                                        </span>
                                        <span class="ml-2 text-xs text-gray-500">{{ $sim->vkb_type ?? '' }}</span>
                                    </div>
                                    @else
                                    <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>
                                @endif
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $sim->network_operator == 'vinaphone' ? 'bg-purple-100 text-purple-800' : '' }}
                                        {{ $sim->network_operator == 'mobifone' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $sim->network_operator == 'viettel' ? 'bg-red-100 text-red-800' : '' }}">
                                        {{ ucfirst($sim->network_operator) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $sim->que64->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $sim->queBien->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full five-element-{{ $sim->five_element }}">
                                        {{ $sim->five_element }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $sim->selling_price ? number_format($sim->selling_price, 0, ',', '.') . ' đ' : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $sim->status == 'available' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $sim->status == 'sold' ? 'bg-red-100 text-red-800' : '' }}">
                                        {{ $sim->status == 'available' ? 'Chưa bán' : 'Đã bán' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('sims.show', $sim->id) }}" class="text-blue-600 hover:text-blue-900">Chi tiết</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $sims->links() }}
                </div>
                @else
                <div class="px-6 py-12 text-center">
                    <p class="text-gray-500">Không tìm thấy SIM nào phù hợp với bộ lọc</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Validation form chấm điểm
            $('#scoringForm').on('submit', function(e) {
                const scoreD = $('input[name="score_d"]').val();
                const scoreM = $('input[name="score_m"]').val();
                const scoreY = $('input[name="score_y"]').val();
                const scoreH = $('input[name="score_h"]').val();
                const scoreMinute = $('input[name="score_minute"]').val();
                const scoreG = $('select[name="score_g"]').val();

                // Kiểm tra xem có ít nhất 1 trường được điền
                const hasAnyField = scoreD || scoreM || scoreY || scoreH || scoreMinute;

                if (hasAnyField) {
                    // Nếu có ít nhất 1 trường được điền, kiểm tra tất cả các trường
                    if (!scoreD || !scoreM || !scoreY || scoreH === '' || scoreMinute === '' || !scoreG) {
                        e.preventDefault();
                        alert('Vui lòng nhập đầy đủ thông tin (ngày, tháng, năm, giờ, phút, giới tính) để chấm điểm sim!');
                        return false;
                    }

                    // Validate giá trị
                    if (scoreD < 1 || scoreD > 31) {
                        e.preventDefault();
                        alert('Ngày sinh phải từ 1 đến 31!');
                        return false;
                    }

                    if (scoreM < 1 || scoreM > 12) {
                        e.preventDefault();
                        alert('Tháng sinh phải từ 1 đến 12!');
                        return false;
                    }

                    if (scoreY < 1940 || scoreY > 2031) {
                        e.preventDefault();
                        alert('Năm sinh phải từ 1940 đến 2031!');
                        return false;
                    }

                    if (scoreH < 0 || scoreH > 23) {
                        e.preventDefault();
                        alert('Giờ sinh phải từ 0 đến 23!');
                        return false;
                    }

                    if (scoreMinute < 0 || scoreMinute > 59) {
                        e.preventDefault();
                        alert('Phút sinh phải từ 0 đến 59!');
                        return false;
                    }
                }

                return true;
            });
        });
    </script>
