<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lá Số Tứ Trụ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js">
    </script>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <style>
        @font-face {
            font-family: 'utm-davida';
            src: url('{{ asset('fonts/UTM-Davida.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: block;
        }
        @font-face {
            font-family: 'utm-davida';
            src: url('{{ asset('fonts/UTM-Davida.ttf') }}') format('truetype');
            font-weight: bold;
            font-style: normal;
            font-display: block;
        }
        @font-face {
            font-family: 'UTM Davida';
            src: url('{{ asset('fonts/UTM-Davida.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: block;
        }
        @font-face {
            font-family: 'UTM Davida';
            src: url('{{ asset('fonts/UTM-Davida.ttf') }}') format('truetype');
            font-weight: bold;
            font-style: normal;
            font-display: block;
        }

        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #dd1212 0%, #970303 100%);
            min-height: 100vh;
        }

        .form-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .form-title {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }

        .input-field {
            transition: all 0.3s ease;
            border: 2px solid #e5e7eb;
        }

        .input-field:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .loading {
            position: relative;
        }

        .loading:after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            margin-top: -10px;
            margin-left: -10px;
            width: 20px;
            height: 20px;
            border: 2px solid #ffffff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Style cho bảng kết quả */
        .result-table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
            min-width: 600px;
        }

        .result-table,
        .result-table th,
        .result-table td {
            border: 1px solid black;
        }

        .result-table th,
        .result-table td {
            padding: 8px;
            text-align: left;
        }

        .result-table th {
            background-color: #f8fafc;
            font-weight: 600;
        }

        /* Wrapper cho table scroll trên mobile */
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            position: relative;
        }

        /* Styles for Chất Lượng Thập Thần Table */
        .chat-luong-table {
            font-family: Arial, sans-serif;
            border-collapse: collapse;
        }

        .chat-luong-table thead th {
            background-color: #6E0101;
            color: #E5CA8E;
            border: 1px solid #ffffff;
            font-weight: bold;
            text-align: center;
        }

        .chat-luong-table tbody tr {
            background-color: #EBE7E0;
        }

        .chat-luong-table tbody td {
            padding: 6px 10px;
            border: 1px solid #ffffff;
            font-size: 13px;
            background-color: #EBE7E0;
        }

        .chat-luong-table tbody td:first-child {
            font-weight: bold;
            color: #E5CA8E;
            background-color: #6E0101;
            text-align: center;
        }

        .chat-luong-table .bar-container {
            display: flex;
            align-items: center;
            width: 100%;
            position: relative;
            height: 24px;
            background-color: #D5D5D5;
            border-radius: 9999px;
            overflow: hidden;
        }

        .chat-luong-table .bar {
            height: 24px;
            position: absolute;
            left: 0;
            top: 0;
            transition: width 0.3s ease;
            z-index: 1;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-weight: bold;
            font-size: 12px;
        }

        .chat-luong-table .bar.natal {
            background-color: #4169E1;
        }

        .chat-luong-table .bar.annual {
            background-color: #6E0101;
        }

        .chat-luong-table .bar-zero-label {
            position: absolute;
            left: 0;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            color: #000000;
            font-weight: bold;
            font-size: 12px;
            text-align: center;
            z-index: 10;
        }

        /* Scroll indicator cho bảng */
        .table-wrapper::after {
            content: '← Vuốt để xem thêm →';
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(79, 70, 229, 0.9);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            display: none;
            white-space: nowrap;
            z-index: 10;
            pointer-events: none;
            animation: fadeInOut 3s ease-in-out infinite;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
            .table-wrapper::after {
                display: block;
            }

            .table-wrapper {
                margin-bottom: 15px;
                padding-bottom: 35px;
            }
        }

        @keyframes fadeInOut {

            0%,
            100% {
                opacity: 0;
                transform: translateX(-50%) translateY(0);
            }

            20%,
            80% {
                opacity: 1;
                transform: translateX(-50%) translateY(-2px);
            }
        }

        /* Ẩn scroll indicator sau khi scroll */
        .table-wrapper.scrolled::after {
            display: none;
        }

        /* Responsive cho mobile */
        @media (max-width: 768px) {
            .result-table {
                font-size: 12px;
                min-width: 500px;
            }

            .result-table th,
            .result-table td {
                padding: 6px 4px;
            }

            .result-table p {
                margin: 2px 0;
                font-size: 11px;
            }

            .result-table .header-cs-0 {
                font-size: 14px;
                font-weight: bold;
            }

            .result-table .ten-gods {
                font-size: 10px;
            }

            .result-container {
                padding: 15px 10px;
            }
        }

        @media (max-width: 480px) {
            .result-table {
                font-size: 11px;
                min-width: 450px;
            }

            .result-table th,
            .result-table td {
                padding: 4px 2px;
            }

            .result-table p {
                margin: 1px 0;
                font-size: 10px;
            }

            .result-table .header-cs-0 {
                font-size: 12px;
            }

            .result-table .ten-gods {
                font-size: 9px;
            }

            .form-container {
                padding: 20px 15px !important;
            }

            .result-container {
                border-radius: 12px !important;
            }

            h2.form-title {
                font-size: 1.25rem !important;
            }

            h3.form-title {
                font-size: 1.1rem !important;
            }
        }

        .phan9-muc1-sheet {
            max-width: 42rem;
            margin: 0 auto 1.5rem;
            background: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            padding: 1.5rem;
            border-left: 4px solid rgb(129 140 248);
        }

        @media (max-width: 640px) {
            .phan9-muc1-sheet {
                padding: 1rem;
            }
        }

        .phan9b-beam-chart {
            display: flex;
            flex-direction: column;
            gap: 14px;
            padding: 18px 4px 8px;
            max-width: 100%;
        }

        .phan9b-beam-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .phan9b-beam-lbl {
            display: flex;
            align-items: center;
            width: 82px;
            flex-shrink: 0;
        }

        .phan9b-beam-lbl img {
            display: block;
            height: 30px;
            width: auto;
            max-width: 82px;
            object-fit: contain;
        }

        .phan9b-beam-track-area {
            flex: 1;
            position: relative;
            height: 36px;
            min-width: 0;
        }

        .phan9b-beam-canvas {
            position: absolute;
            top: 50%;
            left: 0;
            transform: translateY(-50%);
            width: 100%;
            height: 36px;
            display: block;
        }

        .phan9b-beam-badge {
            font-size: 13px;
            font-weight: 500;
            min-width: 96px;
            text-align: right;
            flex-shrink: 0;
            letter-spacing: -0.01em;
            line-height: 1.35;
        }

        @media (max-width: 640px) {
            .phan9b-beam-lbl {
                width: 68px;
            }

            .phan9b-beam-badge {
                min-width: 88px;
                font-size: 12px;
            }
        }

        .result-container {
            display: none;
            margin-top: 30px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        /* Responsive cho form và container */
        @media (max-width: 640px) {
            .form-container {
                margin: 10px;
                padding: 25px;
            }

            .result-container {
                margin: 10px;
                margin-top: 20px;
            }

            /* Điều chỉnh grid thành 1 cột trên mobile */
            .grid.grid-cols-3 {
                grid-template-columns: repeat(1, minmax(0, 1fr)) !important;
            }

            .grid.grid-cols-2 {
                grid-template-columns: repeat(1, minmax(0, 1fr)) !important;
            }

            /* Cải thiện card và section trên mobile */
            .dung-than-section {
                padding: 15px;
                margin: 15px 0;
            }

            .element-card {
                padding: 12px;
                margin: 8px 0;
            }

            /* Giảm padding cho các section */
            .bg-white.rounded-lg.shadow-lg.p-6 {
                padding: 1rem !important;
            }

            /* Điều chỉnh kích thước icon và text */
            .fas.text-4xl {
                font-size: 2rem !important;
            }

            .text-3xl {
                font-size: 1.5rem !important;
            }

            .text-2xl {
                font-size: 1.25rem !important;
            }

            .text-xl {
                font-size: 1.125rem !important;
            }

            /* Cải thiện biểu đồ radar chart trên mobile */
            #nguHanhDongSection .bg-white {
                padding: 0.75rem !important;
            }

            #nguHanhDongChart {
                min-height: 320px !important;
                max-height: 400px !important;
            }

            /* Container của chart full width trên mobile */
            #nguHanhDongSection .flex.justify-center {
                max-width: 100% !important;
                padding: 0 !important;
            }

            /* Responsive cho bảng Chất Lượng Thập Thần */
            .chat-luong-table {
                min-width: 350px !important;
                font-size: 11px;
            }

            .chat-luong-table th,
            .chat-luong-table td {
                padding: 6px 8px !important;
                font-size: 11px !important;
            }

            .chat-luong-table .bar-container {
                height: 20px !important;
            }

            .chat-luong-table .bar {
                height: 20px !important;
            }

            .chat-luong-table .bar-bg {
                height: 20px !important;
            }

            .chat-luong-table .bar-container span {
                font-size: 10px !important;
            }
        }

        .dung-than-section {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #4f46e5;
        }

        .element-card {
            background: white;
            border-radius: 8px;
            padding: 16px;
            margin: 10px 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-left: 4px solid;
        }

        .element-hoa {
            border-left-color: #e74c3c;
        }

        .element-tho {
            border-left-color: #d35400;
        }

        .element-kim {
            border-left-color: #bdc3c7;
        }

        .element-moc {
            border-left-color: #27ae60;
        }

        .element-thuy {
            border-left-color: #3498db;
        }

        .element-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            color: white;
            font-weight: bold;
            margin-right: 8px;
            margin-bottom: 8px;
        }

        .badge-hoa {
            background-color: #e74c3c;
        }

        .badge-tho {
            background-color: #d35400;
        }

        .badge-kim {
            background-color: #bdc3c7;
            color: #333;
        }

        .badge-moc {
            background-color: #27ae60;
        }

        .badge-thuy {
            background-color: #3498db;
        }

        .info-item {
            margin-bottom: 15px;
        }

        .info-item h4 {
            font-weight: 600;
            margin-bottom: 8px;
            color: #374151;
        }

        .career-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }

        .career-item {
            background: #f9fafb;
            padding: 12px;
            border-radius: 6px;
            border-left: 3px solid #4f46e5;
        }

        .result-table thead tr {
            background-color: #009900;
            color: white;
        }

        .result-table tbody tr td:nth-child(1) {
            background-color: #009900;
            color: white;
        }

        .ten-gods {
            color: #009900;
            font-weight: bold;
        }

        .text-center {
            text-align: center !important;
        }

        .header-cs-0 {
            font-size: 40px;
            font-weight: bold;
            line-height: 1.1;
            margin: 0 0 4px;
        }

        .result-table tbody td.text-center {
            vertical-align: middle;
            min-width: 88px;
        }

        .result-table tbody td.text-center p {
            margin: 2px 0;
            line-height: 1.3;
        }

        .result-table tbody td.text-center .ten-gods {
            margin-top: 4px;
            font-size: 14px;
            line-height: 1.25;
        }

        .result-table tbody td.text-center .tang-can-cell {
            display: inline-block;
            min-width: 72px;
            padding: 0 6px;
            text-align: center;
            vertical-align: top;
        }

        .result-table tbody td.text-center .tang-can-cell p {
            margin: 2px 0;
            line-height: 1.25;
        }

        .result-table tbody td.text-center .tang-can-cell .font-bold {
            font-size: 15px;
            line-height: 1.2;
        }

        .cs_selected {
            background-color: #9AD58E;
        }

        /* Đại Vận Table Styles */
        #daiVanTable {
            border-collapse: collapse;
            width: 100%;
            min-width: 1200px;
            background-color: white;
        }

        /* All data cells default: white background, black text */
        #daiVanTable tbody tr td {
            background-color: white;
            color: #000;
            padding: 12px 8px;
            text-align: center;
            border: 1px solid #000;
        }

        /* Row 1: Tuổi */
        #daiVanTable tbody tr:first-child td {
            font-weight: bold;
        }

        /* Row 4: Tàng Can */
        #daiVanTable tbody tr:nth-child(4) td {
            padding: 8px;
            vertical-align: top;
        }

        /* Rows 5+: Years (phần 2) */
        #daiVanTable tbody tr:nth-child(n+5) td {
            padding: 5px 7px;
            line-height: 1.2;
        }

        #daiVanTable .dai-van-can {
            font-size: 24px;
            font-weight: bold;
            color: #000;
        }

        #daiVanTable .dai-van-element {
            font-size: 14px;
            margin-top: 4px;
            color: #000;
        }

        #daiVanTable .dai-van-thapthan {
            font-size: 13px;
            color: #009900;
            font-weight: normal;
            margin-top: 4px;
        }

        #daiVanTable .dai-van-khongvong {
            font-size: 11px;
            color: #666;
            font-style: italic;
            margin-top: 4px;
        }

        #daiVanTable .tang-can-item {
            display: inline-block;
            margin: 0 8px;
            font-size: 12px;
            vertical-align: top;
        }

        #daiVanTable .tang-can-item .tc-can {
            font-weight: bold;
            font-size: 14px;
            color: #000;
        }

        #daiVanTable .tang-can-item .tc-element {
            font-size: 12px;
            margin-top: 2px;
            color: #000;
        }

        #daiVanTable .tang-can-item .tc-thapthan {
            font-size: 11px;
            color: #009900;
            font-weight: normal;
            margin-top: 2px;
        }

        #daiVanTable .year-item {
            margin: 4px 0;
            font-size: 12px;
            line-height: 1.2;
        }

        #daiVanTable .year-canchi {
            font-weight: bold;
            font-size: 14px;
        }

        #daiVanTable .year-number {
            font-size: 12px;
            color: #333;
            margin-top: 2px;
        }

        #daiVanTable .year-note {
            font-size: 11px;
            color: #666;
            font-style: italic;
            margin-top: 2px;
        }

        /* Label column */
        #daiVanTable tbody tr td:first-child {
            background-color: #009900;
            color: white;
            font-weight: bold;
            text-align: center;
            width: 100px;
            min-width: 80px;
        }

        /* Niên Vận Table Styles - giống bảng Lá Số */
        #nienVanTable {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
            min-width: 600px;
        }

        #nienVanTable,
        #nienVanTable th,
        #nienVanTable td {
            border: 1px solid black;
        }

        #nienVanTable th,
        #nienVanTable td {
            padding: 8px;
            text-align: center;
        }

        /* PHẦN 8 – IV: bảng xoay (cột = năm), giống sheet CODING LOGIC */
        .phan8-iv-grid {
            border-collapse: collapse;
            width: 100%;
            min-width: 720px;
            background: #fff;
        }

        .phan8-iv-grid th,
        .phan8-iv-grid td {
            border: 1px solid #000;
            padding: 8px 6px;
            vertical-align: middle;
        }

        .phan8-iv-grid .phan8-iv-head {
            background-color: #ef4444 !important;
            color: #000;
            font-weight: 700;
            text-align: center;
            font-size: 1rem;
        }

        .phan8-iv-grid .phan8-iv-label {
            background-color: #fff;
            color: #000;
            font-weight: 600;
            text-align: left;
            white-space: nowrap;
            min-width: 6.5rem;
        }

        .phan8-iv-grid .phan8-iv-cell-center {
            text-align: center;
        }

        /* Độ đậm theo số dòng Chú ý: 1 nhạt nhất, càng nhiều càng đậm */
        .phan8-iv-grid .phan8-iv-depth-1 { background-color: #fffbeb; }
        .phan8-iv-grid .phan8-iv-depth-2 { background-color: #fef3c7; }
        .phan8-iv-grid .phan8-iv-depth-3 { background-color: #fde68a; }
        .phan8-iv-grid .phan8-iv-depth-4 { background-color: #fcd34d; }
        .phan8-iv-grid .phan8-iv-depth-5 { background-color: #fbbf24; }
        .phan8-iv-grid .phan8-iv-depth-6 { background-color: #f59e0b; }
        .phan8-iv-grid .phan8-iv-depth-7 { background-color: #d97706; }
        .phan8-iv-grid .phan8-iv-depth-8 { background-color: #b45309; }
        .phan8-iv-grid .phan8-iv-depth-9 { background-color: #92400e; }
        .phan8-iv-grid .phan8-iv-depth-10 { background-color: #78350f; color: #fff; }
        .phan8-iv-grid .phan8-iv-depth-11 { background-color: #451a03; color: #fff; }
        .phan8-iv-grid .phan8-iv-depth-12 { background-color: #292524; color: #fff; }

        .phan8-iv-grid .phan8-iv-chu-y-cell {
            min-height: 4.5rem;
            vertical-align: top;
            padding-top: 10px;
            padding-bottom: 10px;
        }

        .phan8-iv-grid .phan8-iv-chu-y-stack {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-height: 3.5rem;
            font-weight: 600;
            font-size: 0.8125rem;
            line-height: 1.25;
        }

        /* Chuyển Cuốn 1 / Cuốn 2 */
        #quyenTabBar {
            display: none;
            margin-bottom: 1.5rem;
        }

        #quyenTabBar.is-visible {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            justify-content: center;
        }

        .quyen-tab-btn {
            padding: 0.65rem 1.5rem;
            border-radius: 9999px;
            font-weight: 700;
            font-size: 0.95rem;
            border: 2px solid #c7d2fe;
            background: #fff;
            color: #4338ca;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .quyen-tab-btn:hover {
            border-color: #6366f1;
            background: #eef2ff;
        }

        .quyen-tab-btn.active {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border-color: transparent;
            color: #fff;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
        }

        .quyen-tab-btn .quyen-tab-parts {
            display: block;
            font-size: 0.7rem;
            font-weight: 500;
            opacity: 0.9;
            margin-top: 0.15rem;
        }

        .quyen-panel {
            display: block;
        }

        .quyen-panel.is-hidden {
            display: none !important;
        }

        .quyen-panel-heading {
            text-align: center;
            font-size: 1.125rem;
            font-weight: 700;
            color: #4338ca;
            margin: 0 0 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #e0e7ff;
        }

        #nienVanTable thead tr {
            background-color: #009900;
            color: white;
        }

        #nienVanTable tbody tr td:nth-child(1) {
            background-color: #009900;
            color: white;
            font-weight: bold;
        }

        #nienVanTable .header-cs-0 {
            font-size: 40px;
            font-weight: bold;
        }

        #nienVanTable .ten-gods {
            color: #009900;
            font-weight: bold;
        }

        /* Responsive cho mobile */
        @media (max-width: 768px) {
            #nienVanTable {
                font-size: 12px;
                min-width: 500px;
            }

            #nienVanTable th,
            #nienVanTable td {
                padding: 6px 4px;
            }

            #nienVanTable p {
                margin: 2px 0;
                font-size: 11px;
            }

            #nienVanTable .header-cs-0 {
                font-size: 14px;
            }

            #nienVanTable .ten-gods {
                font-size: 10px;
            }
        }

        @media (max-width: 480px) {
            #nienVanTable {
                font-size: 11px;
                min-width: 450px;
            }

            #nienVanTable th,
            #nienVanTable td {
                padding: 4px 2px;
            }

            #nienVanTable p {
                margin: 1px 0;
                font-size: 10px;
            }

            #nienVanTable .header-cs-0 {
                font-size: 12px;
            }

            #nienVanTable .ten-gods {
                font-size: 9px;
            }
        }

        /* Responsive adjustments for Dai Van table */
        @media (max-width: 768px) {
            #daiVanTable {
                min-width: 1000px;
                font-size: 11px;
            }

            #daiVanTable tbody tr td {
                padding: 8px 4px;
            }

            #daiVanTable .dai-van-can {
                font-size: 20px;
            }

            #daiVanTable .dai-van-element {
                font-size: 11px;
            }

            #daiVanTable .dai-van-thapthan {
                font-size: 10px;
            }

            #daiVanTable .tang-can-item {
                margin: 0 4px;
            }

            #daiVanTable .tang-can-item .tc-can {
                font-size: 12px;
            }

            #daiVanTable .tang-can-item .tc-element {
                font-size: 10px;
            }

            #daiVanTable .tang-can-item .tc-thapthan {
                font-size: 9px;
            }

            #daiVanTable .year-item {
                margin: 3px 0;
                line-height: 1.2;
            }

            #daiVanTable .year-canchi {
                font-size: 12px;
            }

            #daiVanTable .year-number {
                font-size: 11px;
            }
        }

        /* Phần 5 – khung từ khóa cốt lõi (đồng bộ PDF) */
        .phan5-kw-grid {
            width: 100%;
            margin-bottom: 12px;
        }

        .phan5-kw-grid table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .phan5-kw-grid td {
            width: 33.33%;
            text-align: center;
            vertical-align: middle;
            padding: 0 6px;
            background-color: transparent;
            color: inherit;
        }

        .phan5-kw-box {
            position: relative;
            width: 120px;
            height: 195px;
            margin: 0 auto;
        }

        .phan5-kw-frame {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: block;
            object-fit: fill;
        }

        .phan5-kw-text {
            position: absolute;
            top: 18%;
            left: 14%;
            width: 72%;
            height: 58%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .phan5-kw-text-inner {
            text-align: center;
            width: 100%;
        }

        .phan5-kw-text-inner span {
            display: inline-block;
            max-width: 100%;
            color: #D4AF37;
            font-family: 'utm-davida', 'UTM Davida', 'Times New Roman', serif;
            font-weight: normal;
            font-size: 26px;
            line-height: 1.2;
            text-align: center;
            text-transform: uppercase;
            word-wrap: break-word;
            word-break: break-word;
            white-space: normal;
        }

        .phan5-muc-label {
            color: #6E0101;
            font-weight: 700;
            font-style: italic;
        }

        .phan5-bat-tu-wrap {
            margin: 12px 0 16px;
        }

        .phan5-bat-tu-wrap .phan5-bt-title {
            text-align: center;
            font-weight: 700;
            color: #6E0101;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .phan5-bat-tu-wrap .result-table td.phan5-hl {
            background-color: #F8D7DC;
        }

        .phan9-sub-label {
            color: #6E0101;
            font-weight: 700;
        }
    </style>
</head>

<body class="flex items-center justify-center p-4">
    <div class="w-full max-w-screen-2xl">
        <div class="form-container p-8 mb-8">
            <!-- Header -->
            <div class="text-center mb-8 relative">
                <!-- Nút đăng xuất và quản lý SIM -->
                @auth
                    <div class="absolute top-0 right-0 flex gap-2">
                        <a href="{{ route('sims.index') }}"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center gap-2">
                            <i class="fas fa-mobile-alt"></i>
                            Quản lý SIM
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit"
                                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center gap-2">
                                <i class="fas fa-sign-out-alt"></i>
                                Đăng Xuất
                            </button>
                        </form>
                    </div>
                @endauth
                <h1 class="text-3xl font-bold form-title mb-2">
                    LÁ SỐ TỨ TRỤ
                </h1>
                <p class="text-gray-600">Nhập thông tin để lấy lá số tứ trụ chính xác</p>
                @auth
                    <p class="text-sm text-gray-500 mt-2">
                        <i class="fas fa-user-circle mr-1"></i>
                        Xin chào, <strong>{{ Auth::user()->name }}</strong>
                    </p>
                @endauth
            </div>

            <form id="tutuForm" class="space-y-6">
                <!-- Họ tên -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-2 text-purple-500"></i>
                        Họ và tên
                    </label>
                    <input type="text" name="full_name"
                        class="w-full px-4 py-3 input-field rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                        placeholder="Nhập họ và tên đầy đủ" required>
                </div>

                <!-- Ngày tháng năm sinh -->
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-day mr-2 text-blue-500"></i>
                            Ngày sinh
                        </label>
                        <input type="number" name="d" min="1" max="31"
                            class="w-full px-4 py-3 input-field rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="DD" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt mr-2 text-green-500"></i>
                            Tháng sinh
                        </label>
                        <input type="number" name="m" min="1" max="12"
                            class="w-full px-4 py-3 input-field rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                            placeholder="MM" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar mr-2 text-red-500"></i>
                            Năm sinh
                        </label>
                        <input type="number" name="y" min="1940" max="2031"
                            class="w-full px-4 py-3 input-field rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                            placeholder="YYYY" required>
                    </div>
                </div>

                <!-- Giờ sinh và năm xem -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-clock mr-2 text-yellow-500"></i>
                            Giờ sinh
                        </label>
                        <div class="flex space-x-2">
                            <input type="number" name="h" id="hourInput" min="0" max="23"
                                class="w-full px-4 py-3 input-field rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500"
                                placeholder="Giờ" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-clock mr-2 text-yellow-500"></i>
                            Phút sinh
                        </label>
                        <div class="flex space-x-2">
                            <input type="number" name="minute" id="minuteInput" min="0" max="59"
                                class="w-full px-4 py-3 input-field rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500"
                                placeholder="Phút" required>
                        </div>
                    </div>
                </div>

                <!-- Checkbox không rõ giờ sinh -->
                <div class="flex items-center">
                    <input type="checkbox" name="uknow_birthdate" id="unknowBirthtime"
                        class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <label for="unknowBirthtime" class="ml-2 text-sm font-medium text-gray-700">
                        <i class="fas fa-question-circle mr-1 text-gray-500"></i>
                        Không rõ giờ sinh
                    </label>
                </div>

                <!-- Giới tính và Số điện thoại -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-venus-mars mr-2 text-indigo-500"></i>
                            Giới tính
                        </label>
                        <select
                            class="w-full px-4 py-3 input-field rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            name="g" id="genderSelect" required>
                            <option value="">-- Chọn giới tính --</option>
                            <option value="male">Nam</option>
                            <option value="female">Nữ</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-phone mr-2 text-teal-500"></i>
                            Số điện thoại
                        </label>
                        <input type="tel" name="phone"
                            class="w-full px-4 py-3 input-field rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500"
                            placeholder="Nhập số điện thoại">
                    </div>
                </div>

                <!-- Địa chỉ -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-map-marker-alt mr-2 text-orange-500"></i>
                        Địa chỉ
                    </label>
                    <input type="text" name="address"
                        class="w-full px-4 py-3 input-field rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                        placeholder="Nhập địa chỉ">
                </div>

                <!-- Buttons -->
                <div class="grid grid-cols-1 gap-4 pt-4">
                    <button type="submit" id="submitBtn"
                        class="btn-primary text-white font-bold py-4 px-6 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Lấy Lá Số Tứ Trụ
                    </button>
                </div>
            </form>

            <!-- Footer -->
            <div class="text-center mt-8 pt-6 border-t border-gray-200">
                <p class="text-sm text-gray-500">
                    <i class="fas fa-shield-alt mr-1"></i>
                    Thông tin của bạn được bảo mật an toàn
                </p>
            </div>
        </div>

        <!-- Kết quả -->
        <div id="resultContainer" class="result-container">
            <h2 class="text-2xl font-bold text-center mb-4 form-title">KẾT QUẢ LÁ SỐ TỨ TRỤ</h2>

            <div id="pdfDownloadBar" class="mb-6 flex flex-col items-center gap-3">
                <div class="flex flex-wrap justify-center gap-3">
                    <button type="button" id="btnDownloadPdfQ1" disabled
                        class="inline-flex items-center gap-2 rounded-lg bg-red-700 px-5 py-3 text-sm font-semibold text-white shadow hover:bg-red-800 disabled:cursor-not-allowed disabled:opacity-60">
                        <i class="fas fa-file-pdf"></i>
                        <span class="btn-label">Tải PDF Quyển 1</span>
                    </button>
                    <button type="button" id="btnDownloadPdfQ2" disabled
                        class="inline-flex items-center gap-2 rounded-lg bg-red-700 px-5 py-3 text-sm font-semibold text-white shadow hover:bg-red-800 disabled:cursor-not-allowed disabled:opacity-60">
                        <i class="fas fa-file-pdf"></i>
                        <span class="btn-label">Tải PDF Quyển 2</span>
                    </button>
                </div>
                <p id="pdfStatusHint" class="text-sm text-gray-600 text-center hidden"></p>
            </div>

            <!-- Bảng chính -->
            <div class="table-wrapper">
                <table class="result-table">
                    <thead>
                        <tr>
                            <td>LÁ SỐ BÁT TỰ</td>
                            <td class="text-center">Giờ sinh</td>
                            <td class="text-center">Ngày sinh</td>
                            <td class="text-center">Tháng sinh</td>
                            <td class="text-center">Năm sinh</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Thiên can</td>
                            <td class="text-center" id="thien-can-hour">
                                <p class="header-cs-0" id="thien-can-hour-value">Đinh</p>
                                <p class="" id="thien-can-hour-menh">- Thủy</p>
                                <p class="ten-gods" id="thien-can-hour-chutinh">Thất Sát</p>
                            </td>
                            <td class="text-center cs_selected" id="thien-can-day">
                                <p class="header-cs-0" id="thien-can-day-value">Đinh</p>
                                <p class="" id="thien-can-day-menh">Thủy</p>
                                <p class="ten-gods" id="thien-can-day-chutinh">Thất Sát</p>
                            </td>
                            <td class="text-center" id="thien-can-month">
                                <p class="header-cs-0" id="thien-can-month-value">Đinh</p>
                                <p class="" id="thien-can-month-menh">- Thủy</p>
                                <p class="ten-gods" id="thien-can-month-chutinh">Thất Sát</p>
                            </td>
                            <td class="text-center" id="thien-can-year">
                                <p class="header-cs-0" id="thien-can-year-value">Đinh</p>
                                <p class="" id="thien-can-year-menh">- Thủy</p>
                                <p class="ten-gods" id="thien-can-year-chutinh">Thất Sát</p>
                            </td>
                        </tr>
                        <tr>
                            <td>Địa chi</td>

                            <td class="text-center" id="dia-chi-hour">
                                <p class="header-cs-0" id="dia-chi-hour-value">Đinh</p>
                                <p class="" id="dia-chi-hour-menh">Thủy</p>
                                <p class="text-sm italic text-gray-500" id="dia-chi-hour-khongvong"></p>
                            </td>
                            <td class="text-center cs_selected" id="dia-chi-day">
                                <p class="header-cs-0" id="dia-chi-day-value">Đinh</p>
                                <p class="" id="dia-chi-day-menh">Thủy</p>
                                <p class="text-sm italic text-gray-500" id="dia-chi-day-khongvong"></p>
                            </td>
                            <td class="text-center" id="dia-chi-month">
                                <p class="header-cs-0" id="dia-chi-month-value">Đinh</p>
                                <p class="" id="dia-chi-month-menh">Thủy</p>
                                <p class="text-sm italic text-gray-500" id="dia-chi-month-khongvong"></p>
                            </td>
                            <td class="text-center" id="dia-chi-year">
                                <p class="header-cs-0" id="dia-chi-year-value">Đinh</p>
                                <p class="" id="dia-chi-year-menh">Thủy</p>
                                <p class="text-sm italic text-gray-500" id="dia-chi-year-khongvong"></p>
                            </td>
                        </tr>
                        <tr>
                            <td>Tàng Can</td>

                            <td class="text-center">
                                <div class="flex justify-center" id="tang-can-hour">

                                </div>
                            </td>
                            <td class="text-center cs_selected">
                                <div class="flex justify-center" id="tang-can-day">

                                </div>
                            </td>

                            <td class="text-center">
                                <div class="flex justify-center" id="tang-can-month">

                                </div>

                            </td>
                            <td class="text-center">
                                <div class="flex justify-center" id="tang-can-year">

                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Bảng Đại Vận -->
            <div id="daiVanSection" class="quyen-section quyen-shared mt-6 mb-6" style="display: none;">
                <h3 class="text-xl font-bold mb-4 form-title text-center">ĐẠI VẬN</h3>
                <div class="table-wrapper">
                    <table id="daiVanTable" class="result-table">
                        <!-- Table content will be populated by JavaScript -->
                    </table>
                </div>
            </div>

            <!-- Bảng Niên Vận -->
            <div id="nienVanSection" class="quyen-section quyen-shared mt-6 mb-6" style="display: none;">
                <h3 class="text-xl font-bold mb-4 form-title text-center">NIÊN VẬN</h3>
                <div class="table-wrapper">
                    <table id="nienVanTable" class="result-table">
                        <!-- Table content will be populated by JavaScript -->
                    </table>
                </div>
            </div>

            <!-- Hiển thị Mệnh -->
            <div id="menhSection" class="quyen-section quyen-shared mt-6 mb-6" style="display: none;">
                <div id="menhCard" class="rounded-xl p-6 shadow-lg">
                    <div class="flex items-center justify-center">
                        <div class="text-center">
                            <div class="mb-3">
                                <i id="menhIcon" class="fas text-white text-4xl"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-white mb-2">MỆNH</h3>
                            <p id="menhValue" class="text-3xl font-bold text-white"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hiển thị Ngũ Hành Động -->
            <div id="nguHanhDongSection" class="quyen-section quyen-shared mt-6 mb-6" style="display: none;">
                <h3 class="text-xl font-bold mb-4 form-title">
                    <i class="fas fa-chart-pie mr-2"></i>
                    NGŨ HÀNH BẢN MỆNH
                </h3>
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex flex-col lg:flex-row gap-6 items-start">
                        <!-- Biểu đồ Ngũ Hành -->
                        <div class="flex justify-center items-center lg:w-2/5" style="min-width: 300px;">
                            <div style="max-width: 450px; width: 100%;">
                                <canvas id="nguHanhDongChart"></canvas>
                            </div>
                        </div>

                        <!-- Bảng Chất Lượng Thập Thần -->
                        <div id="chatLuongThapThanTable" class="w-full lg:w-3/5 lg:flex-shrink-0"
                            style="display: none;">
                            <div class="overflow-x-auto">
                                <table class="chat-luong-table"
                                    style="border-collapse: collapse; width: 100%; min-width: 500px;">
                                    <thead>
                                        <tr>
                                            <th colspan="3"
                                                style="text-align: center; padding: 12px; background-color: #6E0101; color: #E5CA8E; font-size: 14px; font-weight: bold; border: 1px solid #ffffff;">
                                                CHẤT LƯỢNG THẬP THẦN
                                            </th>
                                        </tr>
                                        <tr>
                                            <th
                                                style="padding: 8px 12px; background-color: #6E0101; color: #E5CA8E; font-size: 13px; font-weight: bold; border: 1px solid #ffffff; width: 25%;">
                                            </th>
                                            <th
                                                style="padding: 8px 12px; background-color: #6E0101; color: #E5CA8E; font-size: 13px; font-weight: bold; border: 1px solid #ffffff; width: 37.5%;">
                                                BẢN MỆNH</th>
                                            <th style="padding: 8px 12px; background-color: #6E0101; color: #E5CA8E; font-size: 13px; font-weight: bold; border: 1px solid #ffffff; width: 37.5%;"
                                                id="nienMenhYear">NIÊN MỆNH</th>
                                        </tr>
                                    </thead>
                                    <tbody id="chatLuongThapThanBody">
                                        <!-- Rows will be populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chỉ số biểu đồ cột (Phần 2) -->
            <div id="chiSoBieuDoCotSection" class="quyen-section quyen-shared mt-6 mb-6" style="display: none;">
                <h3 class="text-xl font-bold mb-4 form-title">
                    <i class="fas fa-chart-bar mr-2"></i>
                    BIỂU ĐỒ 6 KHÍA CẠNH
                </h3>
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="overflow-x-auto">
                        <div class="flex justify-center items-center" style="min-height: 280px; min-width: 400px;">
                            <div style="width: 100%; max-width: 700px;">
                                <canvas id="chiSoBieuDoCotChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div id="chiSoBieuDoCotChenhLech" class="mt-6 overflow-x-auto">
                        <!-- Bảng chênh lệch Bản mệnh vs Niên mệnh -->
                    </div>
                </div>
            </div>
            <!-- Quý Nhân & Văn Xương (Thần Sát) -->
            <div id="quyNhanVanXuongSection" class="quyen-section quyen-shared mt-6 mb-6 hidden">
                <h3 class="text-xl font-bold mb-4 form-title">
                    <i class="fas fa-user-friends mr-2"></i>
                    THẦN SÁT
                </h3>
                <div class="bg-white rounded-lg shadow-lg p-0 overflow-hidden">
                    <table class="min-w-full text-sm border-collapse">
                        <thead>
                            <tr class="bg-gray-700 text-white">
                                <th class="px-4 py-2 text-left font-semibold w-1/3">
                                    Ngày Sinh / Nhật Chủ
                                </th>
                                <th class="px-4 py-2 text-left font-semibold">
                                    <span id="qnvx-day-stem"></span>
                                    <span id="qnvx-dia-chi-ngay" class="ml-1"></span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b">
                                <td class="px-4 py-2 text-gray-700">Quý Nhân</td>
                                <td class="px-4 py-2 font-semibold text-indigo-600">
                                    <span id="qnvx-quy-nhan"></span>
                                </td>
                            </tr>
                            <tr class="border-b">
                                <td class="px-4 py-2 text-gray-700">Văn Xương</td>
                                <td class="px-4 py-2 font-semibold text-rose-600">
                                    <span id="qnvx-van-xuong"></span>
                                </td>
                            </tr>
                            <tr class="border-b">
                                <td class="px-4 py-2 text-gray-700">Đào Hoa</td>
                                <td class="px-4 py-2 font-semibold text-gray-800">
                                    <span id="qnvx-dao-hoa"></span>
                                </td>
                            </tr>
                            <tr class="border-b">
                                <td class="px-4 py-2 text-gray-700">Dịch Mã</td>
                                <td class="px-4 py-2 font-semibold text-gray-800">
                                    <span id="qnvx-dich-ma"></span>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 text-gray-700">Cô Thần</td>
                                <td class="px-4 py-2 font-semibold text-gray-800">
                                    <span id="qnvx-co-than"></span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="quyenTabBar" role="tablist" aria-label="Chọn cuốn luận giải">
                <button type="button" class="quyen-tab-btn active" data-quyen="1" role="tab" aria-selected="true">
                    CUỐN 1
                    <span class="quyen-tab-parts">Phần 3 · 5 · 6 · 8 · 9</span>
                </button>
                <button type="button" class="quyen-tab-btn" data-quyen="2" role="tab" aria-selected="false">
                    CUỐN 2
                    <span class="quyen-tab-parts">Phần 4 · 7 · 8 · 9</span>
                </button>
            </div>

            <!-- CUỐN 1: Phần 3 · 5 · 6 · 8 · 9 -->
            <div id="quyenCuon1Panel" class="quyen-panel" data-quyen="1">
                <p class="quyen-panel-heading">CUỐN 1 — Phần 3 · 5 · 6 · 8 · 9</p>

            <div id="hanhNoiDungNienVanSection" class="quyen-section mt-6 mb-6" style="display: none;">
                <h3 class="text-xl font-bold mb-4 form-title">
                    <i class="fas fa-leaf mr-2"></i>
                    PHẦN 3 - TỔNG QUAN NGŨ HÀNH BẢN MỆNH
                </h3>
                <div id="hanhNoiDungNienVanContainer">
                    <!-- Nội dung do JS điền -->
                </div>
            </div>

            <div id="chatLuongNhatChuSection" class="quyen-section mt-6 mb-6 bg-white rounded-lg shadow-lg p-6 mb-4 border-l-4 border-amber-300" style="display: none;">
                <h3 class="text-xl font-bold mb-4 form-title">
                    <i class="fas fa-user-edit mr-2"></i>
                    PHẦN 3 — III. CHẤT LƯỢNG NHẬT CHỦ
                </h3>
                <div id="chatLuongNhatChuContainer">
                    <!-- Mùa sinh, Ngũ hành mùa sinh + danh sách items -->
                </div>
            </div>

            <!-- PHẦN 5: Tổng quan các khía cạnh -->
            <div id="tongQuanKhiaCanhSection" class="quyen-section mt-6 mb-6" style="display: none;">
                <h3 class="text-xl font-bold mb-4 form-title">
                    <i class="fas fa-compass mr-2"></i>
                    PHẦN 5 TỔNG QUAN CÁC KHÍA CẠNH TRONG CUỘC SỐNG
                </h3>
                <div id="tongQuanKhiaCanhContent" class="bg-white rounded-lg shadow-lg p-6 space-y-6">
                    <!-- Nội dung được load từ API /api/phan-5/tong-quan -->
                </div>
            </div>
            <!-- Các khía cạnh theo Thập Thần -->
            <div id="suNghiepThapThanSection" class="quyen-section mt-6 mb-6" style="display: none;">
                <h3 class="text-xl font-bold mb-4 form-title">
                    <i class="fas fa-briefcase mr-2"></i>
                    PHẦN 5: THẬP THẦN VÀ CÁC KHÍA CẠNH TRONG CUỘC SỐNG
                </h3>
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div id="phan5KhiaCanhContainer" class="space-y-8 text-sm"></div>
                    <div id="sucKhoeHyKyThanSection" class="mt-8 pt-6 border-t border-gray-200" style="display: none;">
                        <h4 class="font-semibold text-indigo-700 mb-3">V. SỨC KHỎE – HỶ THẦN / KỴ THẦN</h4>
                        <p id="sucKhoeHyKyThanNgay" class="text-sm text-gray-700 mb-1"></p>
                        <p id="sucKhoeHyKyThanHy" class="text-sm text-green-700 mb-1"></p>
                        <p id="sucKhoeHyKyThanKy" class="text-sm text-red-700 mb-1"></p>
                        <p id="sucKhoeHyKyThanCounts" class="text-sm text-gray-600 mb-1"></p>
                        <p id="sucKhoeHyKyThanKetLuan" class="text-sm text-indigo-800 mb-3"></p>
                        <div id="sucKhoeChiTietContainer" class="mt-3 space-y-3 text-sm"></div>
                    </div>
                    <div id="giaiPhapThapThanSection" class="mt-8 pt-6 border-t border-gray-200">
                        <h4 class="font-semibold text-indigo-700 mb-3">VIII. GIẢI PHÁP CHO TỪNG THẬP THẦN</h4>
                        <div id="giaiPhapThapThanContainer" class="space-y-4 text-sm"></div>
                    </div>
                </div>
            </div>
            <!-- PHẦN 6 -->
            <div id="phan6DongChaySection" class="quyen-section mt-6 mb-6" style="display: none;">
                <h3 class="text-xl font-bold mb-4 form-title">
                    <i class="fas fa-water mr-2"></i>
                    PHẦN 6: LUẬN GIẢI DÒNG NĂNG LƯỢNG TRONG LÁ SỐ
                </h3>
                <div class="bg-white rounded-lg shadow-lg p-6 space-y-6">
                    <div id="phan6Ma1Container" class="space-y-4">
                        <h4 class="font-semibold text-indigo-700 mb-2">I: Ý nghĩa tứ trụ</h4>
                        <div id="phan6Ma1Content" class="text-sm text-gray-700 space-y-4"></div>
                    </div>
                    <div id="phan6Ma2Container" class="space-y-4" style="display: none;">
                        <div id="phan6Ma2Content" class="space-y-4 text-sm"></div>
                    </div>
                    <div id="phan6Ma3Container" class="space-y-4" style="display: none;">
                        <div id="phan6Ma3Content" class="space-y-4 text-sm"></div>
                    </div>
                    <div id="phan6Ma4Container" class="space-y-4" style="display: none;">
                        <div id="phan6Ma4Content" class="space-y-4 text-sm"></div>
                    </div>
                    <div id="phan6TransitionContainer" class="mt-6" style="display: none;">
                        <div id="phan6TransitionContent" class="text-sm text-gray-700"></div>
                </div>
            </div>
            </div>

            </div><!-- /quyenCuon1Panel -->

            <!-- CUỐN 2: Phần 4 · 7 · 8 · 9 -->
            <div id="quyenCuon2Panel" class="quyen-panel is-hidden" data-quyen="2">
                <p class="quyen-panel-heading">CUỐN 2 — Phần 4 · 7 · 8 · 9</p>

            <!-- Nhật Chủ Trụ Ngày -->
            <div id="nhatChuTruNgaySection" class="quyen-section mt-6 mb-6" style="display: none;">
                <h3 class="text-xl font-bold mb-4 form-title">
                    <i class="fas fa-user mr-2"></i>
                  PHẦN 4 NHẬT CHỦ TRỤ NGÀY
                </h3>
                <div id="nhatChuTruNgayContainer">
                    <!-- tru_ngay (Kỷ Tỵ) + danh sách items -->
                </div>
            </div>

            <!-- PHẦN 7: BÀI HỌC CUỘC SỐNG -->
            <div id="phan7BaiHocSection" class="quyen-section mt-6 mb-6" style="display: none;">
                <h3 class="text-xl font-bold mb-4 form-title">
                    <i class="fas fa-book-open mr-2"></i>
                    PHẦN 7: BÀI HỌC CUỘC SỐNG
                </h3>
                <div id="phan7BaiHocContent" class="bg-white rounded-lg shadow-lg p-6 space-y-6">
                    <div id="phan7TamThe" class="space-y-4"></div>
                    <div id="phan7Phan27" class="space-y-6"></div>
                    <div id="phan7TamTheCuoi" class="space-y-4"></div>
                </div>
            </div>

            </div><!-- /quyenCuon2Panel -->

            <!-- PHẦN 8 / 9: gắn vào #phan8Group + #phan9Section theo cuốn đang chọn -->
            <div id="phan8Group"></div>

            <div id="phan9Section" class="quyen-section mt-6 mb-6" style="display: none;">
                {{-- Bìa + cuộn thư (giống PDF Phần 9 / 9B) --}}
                <div id="phan9SharedCovers" class="mb-6 max-w-2xl mx-auto" style="display: none;">
                    <img id="phan9CoverImg" src="{{ asset('images/phan-9/bia-phan-9a.png') }}" alt="PHẦN 9 — Giải pháp tối ưu" class="w-full rounded-lg shadow-lg">
                </div>
                <div id="phan9aBlock" data-phan9-quyen="1">
                    <h3 class="text-xl font-bold mb-4 form-title">
                        <i class="fas fa-book-open mr-2"></i>
                        PHẦN 9A — GIẢI PHÁP TỐI ƯU
                    </h3>
                    <div id="phan9TransitionContainer" class="mb-4" style="display: none;">
                        <div id="phan9TransitionContent" class="text-sm text-gray-700 bg-amber-50 border border-amber-200 rounded-lg p-4"></div>
                    </div>
                    <div id="phan9Ma1Container" class="phan9-muc1-sheet text-sm text-gray-700 mb-6" style="display: none;">
                        <div id="phan9Ma1Content"></div>
                    </div>
                    <div id="phan9Ma2Container" class="bg-white rounded-lg shadow-lg p-6 text-sm text-gray-700" style="display: none;">
                        <div id="phan9Ma2Content"></div>
                    </div>
                </div>
                <div id="phan9bBlock" data-phan9-quyen="2" style="display: none;">
                    <h3 class="text-xl font-bold mb-4 form-title">
                        <i class="fas fa-balance-scale mr-2"></i>
                        PHẦN 9B — GIẢI PHÁP CÂN BẰNG
                    </h3>
                    <div id="phan9bTransitionContainer" class="mb-4" style="display: none;">
                        <div id="phan9bTransitionContent" class="text-sm text-gray-700 bg-amber-50 border border-amber-200 rounded-lg p-4"></div>
                    </div>
                    <div id="phan9bContent" class="space-y-4"></div>
                </div>
            </div>

            <!-- PHẦN 8 (gộp vào #phan8Group, hiển thị theo cuốn đang chọn) -->
            <div id="phan8DaiVanSection" data-phan8-quyen="1" class="quyen-section mt-6 mb-6" style="display: none;">
                <h3 class="text-xl font-bold mb-4 form-title">
                    <i class="fas fa-infinity mr-2"></i>
                    PHẦN 8: DỰ BÁO HẠN VẬN – ĐẠI VẬN
                </h3>
                <div class="bg-white rounded-lg shadow-lg p-6 space-y-6">

                    {{-- Thông tin Đại Vận hiện tại --}}
                    <div id="phan8CurrentDaiVan" class="p-4 bg-indigo-50 rounded-lg border border-indigo-200 text-sm text-gray-700"></div>

                    {{-- I. Đại Vận --}}
                    <div id="phan8YNghiaContainer" style="display: none;" class="space-y-4">
                        <div id="phan8YNghiaContent" class="text-sm text-gray-700 whitespace-pre-line bg-amber-50 border border-amber-200 rounded-lg p-4"></div>

                        {{-- Đại Vận – Trụ Năm --}}
                        <div id="phan8NamSection" style="display: none;" class="space-y-3">
                            <div id="phan8NamGioiThieu" class="text-sm text-gray-700 whitespace-pre-line bg-gray-50 border border-gray-200 rounded-lg p-3" style="display: none;"></div>
                            <div id="phan8NamContent" class="space-y-3 text-sm"></div>
                        </div>

                        {{-- Đại Vận – Trụ Tháng --}}
                        <div id="phan8ThangSection" style="display: none;" class="space-y-3">
                            <div id="phan8ThangGioiThieu" class="text-sm text-gray-700 whitespace-pre-line bg-gray-50 border border-gray-200 rounded-lg p-3" style="display: none;"></div>
                            <div id="phan8ThangContent" class="space-y-3 text-sm"></div>
                        </div>

                        {{-- Đại Vận – Trụ Ngày --}}
                        <div id="phan8NgaySection" style="display: none;" class="space-y-3">
                            <div id="phan8NgayGioiThieu" class="text-sm text-gray-700 whitespace-pre-line bg-gray-50 border border-gray-200 rounded-lg p-3" style="display: none;"></div>
                            <div id="phan8NgayContent" class="space-y-3 text-sm"></div>
                        </div>

                        {{-- Đại Vận – Trụ Giờ --}}
                        <div id="phan8GioSection" style="display: none;" class="space-y-3">
                            <div id="phan8GioGioiThieu" class="text-sm text-gray-700 whitespace-pre-line bg-gray-50 border border-gray-200 rounded-lg p-3" style="display: none;"></div>
                            <div id="phan8GioContent" class="space-y-3 text-sm"></div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- PHẦN 8 – II. NIÊN VẬN -->
            <div id="phan8NienVanSection" class="quyen-section mt-6 mb-6" style="display: none;">
                <h3 class="text-xl font-bold mb-4 form-title">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    PHẦN 8: DỰ BÁO HẠN VẬN – NIÊN VẬN
                </h3>
                <div class="bg-white rounded-lg shadow-lg p-6 space-y-6">

                    {{-- Ý nghĩa Niên Vận (cuốn 2 / 8B) --}}
                    <div id="phan8NienVanYNghia" data-phan8-quyen="2" class="text-sm text-gray-700 whitespace-pre-line bg-amber-50 border border-amber-200 rounded-lg p-4" style="display: none;"></div>

                    {{-- Năm hiện tại — CUỐN 1 --}}
                    <div id="phan8HienTaiSection" data-phan8-quyen="1" style="display: none;" class="space-y-4">
                        <div id="phan8HienTaiInfo" class="p-4 bg-indigo-50 rounded-lg border border-indigo-200 text-sm text-gray-700"></div>
                        <div id="phan8HienTaiGioiThieu" class="text-sm text-gray-700 whitespace-pre-line bg-gray-50 border border-gray-200 rounded-lg p-3" style="display: none;"></div>
                        <div id="phan8HienTaiNamSection" style="display: none;" class="space-y-3">
                            <div id="phan8HienTaiNamContent" class="space-y-3 text-sm"></div>
                        </div>
                        <div id="phan8HienTaiThangSection" style="display: none;" class="space-y-3">
                            <div id="phan8HienTaiThangContent" class="space-y-3 text-sm"></div>
                        </div>
                        <div id="phan8HienTaiNgaySection" style="display: none;" class="space-y-3">
                            <div id="phan8HienTaiNgayContent" class="space-y-3 text-sm"></div>
                        </div>
                        <div id="phan8HienTaiGioSection" style="display: none;" class="space-y-3">
                            <div id="phan8HienTaiGioContent" class="space-y-3 text-sm"></div>
                        </div>
                    </div>

                    {{-- Năm tiếp theo — CUỐN 2 --}}
                    <div id="phan8TiepTheoSection" data-phan8-quyen="2" style="display: none;" class="space-y-4">
                        <div id="phan8TiepTheoInfo" class="p-4 bg-indigo-50 rounded-lg border border-indigo-200 text-sm text-gray-700"></div>
                        <div id="phan8TiepTheoGioiThieu" class="text-sm text-gray-700 whitespace-pre-line bg-gray-50 border border-gray-200 rounded-lg p-3" style="display: none;"></div>
                        <div id="phan8TiepTheoNamSection" style="display: none;" class="space-y-3">
                            <div id="phan8TiepTheoNamContent" class="space-y-3 text-sm"></div>
                        </div>
                        <div id="phan8TiepTheoThangSection" style="display: none;" class="space-y-3">
                            <div id="phan8TiepTheoThangContent" class="space-y-3 text-sm"></div>
                        </div>
                        <div id="phan8TiepTheoNgaySection" style="display: none;" class="space-y-3">
                            <div id="phan8TiepTheoNgayContent" class="space-y-3 text-sm"></div>
                        </div>
                        <div id="phan8TiepTheoGioSection" style="display: none;" class="space-y-3">
                            <div id="phan8TiepTheoGioContent" class="space-y-3 text-sm"></div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- PHẦN 8 – III. DỰ BÁO CÁC KHÍA CẠNH CUỘC SỐNG -->
            <div id="phan8DuBaoKhiaCanhSection" data-phan8-quyen="2" class="quyen-section mt-6 mb-6" style="display: none;">
                <h3 class="text-xl font-bold mb-4 form-title">
                    <i class="fas fa-layer-group mr-2"></i>
                    PHẦN 8: III. DỰ BÁO CÁC KHÍA CẠNH CUỘC SỐNG
                </h3>
                <div class="bg-white rounded-lg shadow-lg p-6 space-y-4">
                    <div id="phan8DuBaoKhiaCanhContent" class="space-y-4 text-sm"></div>
                </div>
            </div>

            <!-- PHẦN 8 – IV. NHỮNG NĂM CẦN CHÚ Ý -->
            <div id="phan8NhungNamChuYSection" data-phan8-quyen="1" class="quyen-section mt-6 mb-6" style="display: none;">
                <h3 class="text-xl font-bold mb-4 form-title">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    PHẦN 8: IV. NHỮNG NĂM CẦN CHÚ Ý
                </h3>
                <div class="bg-white rounded-lg shadow-lg p-6 space-y-6">
                    <div id="phan8NhungNamChuYGhiChu" class="text-xs text-gray-600 space-y-2 border border-gray-200 rounded-lg p-3 bg-gray-50" style="display: none;"></div>
                    <div id="phan8NhungNamChuYContent" class="space-y-8 text-sm"></div>
                </div>
            </div>

            <!-- Phần chấm điểm SIM khách hàng -->
            <div id="diemSimSection" class="quyen-section quyen-shared mt-6 mb-6" style="display: none;">
                <h3 class="text-xl font-bold mb-4 form-title">
                    <i class="fas fa-star mr-2"></i>
                    CHẤM ĐIỂM SIM KHÁCH HÀNG
                </h3>
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Điểm số -->
                        <div class="text-center p-6 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl">
                            <i class="fas fa-award text-5xl text-indigo-600 mb-3"></i>
                            <h4 class="text-lg font-semibold text-gray-700 mb-2">Điểm số</h4>
                            <p id="diemSimScore" class="text-4xl font-bold text-indigo-600">0</p>
                            {{-- <p class="text-sm text-gray-600 mt-2">/100 điểm</p> --}}
                        </div>

                        <!-- Loại sim -->
                        <div class="text-center p-6 bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl">
                            <i class="fas fa-mobile-alt text-5xl text-purple-600 mb-3"></i>
                            <h4 class="text-lg font-semibold text-gray-700 mb-2">Loại sim</h4>
                            <p id="diemSimLoai" class="text-2xl font-bold text-purple-600">-</p>
                        </div>
                    </div>

                    <!-- Nút xem chi tiết -->
                    <div class="text-center mt-6">
                        <button id="btnXemChiTietQue"
                            class="btn-primary text-white px-8 py-3 rounded-lg font-semibold hover:shadow-lg transition-all">
                            <i class="fas fa-info-circle mr-2"></i>
                            Xem chi tiết quẻ gốc
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal chi tiết quẻ gốc -->
            <div id="modalQueGoc"
                class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
                <div class="bg-white rounded-2xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                    <div class="sticky top-0 bg-white border-b p-6 flex justify-between items-center">
                        <h3 class="text-2xl font-bold form-title">
                            <i class="fas fa-yin-yang mr-2"></i>
                            <span id="modalQueGocTitle">Chi tiết quẻ gốc</span>
                        </h3>
                        <button id="btnCloseModal" class="text-gray-500 hover:text-gray-700 text-2xl">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div id="modalQueGocContent" class="p-6 prose max-w-none">
                        <!-- Nội dung sẽ được load ở đây -->
                    </div>
                </div>
            </div>

            <!-- Danh sách Sim hợp mệnh -->
            <div id="simsSection" class="quyen-section quyen-shared mt-6 mb-6" style="display: none;">
                <h3 class="text-xl font-bold mb-4 form-title">
                    <i class="fas fa-mobile-alt mr-2"></i>
                    SIM HỢP MỆNH
                </h3>

                <!-- Bộ lọc và tìm kiếm -->
                <div class="bg-white rounded-lg shadow-md p-4 mb-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Tìm kiếm số điện thoại -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-search mr-2"></i>
                                Tìm kiếm số điện thoại
                            </label>
                            <input type="text" id="simSearchInput" placeholder="Nhập số điện thoại cần tìm..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        </div>

                        <!-- Lọc theo nhà mạng -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-filter mr-2"></i>
                                Lọc theo nhà mạng
                            </label>
                            <select id="telcoFilter"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                                <option value="">Tất cả nhà mạng</option>
                                <option value="mobifone">Mobifone</option>
                                <option value="viettel">Viettel</option>
                                <option value="vinaphone">Vinaphone</option>
                                <option value="vietnamobile">Vietnamobile</option>
                                <option value="gmobile">Gmobile</option>
                            </select>
                        </div>
                    </div>

                    <!-- Thông tin kết quả -->
                    <div class="mt-3 flex items-center justify-between">
                        <span id="simsCount" class="text-sm text-gray-600">
                            <i class="fas fa-info-circle mr-1"></i>
                            Đang tải...
                        </span>
                        <button id="resetFilters"
                            class="text-sm text-blue-600 hover:text-blue-800 font-semibold transition-colors hidden">
                            <i class="fas fa-redo mr-1"></i>
                            Xóa bộ lọc
                        </button>
                    </div>
                </div>

                <!-- Loading indicator -->
                <div id="simsLoading" class="text-center py-8 hidden">
                    <i class="fas fa-spinner fa-spin text-3xl text-blue-500 mb-2"></i>
                    <p class="text-gray-600">Đang tải danh sách sim...</p>
                </div>

                <!-- Danh sách sim -->
                <div id="simsContent" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"></div>

                <!-- Thông báo không tìm thấy -->
                <div id="simsEmpty" class="text-center py-12 hidden">
                    <i class="fas fa-search text-5xl text-gray-300 mb-4"></i>
                    <p class="text-gray-600 text-lg">Không tìm thấy sim nào phù hợp</p>
                    <p class="text-gray-500 text-sm mt-2">Vui lòng thử lại với bộ lọc khác</p>
                </div>
            </div>

            <!-- Bảng tinh an -->
            {{-- <table class="result-table" style="margin-top: 20px;">
                <thead>
                    <tr>
                        <th>MỆNH</th>
                        <th>SAO</th>
                        <th>ĐIỂM</th>
                    </tr>
                </thead>
                <tbody id="tinhan-body">
                </tbody>
            </table> --}}

            <div>
                <h3 class="text-xl font-bold mt-6 mb-4 form-title">HỶ THẦN KỴ THẦN</h3>
                <div id="dungthannhatchu" class="text-gray-700"></div>
            </div>

            <!-- Phần hiển thị thông tin Dụng Thần -->
            <div id="dungthanDetailSection" class="quyen-section quyen-shared dung-than-section">
                <h3 class="text-xl font-bold mb-4 form-title">GIẢI PHÁP CÂN BẰNG</h3>
                <div id="dungthanDetailContent"></div>
            </div>

            <!-- Phần Tổng Quan Tính Cách -->
            <div id="tongquantinhcachSection" class="quyen-section quyen-shared dung-than-section">
                <h3 class="text-xl font-bold mb-4 form-title">TỔNG QUAN TÍNH CÁCH</h3>
                <div id="tongquantinhcachContent"></div>
            </div>


            <script>
                // Biến global để lưu danh sách sim gốc
                let allSims = [];
                let currentMenh = '';

                const API_CLIENT_TIMEOUT_MS = {{ (int) config('api.client_timeout_ms', 30000) }};
                const PHAN5_KEYWORD_FRAME_URL = @json(\App\Services\Phan5AssetService::publicUrl('resources/views/pdfs/phan-5/anh-tu-khoa-frame.png'));

                function resolveApiErrorMessage(xhr, status) {
                    if (status === 'timeout') {
                        return 'Yêu cầu quá thời gian chờ. Vui lòng thử lại sau.';
                    }
                    if (xhr && (xhr.status === 504 || xhr.status === 408)) {
                        return 'Máy chủ không phản hồi kịp thời (timeout). Vui lòng thử lại.';
                    }
                    if (xhr && xhr.responseJSON) {
                        if (xhr.responseJSON.error) {
                            return xhr.responseJSON.error;
                        }
                        if (xhr.responseJSON.message) {
                            return xhr.responseJSON.message;
                        }
                    }
                    if (xhr && xhr.responseText) {
                        try {
                            const parsed = JSON.parse(xhr.responseText);
                            if (parsed.error) {
                                return parsed.error;
                            }
                            if (parsed.message) {
                                return parsed.message;
                            }
                        } catch (e) {
                            /* ignore */
                        }
                    }
                    if (xhr && xhr.status === 0) {
                        return 'Không nhận được phản hồi từ máy chủ. Kiểm tra kết nối hoặc thử lại.';
                    }

                    return 'Có lỗi xảy ra khi gọi API. Vui lòng thử lại!';
                }

                $.ajaxSetup({
                    timeout: API_CLIENT_TIMEOUT_MS,
                });

                let activeQuyenCuon = 1;

                function isQuyenSectionAllowed($el) {
                    if (!$el.hasClass('quyen-section') || $el.hasClass('quyen-shared')) {
                        return true;
                    }
                    if ($el.attr('id') === 'phan9Section') {
                        const has9a = !!$el.data('phan9a-has-content');
                        const has9b = !!$el.data('phan9b-has-content');
                        if (activeQuyenCuon === 1) {
                            return has9a;
                        }
                        if (activeQuyenCuon === 2) {
                            return has9b;
                        }
                        return false;
                    }
                    const $panel = $el.closest('.quyen-panel');
                    if ($panel.length) {
                        return parseInt($panel.data('quyen'), 10) === activeQuyenCuon;
                    }
                    if ($el.closest('#phan8Group').length) {
                        const phan8Quyen = $el.data('phan8-quyen');
                        if (phan8Quyen !== undefined && phan8Quyen !== null && phan8Quyen !== '') {
                            return parseInt(phan8Quyen, 10) === activeQuyenCuon;
                        }
                        // #phan8NienVanSection: không có data-phan8-quyen — do refreshPhan8QuyenVisibility quản lý
                        return false;
                    }
                    return false;
                }

                function refreshQuyenSectionsVisibility() {
                    $('#quyenCuon1Panel').toggleClass('is-hidden', activeQuyenCuon !== 1);
                    $('#quyenCuon2Panel').toggleClass('is-hidden', activeQuyenCuon !== 2);
                    $('.quyen-section').not('.quyen-shared').each(function() {
                        const $el = $(this);
                        if ($el.closest('#phan8Group').length && !$el.data('phan8-quyen')) {
                            return;
                        }
                        if (isQuyenSectionAllowed($el)) {
                            if ($el.data('quyen-was-shown')) {
                                $el.show();
                            }
                        } else if ($el.is(':visible')) {
                            $el.hide();
                        }
                    });
                    refreshPhan8QuyenVisibility();
                    refreshPhan9QuyenVisibility();
                }

                function mountPhan9Section() {
                    const $phan9 = $('#phan9Section');
                    const $target = activeQuyenCuon === 2 ? $('#quyenCuon2Panel') : $('#quyenCuon1Panel');
                    const $phan8 = $('#phan8Group');
                    if (!$target.length || !$phan9.length) {
                        return;
                    }
                    if ($phan8.length && $phan8.parent()[0] === $target[0]) {
                        $phan8.after($phan9);
                    } else {
                        $target.append($phan9);
                    }
                }

                function mountPhan8Group() {
                    const $group = $('#phan8Group');
                    const $target = activeQuyenCuon === 2 ? $('#quyenCuon2Panel') : $('#quyenCuon1Panel');
                    if ($target.length) {
                        $target.append($group);
                    }
                    mountPhan9Section();
                    refreshPhan8QuyenVisibility();
                }

                /**
                 * Phân cuốn Phần 8:
                 * Cuốn 1 (8A) — Đại Vận, IV. Năm cần chú ý
                 * Cuốn 2 (8B) — Niên Vận tiếp theo, III. Dự báo khía cạnh
                 */
                function refreshPhan9QuyenVisibility() {
                    const isCuon1 = activeQuyenCuon === 1;
                    const isCuon2 = activeQuyenCuon === 2;
                    const $section = $('#phan9Section');

                    const has9a = !!$section.data('phan9a-has-content');
                    const has9b = !!$section.data('phan9b-has-content');
                    const hasAny = has9a || has9b;

                    if (!$section.data('quyen-was-shown') || !hasAny) {
                        return;
                    }

                    mountPhan9Section();

                    const showForCuon = (isCuon1 && has9a) || (isCuon2 && has9b);

                    $('#phan9aBlock').toggle(isCuon1 && has9a);
                    $('#phan9bBlock').toggle(isCuon2 && has9b);

                    $('#phan9SharedCovers').toggle(showForCuon);
                    if (showForCuon) {
                        $('#phan9CoverImg').attr(
                            'src',
                            isCuon1
                                ? @json(asset('images/phan-9/bia-phan-9a.png'))
                                : @json(asset('images/phan-9/bia-phan-9b.png'))
                        );
                    }

                    if (showForCuon && isQuyenSectionAllowed($section)) {
                        $section.show();
                    } else if (!showForCuon) {
                        $section.hide();
                    }
                }

                function refreshPhan8QuyenVisibility() {
                    const isCuon1 = activeQuyenCuon === 1;
                    const isCuon2 = activeQuyenCuon === 2;

                    function togglePhan8Section($el, allowed) {
                        if (!$el.data('quyen-was-shown')) {
                            return;
                        }
                        $el.toggle(allowed);
                    }

                    togglePhan8Section($('#phan8DaiVanSection'), isCuon1);
                    togglePhan8Section($('#phan8NhungNamChuYSection'), isCuon1);
                    togglePhan8Section($('#phan8DuBaoKhiaCanhSection'), isCuon2);

                    const $nvSection = $('#phan8NienVanSection');
                    if ($nvSection.data('quyen-was-shown')) {
                        const $yNghia = $('#phan8NienVanYNghia');
                        if ($yNghia.data('has-content')) {
                            $yNghia.toggle(isCuon2);
                        }

                        const $tiepTheo = $('#phan8TiepTheoSection');
                        if ($tiepTheo.data('has-content')) {
                            $tiepTheo.toggle(isCuon2);
                        }

                        const anyNv = isCuon2 && ($yNghia.is(':visible') || $tiepTheo.is(':visible'));
                        $nvSection.toggle(anyNv);
                    }
                }

                function initPhan8Group() {
                    const $phan8Blocks = $('#phan8DaiVanSection, #phan8NienVanSection, #phan8DuBaoKhiaCanhSection, #phan8NhungNamChuYSection');
                    $('#phan8Group').append($phan8Blocks);
                    mountPhan8Group();
                }

                function switchQuyenCuon(quyen) {
                    activeQuyenCuon = quyen === 2 ? 2 : 1;
                    $('#quyenTabBar .quyen-tab-btn')
                        .removeClass('active')
                        .attr('aria-selected', 'false')
                        .filter('[data-quyen="' + activeQuyenCuon + '"]')
                        .addClass('active')
                        .attr('aria-selected', 'true');
                    mountPhan8Group();
                    refreshQuyenSectionsVisibility();
                    refreshPhan9QuyenVisibility();
                }

                (function patchQuyenSectionShow() {
                    const origShow = $.fn.show;
                    $.fn.show = function() {
                        return this.each(function() {
                            const $el = $(this);
                            if ($el.hasClass('quyen-section') && !$el.hasClass('quyen-shared')) {
                                $el.data('quyen-was-shown', true);
                                if (!isQuyenSectionAllowed($el)) {
                                    return;
                                }
                            }
                            origShow.call($el);
                        });
                    };
                })();

                $(document).ready(function() {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    const PDF_STATUS_POLL_MS = 3000;

                    let currentPdfExportId = null;
                    let pdfStatusPollTimer = null;
                    let pdfQueueStartedAt = null;
                    let pdfWorkerHintShown = false;
                    let lastPdfQueuePayload = null;
                    let pdfStatusSnapshot = {
                        quyen_1: { status: 'pending' },
                        quyen_2: { status: 'pending' }
                    };

                    initPhan8Group();

                    $('#quyenTabBar').on('click', '.quyen-tab-btn', function() {
                        switchQuyenCuon(parseInt($(this).data('quyen'), 10));
                    });

                    // Xử lý scroll indicator cho table
                    $('.table-wrapper').on('scroll', function() {
                        $(this).addClass('scrolled');
                    });

                    // Xử lý submit form
                    $('#tutuForm').on('submit', function(e) {
                        e.preventDefault();

                        // Hiển thị loading
                        const $submitBtn = $('#submitBtn');
                        const originalHtml = $submitBtn.html();

                        $submitBtn.prop('disabled', true);
                        $submitBtn.addClass('loading');
                        $submitBtn.html('<i class="fas fa-spinner mr-2"></i>ĐANG XỬ LÝ...');

                        // Lấy dữ liệu form
                        const formData = $(this).serializeArray();
                        const params = {};
                        const isUnknowBirthtime = $('#unknowBirthtime').is(':checked');

                        $.each(formData, function() {
                            if (this.name !== 'name' && this.name !== 'uknow_birthdate') {
                                params[this.name] = this.value;
                            }
                        });

                        // Nếu không rõ giờ sinh, xóa h và minute, thêm flag uknow_birthdate = 1
                        if (isUnknowBirthtime) {
                            delete params.h;
                            delete params.minute;
                            params.uknow_birthdate = 1;
                        } else {
                            params.uknow_birthdate = 0;
                        }

                        console.log('API params:', params);

                        // Gọi API
                        callBaziAPI(params, $submitBtn, originalHtml);
                    });

                    // Format số điện thoại
                    $('input[name="phone"]').on('input', function() {
                        let value = $(this).val().replace(/\D/g, '');
                        if (value.length > 10) {
                            value = value.slice(0, 10);
                        }
                        $(this).val(value);
                    });

                    // Xử lý checkbox "Không rõ giờ sinh"
                    $('#unknowBirthtime').on('change', function() {
                        const isChecked = $(this).is(':checked');
                        const $hourInput = $('#hourInput');
                        const $minuteInput = $('#minuteInput');

                        if (isChecked) {
                            // Disable và clear giá trị của giờ và phút
                            $hourInput.prop('required', false).prop('disabled', true).val('');
                            $minuteInput.prop('required', false).prop('disabled', true).val('');
                            $hourInput.parent().parent().addClass('opacity-50');
                            $minuteInput.parent().parent().addClass('opacity-50');
                        } else {
                            // Enable lại giờ và phút
                            $hourInput.prop('required', true).prop('disabled', false);
                            $minuteInput.prop('required', true).prop('disabled', false);
                            $hourInput.parent().parent().removeClass('opacity-50');
                            $minuteInput.parent().parent().removeClass('opacity-50');
                        }
                    });

                    // Validation cho ngày tháng năm
                    $('input[name="d"]').on('change', function() {
                        const day = parseInt($(this).val());
                        if (day < 1 || day > 31) {
                            showNotification('Ngày sinh phải từ 1 đến 31', 'error');
                            $(this).val('');
                        }
                    });

                    $('input[name="m"]').on('change', function() {
                        const month = parseInt($(this).val());
                        if (month < 1 || month > 12) {
                            showNotification('Tháng sinh phải từ 1 đến 12', 'error');
                            $(this).val('');
                        }
                    });

                    $('input[name="h"]').on('change', function() {
                        const hour = parseInt($(this).val());
                        if (hour < 0 || hour > 23) {
                            showNotification('Giờ sinh phải từ 0 đến 23', 'error');
                            $(this).val('');
                        }
                    });

                    function buildPdfQueuePayload(params, result, phan2Data) {
                        const payload = Object.assign({}, params);
                        payload.full_name = payload.full_name || $('input[name="full_name"]').val() || '';
                        payload.address = payload.address || $('input[name="address"]').val() || '';
                        payload.gender = payload.g === 'female' ? 'Nữ' : 'Nam';

                        const minute = String(payload.minute || '00').padStart(2, '0');
                        if (payload.h === undefined || payload.h === null || payload.h === '') {
                            payload.birth_date = `ngày ${String(payload.d).padStart(2, '0')} thg ${String(payload.m).padStart(2, '0')}, ${payload.y}`;
                        } else {
                            payload.birth_date = `${String(payload.h).padStart(2, '0')}:${minute} – ngày ${String(payload.d).padStart(2, '0')} thg ${String(payload.m).padStart(2, '0')}, ${payload.y}`;
                        }

                        if (result) {
                            payload.chat_luong_thap_than = result.chat_luong_thap_than || [];
                            payload.bieu_do_ngu_hanh = result.bieu_do_ngu_hanh || [];
                            payload.ngu_hanh_dong = result.ngu_hanh_dong || [];
                            payload.phan_tram_nien_van = result.phan_tram_nien_van || [];
                            payload.hanh_noi_dung_nien_van = result.hanh_noi_dung_nien_van || [];
                        }

                        if (phan2Data && phan2Data.chi_so_bieu_do_cot) {
                            payload.chi_so_bieu_do_cot = phan2Data.chi_so_bieu_do_cot;
                        }

                        const order = [
                            ['year', 'Năm'],
                            ['month', 'Tháng'],
                            ['day', 'Ngày'],
                            ['hour', 'Giờ']
                        ];
                        const batTuParts = [];
                        if (result && result.bat_tu) {
                            order.forEach(function(item) {
                                const key = item[0];
                                const label = item[1];
                                const pillar = result.bat_tu[key];
                                if (!pillar) return;
                                if (key === 'hour' && (payload.h === undefined || payload.h === null || payload.h === '')) {
                                    return;
                                }
                                const can = pillar.can && pillar.can.thien_can ? pillar.can.thien_can : '';
                                const chi = pillar.chi && pillar.chi.dia_chi ? pillar.chi.dia_chi : '';
                                if (can || chi) {
                                    batTuParts.push(`${label} ${can} ${chi}`.trim());
                                }
                            });
                        }
                        payload.bat_tu = batTuParts.join(', ');

                        return payload;
                    }

                    function pdfStatusLabel(status) {
                        switch (status) {
                            case 'ready':
                                return 'sẵn sàng';
                            case 'processing':
                                return 'đang xử lý';
                            case 'failed':
                                return 'thất bại';
                            default:
                                return 'đang chờ';
                        }
                    }

                    function updatePdfStatusHint() {
                        const $hint = $('#pdfStatusHint');
                        if (!currentPdfExportId) {
                            $hint.addClass('hidden').text('');
                            return;
                        }

                        const q1 = pdfStatusSnapshot.quyen_1?.status || 'pending';
                        const q2 = pdfStatusSnapshot.quyen_2?.status || 'pending';
                        let html = `PDF Quyển 1: <strong>${pdfStatusLabel(q1)}</strong> · PDF Quyển 2: <strong>${pdfStatusLabel(q2)}</strong>`;

                        const stillWaiting = function(status) {
                            return status === 'pending' || status === 'processing';
                        };
                        if (
                            pdfQueueStartedAt &&
                            !pdfWorkerHintShown &&
                            Date.now() - pdfQueueStartedAt > 60000 &&
                            (stillWaiting(q1) || stillWaiting(q2))
                        ) {
                            pdfWorkerHintShown = true;
                            html += '<br><span class="text-amber-700">PDF vẫn đang chờ. Hãy chạy queue worker: <code>php artisan pdf:queue-work</code></span>';
                        }

                        $hint.removeClass('hidden').html(html);
                    }

                    function setPdfButtonLoading($btn, loading) {
                        const $label = $btn.find('.btn-label');
                        if (!$label.data('default-text')) {
                            $label.data('default-text', $label.text());
                        }
                        if (loading) {
                            $btn.prop('disabled', true).addClass('loading');
                            $label.html('<i class="fas fa-spinner fa-spin mr-1"></i> Đang chuẩn bị PDF...');
                        } else {
                            $btn.prop('disabled', false).removeClass('loading');
                            $label.text($label.data('default-text'));
                        }
                    }

                    function pollPdfStatus() {
                        if (!currentPdfExportId) return;

                        $.getJSON(`/api/la-so/pdf/status/${currentPdfExportId}`)
                            .done(function(res) {
                                pdfStatusSnapshot = {
                                    quyen_1: res.quyen_1 || { status: 'pending' },
                                    quyen_2: res.quyen_2 || { status: 'pending' }
                                };
                                updatePdfStatusHint();
                            });
                    }

                    function startPdfStatusPolling() {
                        if (pdfStatusPollTimer) {
                            clearInterval(pdfStatusPollTimer);
                        }
                        pdfQueueStartedAt = Date.now();
                        pdfWorkerHintShown = false;
                        pollPdfStatus();
                        pdfStatusPollTimer = setInterval(pollPdfStatus, PDF_STATUS_POLL_MS);
                    }

                    function waitForPdfReady(quyen, timeoutMs) {
                        return new Promise(function(resolve, reject) {
                            const started = Date.now();
                            const key = quyen === 1 ? 'quyen_1' : 'quyen_2';

                            function check() {
                                if (!currentPdfExportId) {
                                    reject(new Error('Chưa có export_id'));
                                    return;
                                }

                                const status = pdfStatusSnapshot[key]?.status || 'pending';

                                if (status === 'ready') {
                                    resolve(true);
                                    return;
                                }
                                if (status === 'failed') {
                                    reject(new Error(pdfStatusSnapshot[key]?.error || 'Tạo PDF thất bại'));
                                    return;
                                }
                                if (Date.now() - started > timeoutMs) {
                                    reject(new Error('PDF đang xử lý quá lâu. Vui lòng chạy queue worker và thử lại.'));
                                    return;
                                }
                                setTimeout(check, PDF_STATUS_POLL_MS);
                            }

                            check();
                        });
                    }

                    function downloadPdfQuyen(quyen) {
                        if (!currentPdfExportId) {
                            showNotification('Chưa có PDF để tải. Vui lòng lấy lá số trước.', 'error');
                            return;
                        }

                        const statusKey = quyen === 1 ? 'quyen_1' : 'quyen_2';
                        if (pdfStatusSnapshot[statusKey]?.status === 'failed') {
                            if (lastPdfQueuePayload) {
                                showNotification('PDF thất bại. Đang thử tạo lại...', 'error');
                                queuePdfExport(lastPdfQueuePayload);
                            } else {
                                showNotification(pdfStatusSnapshot[statusKey]?.error || 'Tạo PDF thất bại', 'error');
                            }
                            return;
                        }

                        const $btn = quyen === 1 ? $('#btnDownloadPdfQ1') : $('#btnDownloadPdfQ2');
                        setPdfButtonLoading($btn, true);

                        waitForPdfReady(quyen, 180000)
                            .then(function() {
                                window.location.href = `/api/la-so/pdf/download/${currentPdfExportId}/${quyen}`;
                            })
                            .catch(function(err) {
                                showNotification(err.message || 'Không thể tải PDF', 'error');
                            })
                            .finally(function() {
                                setPdfButtonLoading($btn, false);
                            });
                    }

                    function enablePdfActionsAfterPhan2(phan2Data, params, result) {
                        queuePdfExport(buildPdfQueuePayload(params, result, phan2Data));
                    }

                    function queuePdfExport(payload) {
                        lastPdfQueuePayload = payload;
                        $('#pdfStatusHint').removeClass('hidden').text('Đang xếp hàng tạo PDF Quyển 1 và Quyển 2...');

                        $.ajax({
                            url: '/api/la-so/pdf/queue',
                            method: 'POST',
                            contentType: 'application/json',
                            data: JSON.stringify(payload),
                            dataType: 'json'
                        }).done(function(res) {
                            currentPdfExportId = res.export_id;
                            pdfStatusSnapshot = {
                                quyen_1: { status: res.q1_status || 'pending' },
                                quyen_2: { status: res.q2_status || 'pending' }
                            };
                            updatePdfStatusHint();
                            startPdfStatusPolling();
                            $('#btnDownloadPdfQ1, #btnDownloadPdfQ2').prop('disabled', false);
                        }).fail(function(xhr) {
                            showNotification(resolveApiErrorMessage(xhr, 'error'), 'error');
                            $('#pdfStatusHint').text('Không thể xếp hàng tạo PDF.');
                        });
                    }

                    $('#btnDownloadPdfQ1').on('click', function() {
                        downloadPdfQuyen(1);
                    });

                    $('#btnDownloadPdfQ2').on('click', function() {
                        downloadPdfQuyen(2);
                    });

                    // Hàm gọi API
                    async function callBaziAPI(params, $submitBtn, originalHtml) {
                        const apiUrl = '/api/bazi/calc?' + $.param(params);

                        await $.ajax({
                            url: apiUrl,
                            method: 'GET',
                            dataType: 'json',
                            success: function(response) {
                                console.log('API Response:', response);

                                // Kiểm tra nếu response có error từ backend
                                if (response.error) {
                                    // Khôi phục trạng thái nút
                                    $submitBtn.prop('disabled', false);
                                    $submitBtn.removeClass('loading');
                                    $submitBtn.html(originalHtml);

                                    // Hiển thị lỗi từ backend
                                    showNotification(response.error, 'error');
                                    return;
                                }

                                // Hiển thị kết quả (xếp hàng PDF sau khi PHẦN 2 load xong)
                                displayResults(params, response, function(phan2Data) {
                                    enablePdfActionsAfterPhan2(phan2Data, params, response);
                                });

                                // Khôi phục trạng thái nút
                                $submitBtn.prop('disabled', false);
                                $submitBtn.removeClass('loading');
                                $submitBtn.html(originalHtml);

                                showNotification('Lá số tứ trụ đã được tạo thành công!', 'success');
                            },
                            error: function(xhr, status) {
                                console.error('API Error:', status, xhr);

                                $submitBtn.prop('disabled', false);
                                $submitBtn.removeClass('loading');
                                $submitBtn.html(originalHtml);

                                showNotification(resolveApiErrorMessage(xhr, status), 'error');
                            }
                        });
                    }

                    // Load Tổng quan các khía cạnh từ API /api/phan-5/tong-quan
                    function loadTongQuanKhiaCanh() {
                        const section = $('#tongQuanKhiaCanhSection');
                        const container = $('#tongQuanKhiaCanhContent');
                        $.ajax({
                            url: '/api/phan-5/tong-quan',
                            method: 'GET',
                            dataType: 'json',
                            success: function(res) {
                                if (!res.data || res.data.length === 0) {
                                    section.hide();
                                    return;
                                }
                                let html = '';
                                res.data.forEach(function(item) {
                                    html += '<div class="mb-4">';
                                    if (item.title) {
                                        html +=
                                            `<h4 class="font-semibold text-indigo-700 mb-3">${item.title}</h4>`;
                                    }
                                    if (item.content) {
                                        html +=
                                            `<div class="text-gray-700 whitespace-pre-line text-sm">${item.content}</div>`;
                                    }
                                    html += '</div>';
                                });
                                container.html(html || '<p class="text-gray-500">Chưa có nội dung.</p>');
                                section.show();
                            },
                            error: function() {
                                section.hide();
                            }
                        });
                    }

                    // Load PHẦN 6: Mã 1 (Ý nghĩa tứ trụ) + Mã 2,3,4 (Dòng chảy năng lượng) từ API năng lượng trong lá số
                    function loadPhan6(params) {
                        const section = $('#phan6DongChaySection');
                        const ma1Content = $('#phan6Ma1Content');
                        const ma2Content = $('#phan6Ma2Content');
                        const ma3Content = $('#phan6Ma3Content');
                        const ma4Content = $('#phan6Ma4Content');
                        ma1Content.empty();
                        ma2Content.empty();
                        ma3Content.empty();
                        ma4Content.empty();
                        $('#phan6Ma2Container').hide();
                        $('#phan6Ma3Container').hide();
                        $('#phan6Ma4Container').hide();

                        $.ajax({
                            url: '/api/phan-6/nang-luong-trong-la-so',
                            method: 'GET',
                            dataType: 'json',
                            data: params,
                            error: function(xhr, status, err) {
                                console.error('loadPhan6 API error:', status, err);
                            },
                            success: function(res) {
                                const data = (res && res.data) ? res.data : (res || {});
                                const items = (data.y_nghia_tu_tru || []).filter(function(item) {
                                    const s = (item.slug || '').toLowerCase();
                                    return s.indexOf('la_so_bat_tu') < 0 && s.indexOf('lá_số_bát') < 0
                                        && s !== 'transition_phan8';
                                });
                                const laSoBatTu = data.la_so_bat_tu || null;
                                const transitionPhan8 = data.transition_phan8 || null;
                                const d = data.dong_chay || null;
                                const transitionBox = $('#phan6TransitionContainer');
                                const transitionContent = $('#phan6TransitionContent');
                                transitionBox.hide();
                                transitionContent.empty();

                                if (d) {
                                    section.show();
                                    section.css('display', 'block');
                                }

                                if (items.length > 0) {
                                    let html = '';
                                    items.forEach(function(item) {
                                        html += '<div class="border rounded-lg p-3 bg-gray-50 mb-3">';
                                        if (item.title) {
                                            html += '<h5 class="font-semibold text-indigo-700 mb-2">' +
                                                item.title + '</h5>';
                                        }
                                        if (item.content) {
                                            html += '<div class="text-gray-700">' +
                                                formatYNghiaTuTruContent(item.content) + '</div>';
                                        }
                                        html += '</div>';
                                    });
                                    if (laSoBatTu) {
                                        html += renderLaSoBatTuTable(laSoBatTu);
                                    }
                                    ma1Content.html(html);
                                    section.show();
                                } else if (laSoBatTu) {
                                    ma1Content.html(renderLaSoBatTuTable(laSoBatTu));
                                    section.show();
                                }

                                // Mã 2–4: mẫu Excel — tiêu đề → đoạn mở đầu → ảnh → luận giải động (Thiên Can / Địa Chi)
                                if (d && d.nam_thang) {
                                    ma2Content.html(renderPhan6MaDongChay(d.nam_thang,
                                        'II. SỰ TƯƠNG TÁC GIỮA TRỤ NĂM VÀ TRỤ THÁNG',
                                        'Thiên Can – Trụ Năm – Trụ Tháng', 'Địa Chi – Trụ Năm – Trụ Tháng'));
                                    $('#phan6Ma2Container').show();
                                    section.show();
                                }
                                if (d && d.thang_ngay) {
                                    ma3Content.html(renderPhan6MaDongChay(d.thang_ngay,
                                        'III. SỰ TƯƠNG TÁC GIỮA TRỤ THÁNG VÀ TRỤ NGÀY',
                                        'Thiên Can – Trụ Tháng – Trụ Ngày', 'Địa Chi – Trụ Tháng – Trụ Ngày'));
                                    $('#phan6Ma3Container').show();
                                    section.show();
                                }
                                if (d && d.ngay_gio) {
                                    ma4Content.html(renderPhan6MaDongChay(d.ngay_gio,
                                        'IV. SỰ TƯƠNG TÁC GIỮA TRỤ NGÀY VÀ TRỤ GIỜ',
                                        'Thiên Can – Trụ Ngày – Trụ Giờ', 'Địa Chi – Trụ Ngày – Trụ Giờ'));
                                    $('#phan6Ma4Container').show();
                                    section.show();
                                }
                                if (transitionPhan8 && transitionPhan8.content) {
                                    let tHtml = '<div class="border rounded-lg p-4 bg-amber-50 border-amber-200">';
                                    if (transitionPhan8.title) {
                                        tHtml += '<h5 class="font-semibold text-amber-800 mb-2">' +
                                            escapeHtml(transitionPhan8.title) + '</h5>';
                                    }
                                    tHtml += '<div class="leading-relaxed whitespace-pre-line text-gray-700">' +
                                        escapeHtml(transitionPhan8.content) + '</div>';
                                    tHtml += '</div>';
                                    transitionContent.html(tHtml);
                                    transitionBox.show();
                                    section.show();
                                }
                            }
                        });
                    }

                    function normalizeSubLabelLine(line) {
                        line = String(line || '')
                            .replace(/\u00A0|\u200B/g, ' ')
                            .trim()
                            .replace(/^[-–—•*]+\s*/, '');
                        return line.trim();
                    }

                    function splitVeColonPrefix(line) {
                        line = String(line || '').trim();
                        const m = line.match(/^(Về\s+[^:]+):\s*(.+)$/u);
                        if (!m) return null;
                        const label = m[1].trim();
                        const body = m[2].trim();
                        if (!label || !body) return null;
                        return { label: label, body: body };
                    }

                    function isPhan9SubLabelLine(line) {
                        line = normalizeSubLabelLine(line);
                        if (!line || splitVeColonPrefix(line)) return false;
                        return /^[a-z]\.\s+/i.test(line)
                            || /^\d+\.\s+/.test(line)
                            || /^Về\s+/u.test(line);
                    }

                    function formatPhan9TextBlock(text) {
                        if (!text) return '';
                        const lines = String(text).split(/\r?\n/);
                        let html = '';
                        lines.forEach(function(line) {
                            line = line.trim();
                            if (!line) return;
                            const veSplit = splitVeColonPrefix(line);
                            if (veSplit) {
                                html += '<p class="mb-3 leading-relaxed text-gray-700">' +
                                    '<span class="phan9-sub-label">' + escapeHtml(veSplit.label) + ':</span> ' +
                                    escapeHtml(veSplit.body) + '</p>';
                            } else if (isPhan9SubLabelLine(line)) {
                                html += '<p class="mb-2 phan9-sub-label leading-relaxed">' +
                                    escapeHtml(line) + '</p>';
                            } else {
                                html += '<p class="mb-3 leading-relaxed text-gray-700">' +
                                    escapeHtml(line) + '</p>';
                            }
                        });
                        return html;
                    }

                    function renderPhan9Paragraphs(paragraphs) {
                        if (!paragraphs || !paragraphs.length) return '';
                        return paragraphs.map(function(p) {
                            if (/\r?\n/.test(String(p)) || isPhan9SubLabelLine(p)) {
                                return formatPhan9TextBlock(p);
                            }
                            return '<p class="mb-3 leading-relaxed text-gray-700">' + escapeHtml(p) + '</p>';
                        }).join('');
                    }

                    const PHAN9B_BEAM_THEMES = {
                        moc: { color: '#3a9e20', colorLight: '#a8dc6a', trackBg: '#d8f0b4', badgeUp: '#2e7d12', badgeDown: '#c03018' },
                        hoa: { color: '#d63b1c', colorLight: '#f5a088', trackBg: '#fcd8d0', badgeUp: '#c03018', badgeDown: '#c03018' },
                        tho: { color: '#c98020', colorLight: '#f0c060', trackBg: '#f5e0b0', badgeUp: '#a06010', badgeDown: '#c03018' },
                        kim: { color: '#606060', colorLight: '#b8b8b4', trackBg: '#dcdcd8', badgeUp: '#4a4a46', badgeDown: '#c03018' },
                        thuy: { color: '#1460a5', colorLight: '#80bce8', trackBg: '#cce4f8', badgeUp: '#0d4a8a', badgeDown: '#c03018' }
                    };

                    const PHAN9B_THUMB_R_BIG = 13;
                    const PHAN9B_THUMB_R_SMALL = 6;
                    const PHAN9B_TRACK_H = 7;

                    function drawPhan9bBeamSmallThumb(ctx, x, cy, item) {
                        ctx.beginPath();
                        ctx.arc(x, cy, PHAN9B_THUMB_R_SMALL, 0, Math.PI * 2);
                        ctx.fillStyle = '#ffffff';
                        ctx.fill();
                        ctx.strokeStyle = item.color + '99';
                        ctx.lineWidth = 1.5;
                        ctx.stroke();
                        ctx.beginPath();
                        ctx.arc(x, cy, PHAN9B_THUMB_R_SMALL - 3, 0, Math.PI * 2);
                        ctx.fillStyle = item.color + '55';
                        ctx.fill();
                    }

                    function drawPhan9bBeamBigThumb(ctx, x, cy, item) {
                        ctx.beginPath();
                        ctx.arc(x, cy, PHAN9B_THUMB_R_BIG, 0, Math.PI * 2);
                        ctx.fillStyle = '#ffffff';
                        ctx.fill();
                        ctx.strokeStyle = item.color + '55';
                        ctx.lineWidth = 1.5;
                        ctx.stroke();
                        ctx.beginPath();
                        ctx.arc(x, cy, PHAN9B_THUMB_R_BIG - 4, 0, Math.PI * 2);
                        const gThumb = ctx.createRadialGradient(x - 2, cy - 2, 1, x, cy, PHAN9B_THUMB_R_BIG - 4);
                        gThumb.addColorStop(0, item.colorLight);
                        gThumb.addColorStop(1, item.color);
                        ctx.fillStyle = gThumb;
                        ctx.fill();
                    }

                    function drawPhan9bBeamSlider(canvas, item, W, H) {
                        const dpr = window.devicePixelRatio || 1;
                        canvas.width = Math.round(W * dpr);
                        canvas.height = Math.round(H * dpr);
                        canvas.style.width = W + 'px';
                        canvas.style.height = H + 'px';
                        const ctx = canvas.getContext('2d');
                        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

                        const cy = H / 2;
                        const PAD_L = PHAN9B_THUMB_R_BIG + 2;
                        const PAD_R = PHAN9B_THUMB_R_BIG + 2;
                        const trackW = W - PAD_L - PAD_R;
                        const xLeft = PAD_L;

                        const before = Math.max(0, Math.min(100, Number(item.before) || 0));
                        const after = Math.max(0, Math.min(100, Number(item.after) || 0));
                        const isStable = item.direction === 'stable' || before === after;
                        const isGiam = item.direction === 'giam';

                        const initX = xLeft + trackW * (before / 100);
                        const valX = xLeft + trackW * (after / 100);
                        const smallX = isGiam ? valX : initX;
                        const bigX = isGiam ? initX : valX;

                        ctx.clearRect(0, 0, W, H);
                        const trackY = cy - PHAN9B_TRACK_H / 2;

                        ctx.beginPath();
                        ctx.roundRect(xLeft, trackY, trackW, PHAN9B_TRACK_H, PHAN9B_TRACK_H / 2);
                        ctx.fillStyle = item.trackBg;
                        ctx.fill();

                        if (!isStable) {
                            const fillLeft = Math.min(initX, valX);
                            const fillW = Math.abs(valX - initX);
                            if (fillW > 0.5) {
                                ctx.beginPath();
                                ctx.roundRect(fillLeft, trackY, fillW, PHAN9B_TRACK_H, PHAN9B_TRACK_H / 2);
                                const gFill = ctx.createLinearGradient(fillLeft, 0, fillLeft + fillW, 0);
                                gFill.addColorStop(0, item.colorLight + '88');
                                gFill.addColorStop(1, item.color);
                                ctx.fillStyle = gFill;
                                ctx.fill();
                            }

                            const coneW = Math.min(Math.abs(bigX - smallX) * 0.85, trackW * 0.55);
                            const coneH = 22;
                            if (coneW > 4) {
                                ctx.save();
                                ctx.beginPath();
                                if (bigX >= smallX) {
                                    ctx.moveTo(bigX - coneW, cy - 2);
                                    ctx.lineTo(bigX - coneW, cy + 2);
                                    ctx.lineTo(bigX, cy + coneH / 2);
                                    ctx.lineTo(bigX, cy - coneH / 2);
                                    const gCone = ctx.createLinearGradient(bigX - coneW, 0, bigX, 0);
                                    gCone.addColorStop(0, item.color + '00');
                                    gCone.addColorStop(1, item.color + '66');
                                    ctx.fillStyle = gCone;
                                } else {
                                    ctx.moveTo(bigX + coneW, cy - 2);
                                    ctx.lineTo(bigX + coneW, cy + 2);
                                    ctx.lineTo(bigX, cy + coneH / 2);
                                    ctx.lineTo(bigX, cy - coneH / 2);
                                    const gCone = ctx.createLinearGradient(bigX, 0, bigX + coneW, 0);
                                    gCone.addColorStop(0, item.color + '66');
                                    gCone.addColorStop(1, item.color + '00');
                                    ctx.fillStyle = gCone;
                                }
                                ctx.closePath();
                                ctx.fill();
                                ctx.restore();
                            }

                            if (Math.abs(smallX - bigX) > 1) {
                                drawPhan9bBeamSmallThumb(ctx, smallX, cy, item);
                            }
                            drawPhan9bBeamBigThumb(ctx, bigX, cy, item);
                        } else {
                            drawPhan9bBeamBigThumb(ctx, initX, cy, item);
                        }
                    }

                    function initPhan9bBeamCharts(root) {
                        const $root = root ? $(root) : $('#phan9bContent');
                        $root.find('.phan9b-beam-chart').each(function() {
                            $(this).find('.phan9b-beam-row').each(function() {
                                const $row = $(this);
                                let item;
                                try {
                                    item = JSON.parse($row.attr('data-beam-item') || '{}');
                                } catch (e) {
                                    return;
                                }
                                const canvas = $row.find('canvas.phan9b-beam-canvas')[0];
                                const $ta = $row.find('.phan9b-beam-track-area');
                                if (!canvas || !$ta.length) return;

                                function redraw() {
                                    const W = $ta.innerWidth();
                                    if (W <= 0) return;
                                    drawPhan9bBeamSlider(canvas, item, W, 36);
                                }

                                redraw();
                                if (typeof ResizeObserver !== 'undefined') {
                                    const ro = new ResizeObserver(function() { redraw(); });
                                    ro.observe($ta[0]);
                                    $row.data('beam-ro', ro);
                                }
                            });
                        });
                    }

                    function renderPhan9bBeamChart(chartData) {
                        if (!chartData || !chartData.rows || !chartData.rows.length) {
                            return '<p class="text-gray-500 text-sm italic">Chưa có dữ liệu biểu đồ chuyển hóa.</p>';
                        }

                        let html = '<div class="phan9b-beam-chart">';

                        chartData.rows.forEach(function(row, idx) {
                            const theme = PHAN9B_BEAM_THEMES[row.slug] || PHAN9B_BEAM_THEMES.kim;
                            const before = Math.max(0, Math.min(100, Number(row.before) || 0));
                            const after = Math.max(0, Math.min(100, Number(row.after) || 0));
                            const dir = row.direction || 'stable';
                            const isTang = dir === 'tang';
                            const isGiam = dir === 'giam';

                            const delta = after - before;
                            const deltaAbs = Math.abs(Math.round(delta));

                            const item = Object.assign({
                                slug: row.slug,
                                before: before,
                                after: after,
                                direction: dir,
                                delta: Math.round(delta)
                            }, theme);

                            let badgeText = '— 0%';
                            let badgeColor = '#9ca3af';
                            if (isTang) {
                                badgeText = '↑ (Tăng) +' + deltaAbs + '%';
                                badgeColor = theme.badgeUp;
                            } else if (isGiam) {
                                badgeText = '↓ (Giảm) -' + deltaAbs + '%';
                                badgeColor = theme.badgeDown;
                            }

                            html += '<div class="phan9b-beam-row" data-beam-item="' +
                                escapeHtml(JSON.stringify(item)) + '">';
                            html += '<div class="phan9b-beam-lbl">';
                            html += '<img src="/images/ngu-hanh/' + escapeHtml(row.slug) +
                                '.svg" alt="' + escapeHtml(row.ten) + '">';
                            html += '</div>';
                            html += '<div class="phan9b-beam-track-area">';
                            html += '<canvas class="phan9b-beam-canvas"></canvas>';
                            html += '</div>';
                            html += '<div class="phan9b-beam-badge" style="color:' + badgeColor + '">' +
                                escapeHtml(badgeText) + '</div>';
                            html += '</div>';
                        });

                        html += '</div>';
                        return html;
                    }

                    function renderPhan9bNoiLucParagraphs(text) {
                        if (!text) return '';
                        return formatPhan9TextBlock(text);
                    }

                    function renderPhan9bContent(data) {
                        if (!data) {
                            return '<p class="text-gray-500">Chưa có dữ liệu PHẦN 9B.</p>';
                        }

                        let html = '';

                        if (data.than_trang_thai && data.than_trang_thai.label) {
                            html += '<div class="mb-4 p-3 bg-indigo-50 border border-indigo-100 rounded-lg text-indigo-900">' +
                                '<span class="font-semibold">Trạng thái Nhật Chủ:</span> ' +
                                escapeHtml(data.than_trang_thai.label) + '</div>';
                        }

                        if (data.ngu_hanh_yeu_nhat && data.ngu_hanh_yeu_nhat.ten) {
                            html += '<div class="mb-4 p-3 bg-orange-50 border border-orange-200 rounded-lg text-orange-900">' +
                                '<span class="font-semibold">Ngũ hành Bản Mệnh yếu nhất:</span> ' +
                                escapeHtml(data.ngu_hanh_yeu_nhat.ten) + ' — ' +
                                escapeHtml(String(data.ngu_hanh_yeu_nhat.phan_tram)) + '%</div>';
                        }

                        if (!data.noi_dung && (data.noi_luc || data.thap_than || data.ngoai_luc || data.hieu_qua)) {
                            html += '<div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-900">' +
                                'Chưa xác định được <strong>Thân Vượng / Thân Nhược</strong> — không thể chọn nội dung Mục I. ' +
                                'Kiểm tra bảng Hỷ Kỵ Thần (Can ngày + Chi tháng) hoặc Chất lượng Nhật Chủ (Phần 3).' +
                                '</div>';
                        }

                        const block = data.noi_dung;
                        if (block) {
                            html += '<div class="phan9-muc1-sheet mb-4">';
                            html += '<h4 class="text-lg font-bold mb-4 form-title">' +
                                escapeHtml(block.tieu_de || 'I. GIẢI PHÁP CÂN BẰNG') + '</h4>';

                            (block.sections || []).forEach(function(sec) {
                                if (sec.tieu_de) {
                                    html += '<h5 class="text-base phan9-sub-label mt-5 mb-3">' +
                                        escapeHtml(sec.tieu_de) + '</h5>';
                                }
                                (sec.doan || []).forEach(function(item) {
                                    const text = item.noi_dung || '';
                                    if (!text) return;
                                    if (item.is_hanh_dong) {
                                        html += '<p class="font-semibold text-indigo-800 mb-2 leading-relaxed">' +
                                            escapeHtml(text) + '</p>';
                                    } else {
                                        html += '<p class="mb-3 text-gray-700 leading-relaxed">' +
                                            escapeHtml(text) + '</p>';
                                    }
                                });
                            });

                            html += '</div>';
                        }

                        const nl = data.noi_luc;
                        const tt = data.thap_than;
                        if (nl || tt) {
                            html += '<div class="bg-white rounded-lg shadow-lg p-6 mb-4 border-l-4 border-amber-300">';
                            html += '<h4 class="text-lg font-bold mb-4 form-title">' +
                                escapeHtml((nl && nl.tieu_de) ? nl.tieu_de : 'II. NỘI LỰC TỰ THÂN') + '</h4>';

                            if (nl && nl.intro && nl.intro.length) {
                                html += '<div class="mb-5 space-y-3">' +
                                    renderPhan9Paragraphs(nl.intro) + '</div>';
                            }

                            if (nl && nl.muc) {
                                html += '<h5 class="text-base phan9-sub-label mb-4">' +
                                    escapeHtml(nl.muc) + '</h5>';
                            }

                            if (nl && nl.hanh) {
                                (nl.hanh || []).forEach(function(hanh) {
                                    html += '<div class="mb-6 pb-6 border-b border-gray-100 last:border-0">';
                                    if (hanh.ngu_hanh && hanh.ngu_hanh.ten) {
                                        html += '<h5 class="text-base font-bold text-indigo-800 mb-2">' +
                                            escapeHtml(hanh.ngu_hanh.ten) + '</h5>';
                                    }
                                    if (hanh.tieu_de_chinh) {
                                        html += '<p class="font-semibold text-gray-800 mb-3">' +
                                            escapeHtml(hanh.tieu_de_chinh) + '</p>';
                                    }
                                    (hanh.sections || []).forEach(function(sec) {
                                        if (sec.tieu_de) {
                                            html += '<h6 class="phan9-sub-label mt-4 mb-2">' +
                                                escapeHtml(sec.tieu_de) + '</h6>';
                                        }
                                        (sec.doan || []).forEach(function(para) {
                                            if (!para) return;
                                            html += renderPhan9bNoiLucParagraphs(para);
                                        });
                                    });
                                    html += '</div>';
                                });
                            }

                            if (tt) {
                                if (data.thap_than_cao_nhat_label) {
                                    html += '<div class="mb-4 p-3 bg-violet-50 border border-violet-200 rounded-lg text-violet-900">' +
                                        '<span class="font-semibold">Thập Thần bản mệnh cao nhất:</span> ' +
                                        escapeHtml(data.thap_than_cao_nhat_label) + '</div>';
                                }

                                if (tt.intro && tt.intro.length) {
                                    html += '<div class="mb-5 space-y-3">' +
                                        renderPhan9Paragraphs(tt.intro) + '</div>';
                                }

                                if (tt.muc) {
                                    html += '<h5 class="text-base phan9-sub-label mb-4 mt-6 pt-4 border-t border-gray-200">' +
                                        escapeHtml(tt.muc) + '</h5>';
                                }

                                (tt.thap_than || []).forEach(function(item) {
                                    const topClass = item.is_top
                                        ? 'border-violet-300 bg-violet-50/40'
                                        : 'border-gray-100';
                                    html += '<div class="mb-6 pb-6 border rounded-lg p-4 ' + topClass + '">';
                                    if (item.bo) {
                                        html += '<p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">' +
                                            escapeHtml(item.bo) + '</p>';
                                    }
                                    if (item.thap_than && item.thap_than.ten) {
                                        html += '<h5 class="text-base font-bold text-indigo-800 mb-1">' +
                                            escapeHtml(item.thap_than.ten);
                                        if (item.is_top) {
                                            html += ' <span class="text-xs font-semibold text-violet-700 bg-violet-100 px-2 py-0.5 rounded-full ml-1">Bản mệnh cao</span>';
                                        }
                                        html += '</h5>';
                                    }
                                    if (item.tagline) {
                                        html += '<p class="font-semibold text-gray-800 mb-3 italic">' +
                                            escapeHtml(item.tagline) + '</p>';
                                    }
                                    if (item.intro) {
                                        html += '<p class="mb-4 text-gray-700 leading-relaxed">' +
                                            escapeHtml(item.intro) + '</p>';
                                    }
                                    (item.sections || []).forEach(function(sec) {
                                        if (sec.tieu_de) {
                                            html += '<h6 class="phan9-sub-label mt-4 mb-2">' +
                                                escapeHtml(sec.tieu_de) + '</h6>';
                                        }
                                        (sec.doan || []).forEach(function(para) {
                                            if (!para) return;
                                            html += renderPhan9bNoiLucParagraphs(para);
                                        });
                                    });
                                    html += '</div>';
                                });
                            }

                            html += '</div>';
                        }

                        const nluc = data.ngoai_luc;
                        if (nluc) {
                            html += '<div class="bg-white rounded-lg shadow-lg p-6 mb-4 border-l-4 border-teal-400">';
                            html += '<h4 class="text-lg font-bold mb-2 form-title">' +
                                escapeHtml(nluc.tieu_de || 'III. NGOẠI LỰC - CÔNG CỤ HỖ TRỢ') + '</h4>';
                            if (nluc.subtitle) {
                                html += '<p class="text-sm text-gray-600 italic mb-4">' +
                                    escapeHtml(nluc.subtitle) + '</p>';
                            }
                            if (nluc.intro && nluc.intro.length) {
                                html += '<div class="mb-5 space-y-3">' +
                                    renderPhan9Paragraphs(nluc.intro) + '</div>';
                            }
                            (nluc.sections || []).forEach(function(sec) {
                                html += '<div class="mb-6 pb-5 border-b border-gray-100 last:border-0">';
                                if (sec.tieu_de) {
                                    html += '<h5 class="text-base phan9-sub-label mb-3">' +
                                        escapeHtml(sec.tieu_de) + '</h5>';
                                }
                                (sec.items || []).forEach(function(item) {
                                    if (!item) return;
                                    const isLabel = isPhan9SubLabelLine(item) || (
                                        /^[^:]{1,80}:/u.test(item) &&
                                        !/^Nếu bạn cần nạp/u.test(item)
                                    );
                                    if (isLabel) {
                                        html += '<p class="phan9-sub-label mt-3 mb-1 leading-relaxed">' +
                                            escapeHtml(item) + '</p>';
                                    } else if (/^Nếu bạn cần nạp/u.test(item) || /^Cần nạp/u.test(item)) {
                                        html += '<p class="text-gray-700 mb-2 leading-relaxed pl-3 border-l-2 border-teal-200">' +
                                            escapeHtml(item) + '</p>';
                                    } else {
                                        html += '<p class="text-gray-700 mb-2 leading-relaxed">' +
                                            escapeHtml(item) + '</p>';
                                    }
                                });
                                html += '</div>';
                            });
                            html += '</div>';
                        }

                        const hq = data.hieu_qua;
                        const chartData = data.ngu_hanh_chuyen_hoa_chart;
                        if (hq) {
                            html += '<div class="bg-white rounded-lg shadow-lg p-6 mb-4 border-l-4 border-rose-400">';
                            html += '<h4 class="text-lg font-bold mb-2 form-title">' +
                                escapeHtml(hq.tieu_de || 'IV. HIỆU QUẢ CHUYỂN HÓA') + '</h4>';
                            if (hq.subtitle) {
                                html += '<p class="text-sm text-gray-600 italic mb-4">' +
                                    escapeHtml(hq.subtitle) + '</p>';
                            }
                            (hq.sections || []).forEach(function(sec) {
                                html += '<div class="mb-6 pb-5 border-b border-gray-100 last:border-0">';
                                if (sec.tieu_de) {
                                    html += '<h5 class="text-base phan9-sub-label mb-3">' +
                                        escapeHtml(sec.tieu_de) + '</h5>';
                                }
                                if (sec.intro) {
                                    html += '<p class="mb-4 text-gray-700 leading-relaxed">' +
                                        escapeHtml(sec.intro) + '</p>';
                                }
                                (sec.items || []).forEach(function(item) {
                                    if (!item) return;
                                    if (item.type === 'chart') {
                                        html += renderPhan9bBeamChart(chartData);
                                        return;
                                    }
                                    const text = item.noi_dung || '';
                                    if (!text.trim()) return;
                                    const veSplit = splitVeColonPrefix(text);
                                    if (veSplit) {
                                        html += '<p class="text-gray-700 mb-2 leading-relaxed">' +
                                            '<span class="phan9-sub-label">' + escapeHtml(veSplit.label) + ':</span> ' +
                                            escapeHtml(veSplit.body) + '</p>';
                                    } else if (isPhan9SubLabelLine(text)) {
                                        html += '<p class="phan9-sub-label mt-3 mb-1 leading-relaxed">' +
                                            escapeHtml(text) + '</p>';
                                    } else {
                                        html += '<p class="text-gray-700 mb-2 leading-relaxed">' +
                                            escapeHtml(text) + '</p>';
                                    }
                                });
                                html += '</div>';
                            });
                            html += '</div>';
                        }

                        if (!html) {
                            return '<p class="text-gray-500">Chưa có dữ liệu PHẦN 9B.</p>';
                        }

                        return html;
                    }

                    function loadPhan9b(params, chatLuongThapThan, baziResult) {
                        const section = $('#phan9Section');
                        const block = $('#phan9bBlock');
                        const content = $('#phan9bContent');
                        const transitionBox = $('#phan9bTransitionContainer');
                        const transitionContent = $('#phan9bTransitionContent');

                        block.hide();
                        content.empty();
                        transitionBox.hide();
                        transitionContent.empty();
                        section.removeData('phan9b-has-content');

                        const apiParams = Object.assign({}, params);
                        if (chatLuongThapThan && chatLuongThapThan.length) {
                            apiParams.chat_luong_thap_than = JSON.stringify(chatLuongThapThan);
                        }
                        const nhd = baziResult && baziResult.ngu_hanh_dong ? baziResult.ngu_hanh_dong : null;
                        if (nhd && typeof nhd === 'object') {
                            ['kim', 'moc', 'thuy', 'hoa', 'tho'].forEach(function(key) {
                                if (nhd[key] !== undefined && nhd[key] !== null) {
                                    apiParams[key] = nhd[key];
                                }
                            });
                        }

                        $.ajax({
                            url: '/api/phan-9b/giai-phap-can-bang',
                            method: 'GET',
                            data: apiParams,
                            error: function(xhr, status, err) {
                                console.error('loadPhan9b API error:', status, err);
                            },
                            success: function(res) {
                                const data = (res && res.data) ? res.data : (res || {});
                                let shown = false;

                                if (data.noi_dung || data.noi_luc || data.thap_than || data.ngoai_luc || data.hieu_qua) {
                                    content.html(renderPhan9bContent(data));
                                    initPhan9bBeamCharts(content);
                                    shown = true;
                                }

                                if (shown) {
                                    section.data('phan9b-has-content', true);
                                    section.data('quyen-was-shown', true);
                                    refreshPhan9QuyenVisibility();
                                    refreshQuyenSectionsVisibility();
                                }
                            }
                        });
                    }

                    function renderPhan9Ma1(p1) {
                        if (!p1) return '<p class="text-gray-500">Chưa có dữ liệu Mã 1.</p>';
                        let html = '<h4 class="text-lg font-bold text-indigo-800 mb-4">' +
                            escapeHtml(p1.tieu_de || 'I. NỘI LỰC TỰ THÂN') + '</h4>';
                        if (p1.yeu_nhat) {
                            html += '<div class="mb-4 p-3 bg-indigo-50 border border-indigo-100 rounded-lg text-indigo-900">' +
                                '<span class="font-semibold">Ngũ hành yếu nhất (Bản Mệnh):</span> ' +
                                escapeHtml(p1.yeu_nhat.ten) + ' — ' +
                                escapeHtml(String(p1.yeu_nhat.phan_tram)) + '%</div>';
                        }
                        if (p1.intro && p1.intro.length) {
                            html += '<div class="mb-5">' + renderPhan9Paragraphs(p1.intro) + '</div>';
                        }
                        const hanh = p1.noi_dung_hanh;
                        if (hanh) {
                            html += '<div class="border-t border-gray-200 pt-4 mt-2">';
                            if (hanh.ngu_hanh && hanh.ngu_hanh.ten) {
                                html += '<h5 class="text-base font-bold text-gray-800 mb-2">' +
                                    escapeHtml(hanh.ngu_hanh.ten) + '</h5>';
                            }
                            if (hanh.tieu_de_chinh) {
                                html += '<p class="font-semibold text-gray-700 mb-3">' +
                                    escapeHtml(hanh.tieu_de_chinh) + '</p>';
                            }
                            (hanh.sections || []).forEach(function(sec) {
                                if (sec.tieu_de) {
                                    html += '<h6 class="phan9-sub-label mt-4 mb-2">' +
                                        escapeHtml(sec.tieu_de) + '</h6>';
                                }
                                if (sec.doan && sec.doan.length) {
                                    html += renderPhan9Paragraphs(sec.doan);
                                }
                            });
                            html += '</div>';
                        }
                        return html;
                    }

                    function renderPhan9Ma2(p2) {
                        if (!p2) return '<p class="text-gray-500">Chưa có dữ liệu Mã 2.</p>';
                        let html = '';
                        if (p2.tieu_de) {
                            html += '<h4 class="text-lg font-bold text-indigo-800 mb-4">' +
                                escapeHtml(p2.tieu_de) + '</h4>';
                        }
                        const paras = p2.paragraphs || [];
                        if (paras.length) {
                            html += renderPhan9Paragraphs(paras);
                        } else if (p2.noi_dung) {
                            html += '<div class="leading-relaxed whitespace-pre-line">' +
                                escapeHtml(p2.noi_dung) + '</div>';
                        }
                        return html;
                    }

                    function loadPhan9(params, baziResult) {
                        const section = $('#phan9Section');
                        const block = $('#phan9aBlock');
                        const ma1Box = $('#phan9Ma1Container');
                        const ma2Box = $('#phan9Ma2Container');
                        const ma1Content = $('#phan9Ma1Content');
                        const ma2Content = $('#phan9Ma2Content');
                        const transitionBox = $('#phan9TransitionContainer');
                        const transitionContent = $('#phan9TransitionContent');

                        block.hide();
                        ma1Box.hide();
                        ma2Box.hide();
                        transitionBox.hide();
                        ma1Content.empty();
                        ma2Content.empty();
                        transitionContent.empty();
                        section.removeData('phan9a-has-content');

                        const ajaxData = Object.assign({}, params);
                        const nhd = baziResult && baziResult.ngu_hanh_dong ? baziResult.ngu_hanh_dong : null;
                        if (nhd && typeof nhd === 'object') {
                            ['kim', 'moc', 'thuy', 'hoa', 'tho'].forEach(function(key) {
                                if (nhd[key] !== undefined && nhd[key] !== null) {
                                    ajaxData[key] = nhd[key];
                                }
                            });
                        }

                        $.ajax({
                            url: '/api/phan-9/giai-phap',
                            method: 'GET',
                            data: ajaxData,
                            error: function(xhr, status, err) {
                                console.error('loadPhan9 API error:', status, err);
                            },
                            success: function(res) {
                                const data = (res && res.data) ? res.data : (res || {});
                                let shown = false;

                                if (data.phan_1) {
                                    ma1Content.html(renderPhan9Ma1(data.phan_1));
                                    ma1Box.show();
                                    shown = true;
                                }

                                if (data.phan_2) {
                                    ma2Content.html(renderPhan9Ma2(data.phan_2));
                                    ma2Box.show();
                                    shown = true;
                                }

                                if (shown) {
                                    section.data('phan9a-has-content', true);
                                    section.data('quyen-was-shown', true);
                                    refreshPhan9QuyenVisibility();
                                    refreshQuyenSectionsVisibility();
                                }
                            }
                        });
                    }

                    // Load PHẦN 8: Đại Vận – Ý nghĩa + mối quan hệ TC/ĐC với 4 Trụ
                    function loadPhan8(params) {
                        const section = $('#phan8DaiVanSection');
                        const dvInfo  = $('#phan8CurrentDaiVan');
                        dvInfo.empty();
                        $('#phan8YNghiaContainer').hide();
                        $('#phan8NamSection,#phan8ThangSection,#phan8NgaySection,#phan8GioSection').hide();
                        $('#phan8NamContent,#phan8ThangContent,#phan8NgayContent,#phan8GioContent').empty();
                        $('#phan8NamGioiThieu,#phan8ThangGioiThieu,#phan8NgayGioiThieu,#phan8GioGioiThieu').hide();

                        $.ajax({
                            url: '/api/phan-8/dai-van',
                            method: 'GET',
                            dataType: 'json',
                            data: params,
                            error: function(xhr, status, err) {
                                console.error('loadPhan8 API error:', status, err);
                            },
                            success: function(res) {
                                const data = (res && res.data) ? res.data : null;
                                if (!data) return;

                                section.data('quyen-was-shown', true);
                                refreshPhan8QuyenVisibility();

                                // Thông tin Đại Vận hiện tại
                                const cdv = data.current_dai_van;
                                if (cdv) {
                                    dvInfo.html(
                                        '<strong>Đại Vận hiện tại:</strong> Tuổi ' + cdv.age + ' – ' +
                                        '<strong>Thiên Can:</strong> ' + (cdv.thien_can || '—') +
                                        ' (' + (cdv.thap_than_thien_can || '—') + ')' +
                                        ' &nbsp;|&nbsp; ' +
                                        '<strong>Địa Chi:</strong> ' + (cdv.dia_chi || '—') +
                                        ' (' + (cdv.thap_than_dia_chi || '—') + ')'
                                    );
                                } else {
                                    dvInfo.html('<em class="text-gray-500">Không tìm thấy Đại Vận hiện tại.</em>');
                                }

                                // 1. Ý nghĩa Đại Vận
                                $('#phan8YNghiaContainer').show();
                                if (data.y_nghia && data.y_nghia.noi_dung) {
                                    $('#phan8YNghiaContent').text(data.y_nghia.noi_dung);
                                } else {
                                    $('#phan8YNghiaContent').hide();
                                }

                                // Helper: render một Trụ (gioi_thieu + TC + DC blocks)
                                function renderTruPhan8(truData, sectionEl, gtEl, contentEl, labelTC, labelDC) {
                                    if (!truData) return;

                                    const gt = truData.gioi_thieu;
                                    if (gt && gt.noi_dung) {
                                        gtEl.text(gt.noi_dung).show();
                                    }

                                    const tcBlocks = Array.isArray(truData.thien_can) ? truData.thien_can : (truData.thien_can ? [truData.thien_can] : []);
                                    const dcBlocks = Array.isArray(truData.dia_chi)   ? truData.dia_chi   : (truData.dia_chi   ? [truData.dia_chi]   : []);
                                    let html = '';
                                    tcBlocks.forEach(function(block) {
                                        if (block && block.moi_quan_he) html += renderCodingLogicBlock(block, labelTC, false);
                                    });
                                    dcBlocks.forEach(function(block) {
                                        if (block && block.moi_quan_he) html += renderCodingLogicBlock(block, labelDC, true);
                                    });
                                    if (html) {
                                        contentEl.html(html);
                                    }

                                    // Hiện section nếu có intro text hoặc coding logic
                                    if ((gt && gt.noi_dung) || html) {
                                        sectionEl.show();
                                    }
                                }

                                renderTruPhan8(data.nam,   $('#phan8NamSection'),   $('#phan8NamGioiThieu'),   $('#phan8NamContent'),   'Thiên Can – Đại Vận – Trụ Năm',   'Địa Chi – Đại Vận – Trụ Năm');
                                renderTruPhan8(data.thang, $('#phan8ThangSection'), $('#phan8ThangGioiThieu'), $('#phan8ThangContent'), 'Thiên Can – Đại Vận – Trụ Tháng', 'Địa Chi – Đại Vận – Trụ Tháng');
                                renderTruPhan8(data.ngay,  $('#phan8NgaySection'),  $('#phan8NgayGioiThieu'),  $('#phan8NgayContent'),  'Thiên Can – Đại Vận – Trụ Ngày',  'Địa Chi – Đại Vận – Trụ Ngày');
                                renderTruPhan8(data.gio,   $('#phan8GioSection'),   $('#phan8GioGioiThieu'),   $('#phan8GioContent'),   'Thiên Can – Đại Vận – Trụ Giờ',   'Địa Chi – Đại Vận – Trụ Giờ');
                            }
                        });
                    }

                    // Load PHẦN 8: Niên Vận – 8A (cuốn 1: hiện tại) + 8B (cuốn 2: tiếp theo)
                    function loadPhan8NienVan(params) {
                        const section = $('#phan8NienVanSection');
                        $('#phan8NienVanYNghia').hide().empty();
                        $('#phan8HienTaiSection,#phan8TiepTheoSection').hide();
                        $('#phan8HienTaiNamSection,#phan8HienTaiThangSection,#phan8HienTaiNgaySection,#phan8HienTaiGioSection').hide();
                        $('#phan8TiepTheoNamSection,#phan8TiepTheoThangSection,#phan8TiepTheoNgaySection,#phan8TiepTheoGioSection').hide();
                        $('#phan8HienTaiNamContent,#phan8HienTaiThangContent,#phan8HienTaiNgayContent,#phan8HienTaiGioContent').empty();
                        $('#phan8TiepTheoNamContent,#phan8TiepTheoThangContent,#phan8TiepTheoNgayContent,#phan8TiepTheoGioContent').empty();
                        $('#phan8HienTaiGioiThieu,#phan8TiepTheoGioiThieu').hide();
                        $('#phan8HienTaiSection').removeData('has-content');
                        $('#phan8TiepTheoSection').removeData('has-content');

                        function renderNienVanItem(itemData, prefix, labelSuffix) {
                            if (!itemData) return;
                            const infoEl    = $('#' + prefix + 'Info');
                            const sectionEl = $('#' + prefix + 'Section');
                            const gtEl      = $('#' + prefix + 'GioiThieu');

                            infoEl.html(
                                '<strong>Niên Vận ' + labelSuffix + ':</strong> Năm ' + (itemData.nam_number || '—') + ' – ' +
                                '<strong>Thiên Can:</strong> ' + (itemData.thien_can || '—') +
                                ' (' + (itemData.thap_than_thien_can || '—') + ')' +
                                ' &nbsp;|&nbsp; ' +
                                '<strong>Địa Chi:</strong> ' + (itemData.dia_chi || '—') +
                                ' (' + (itemData.thap_than_dia_chi || '—') + ')'
                            );

                            gtEl.hide().empty();

                            var hasAny = false;
                            var truMap = [
                                ['nam',   'Thiên Can – Niên Vận – Trụ Năm',   'Địa Chi – Niên Vận – Trụ Năm'],
                                ['thang', 'Thiên Can – Niên Vận – Trụ Tháng', 'Địa Chi – Niên Vận – Trụ Tháng'],
                                ['ngay',  'Thiên Can – Niên Vận – Trụ Ngày',  'Địa Chi – Niên Vận – Trụ Ngày'],
                                ['gio',   'Thiên Can – Niên Vận – Trụ Giờ',   'Địa Chi – Niên Vận – Trụ Giờ'],
                            ];
                            truMap.forEach(function(t) {
                                var key     = t[0];
                                var labelTC = t[1];
                                var labelDC = t[2];
                                var truData = itemData[key];
                                var capKey  = key.charAt(0).toUpperCase() + key.slice(1);
                                var truSec  = $('#' + prefix + capKey + 'Section');
                                var truCont = $('#' + prefix + capKey + 'Content');

                                truSec.hide();
                                truCont.empty();
                                if (!truData) return;

                                var html = '';
                                var introText = (truData.gioi_thieu && truData.gioi_thieu.noi_dung) ?
                                    String(truData.gioi_thieu.noi_dung) : '';

                                if (introText) {
                                    html += '<div class="text-sm text-gray-700 whitespace-pre-line bg-gray-50 border border-gray-200 rounded-lg p-3 mb-3">' +
                                                $('<div>').text(introText).html() +
                                            '</div>';
                                }

                                var tcBlocks = Array.isArray(truData.thien_can) ? truData.thien_can : (truData.thien_can ? [truData.thien_can] : []);
                                var dcBlocks = Array.isArray(truData.dia_chi)   ? truData.dia_chi   : (truData.dia_chi   ? [truData.dia_chi]   : []);
                                tcBlocks.forEach(function(block) {
                                    if (block && block.moi_quan_he) html += renderCodingLogicBlock(block, labelTC, false);
                                });
                                dcBlocks.forEach(function(block) {
                                    if (block && block.moi_quan_he) html += renderCodingLogicBlock(block, labelDC, true);
                                });

                                if (html) {
                                    truCont.html(html);
                                    truSec.show();
                                    hasAny = true;
                                }
                            });

                            if (hasAny) {
                                sectionEl.data('has-content', true).show();
                            } else {
                                sectionEl.removeData('has-content');
                            }
                        }

                        function finishNienVanLoad() {
                            section.data('quyen-was-shown', true).show();
                            refreshPhan8QuyenVisibility();
                        }

                        $.ajax({
                            url: '/api/phan-8/nien-van',
                            method: 'GET',
                            dataType: 'json',
                            data: Object.assign({}, params, { phan_ban: '8b' }),
                            error: function(xhr, status, err) {
                                console.error('loadPhan8NienVan 8b API error:', status, err);
                                finishNienVanLoad();
                            },
                            success: function(res) {
                                const data = (res && res.data) ? res.data : null;
                                if (data) {
                                    const $yNghia = $('#phan8NienVanYNghia');
                                    $yNghia.removeData('has-content').hide().empty();
                                    if (data.y_nghia && data.y_nghia.noi_dung) {
                                        $yNghia.text(data.y_nghia.noi_dung).data('has-content', true);
                                    }
                                    renderNienVanItem(data.tiep_theo, 'phan8TiepTheo', 'Tiếp Theo');
                                }
                                finishNienVanLoad();
                            }
                        });
                    }

                    // Load PHẦN 8 - III: Dự báo các khía cạnh cuộc sống
                    function loadPhan8DuBaoKhiaCanh(params) {
                        const section = $('#phan8DuBaoKhiaCanhSection');
                        const container = $('#phan8DuBaoKhiaCanhContent');
                        section.hide();
                        container.empty();

                        $.ajax({
                            url: '/api/phan-8/du-bao-khia-canh',
                            method: 'GET',
                            dataType: 'json',
                            data: Object.assign({}, params, { phan_ban: '8b' }),
                            error: function(xhr, status, err) {
                                console.error('loadPhan8DuBaoKhiaCanh API error:', status, err);
                            },
                            success: function(res) {
                                const data = (res && res.data) ? res.data : null;
                                const items = (data && Array.isArray(data.items)) ? data.items : [];
                                if (!items.length) return;

                                const escapeHtml = function(text) {
                                    return $('<div>').text(text || '').html();
                                };

                                let html = '';
                                items.forEach(function(item) {
                                    if (!item || !item.match) return;
                                    const m = item.match;
                                    const title = item.khia_canh || '';
                                    const dieuKien = m.dieu_kien || '';
                                    const noiDung = m.noi_dung || '';
                                    if (!noiDung) return;

                                    html += '<div class="rounded-lg border border-indigo-200 bg-indigo-50/40 p-4">';
                                    html += '<h4 class="font-semibold text-indigo-700 mb-2">' + escapeHtml(title) + '</h4>';
                                    if (dieuKien) {
                                        html += '<div class="text-xs text-gray-600 mb-2"><strong>Điều kiện khớp:</strong> ' + escapeHtml(dieuKien) + '</div>';
                                    }
                                    html += '<div class="text-sm text-gray-700 whitespace-pre-line">' + escapeHtml(noiDung) + '</div>';
                                    html += '</div>';
                                });

                                if (html) {
                                    container.html(html);
                                    section.data('quyen-was-shown', true);
                                    refreshPhan8QuyenVisibility();
                                }
                            }
                        });
                    }

                    // Load PHẦN 8 - IV: Những năm cần chú ý (Niên Vận × Trụ, từ năm hiện tại)
                    function loadPhan8NhungNamCanChuY(params) {
                        const section = $('#phan8NhungNamChuYSection');
                        const container = $('#phan8NhungNamChuYGhiChu');
                        const mainEl = $('#phan8NhungNamChuYContent');
                        section.hide();
                        container.hide().empty();
                        mainEl.empty();

                        $.ajax({
                            url: '/api/phan-8/nhung-nam-can-chu-y',
                            method: 'GET',
                            dataType: 'json',
                            data: params,
                            error: function(xhr, status, err) {
                                console.error('loadPhan8NhungNamCanChuY API error:', status, err);
                            },
                            success: function(res) {
                                const data = (res && res.data) ? res.data : null;
                                const blocks = (data && Array.isArray(data.dai_van_blocks)) ? data.dai_van_blocks : [];
                                if (!blocks.length) return;

                                const gkx = data.ghi_chu_khac_xung || '';
                                const gtr = data.ghi_chu_trung || '';
                                if (gkx || gtr) {
                                    let ghtml = '';
                                    if (gkx) {
                                        ghtml += '<p><strong>Khắc + Xung:</strong> ' + escapeHtml(gkx) + '</p>';
                                    }
                                    if (gtr) {
                                        ghtml += '<p><strong>Trùng Can Chi:</strong> ' + escapeHtml(gtr) + '</p>';
                                    }
                                    container.html(ghtml).show();
                                }

                                function depthClass(yr) {
                                    const chuY = (yr && Array.isArray(yr.chu_y)) ? yr.chu_y : [];
                                    const n = chuY.length;
                                    if (n <= 0) return '';
                                    const d = Math.min(n, 12);
                                    return 'phan8-iv-depth-' + d;
                                }

                                let out = '';
                                blocks.forEach(function(blk) {
                                    if (!blk || !Array.isArray(blk.years) || !blk.years.length) {
                                        return;
                                    }

                                    const yearCols = blk.years.slice().sort(function(a, b) {
                                        return (a.nam || 0) - (b.nam || 0);
                                    });
                                    const ageLabel = blk.age != null && blk.age !== '' ? String(blk.age) : '';
                                    const colSpan = yearCols.length + 1;
                                    out += '<div class="mb-8 overflow-x-auto">';
                                    out += '<table class="phan8-iv-grid text-sm">';
                                    out += '<thead><tr><th class="phan8-iv-head border border-black" colspan="' + colSpan + '">Đại Vận ' + escapeHtml(ageLabel) + '</th></tr></thead>';
                                    out += '<tbody>';

                                    function openRow(label) {
                                        out += '<tr><td class="phan8-iv-label border border-black">' + escapeHtml(label) + '</td>';
                                    }
                                    function closeRow() {
                                        out += '</tr>';
                                    }

                                    openRow('Niên Vận');
                                    yearCols.forEach(function(yr) {
                                        const nam = yr.nam != null ? yr.nam : '';
                                        out += '<td class="phan8-iv-cell-center border border-black ' + depthClass(yr) + '">' + escapeHtml(String(nam)) + '</td>';
                                    });
                                    closeRow();

                                    openRow('Thiên Can');
                                    yearCols.forEach(function(yr) {
                                        out += '<td class="phan8-iv-cell-center border border-black ' + depthClass(yr) + '">' + escapeHtml(yr.thien_can || '') + '</td>';
                                    });
                                    closeRow();

                                    openRow('Địa Chi');
                                    yearCols.forEach(function(yr) {
                                        out += '<td class="phan8-iv-cell-center border border-black ' + depthClass(yr) + '">' + escapeHtml(yr.dia_chi || '') + '</td>';
                                    });
                                    closeRow();

                                    openRow('Tàng Can');
                                    yearCols.forEach(function(yr) {
                                        out += '<td class="phan8-iv-cell-center border border-black ' + depthClass(yr) + '">' + escapeHtml(yr.tang_can || '') + '</td>';
                                    });
                                    closeRow();

                                    openRow('Chú ý');
                                    yearCols.forEach(function(yr) {
                                        const chuY = Array.isArray(yr.chu_y) ? yr.chu_y : [];
                                        let stack = '<div class="phan8-iv-chu-y-stack">';
                                        chuY.forEach(function(lb) {
                                            stack += '<div>' + escapeHtml(lb) + '</div>';
                                        });
                                        stack += '</div>';
                                        out += '<td class="phan8-iv-chu-y-cell phan8-iv-cell-center border border-black ' + depthClass(yr) + '">' + stack + '</td>';
                                    });
                                    closeRow();

                                    out += '</tbody></table></div>';
                                });

                                if (!out) {
                                    return;
                                }

                                mainEl.html(out);
                                section.data('quyen-was-shown', true);
                                refreshPhan8QuyenVisibility();
                            }
                        });
                    }

                    function parsePhan5Keywords(text) {
                        if (!text || !String(text).trim()) {
                            return [];
                        }
                        const parts = String(text).split(/[,，、;]+|\r\n|\r|\n/);
                        const keywords = [];
                        for (let i = 0; i < parts.length; i++) {
                            const part = parts[i].trim();
                            if (!part) {
                                continue;
                            }
                            keywords.push(part);
                            if (keywords.length >= 3) {
                                break;
                            }
                        }
                        return keywords;
                    }

                    function isPhan5KeywordSection(label) {
                        return String(label || '').toLowerCase().indexOf('từ khóa') >= 0;
                    }

                    function renderPhan5KeywordBoxes(keywords, label) {
                        if (!keywords || !keywords.length) {
                            return '';
                        }
                        const frameUrl = PHAN5_KEYWORD_FRAME_URL ||
                            '/images/pdfs/phan-5/anh-tu-khoa-frame.png';
                        let html = '<div class="mb-3">';
                        html += '<div class="phan5-muc-label mb-2">' +
                            escapeHtml(label || 'Ba từ khóa cốt lõi') + '</div>';
                        html += '<div class="phan5-kw-grid"><table><tr>';
                        keywords.forEach(function(kw) {
                            html += '<td><div class="phan5-kw-box">';
                            html += '<img class="phan5-kw-frame" src="' + escapeHtml(frameUrl) +
                                '" alt="">';
                            html += '<div class="phan5-kw-text"><div class="phan5-kw-text-inner"><span>' +
                                escapeHtml(kw) + '</span></div></div></div></td>';
                        });
                        for (let i = keywords.length; i < 3; i++) {
                            html += '<td></td>';
                        }
                        html += '</tr></table></div></div>';
                        return html;
                    }

                    function renderPhan5SectionBlocks(sections) {
                        if (!sections || !Array.isArray(sections) || sections.length === 0) {
                            return '<p class="text-gray-500">Chưa có nội dung cho Thập Thần này.</p>';
                        }
                        let html = '';
                        sections.forEach(function(sec) {
                            const rawLabel = sec.label || '';
                            const rawContent = sec.content || '';
                            if (!rawContent) return;

                            if (isPhan5KeywordSection(rawLabel)) {
                                const keywords = parsePhan5Keywords(rawContent);
                                if (keywords.length) {
                                    html += renderPhan5KeywordBoxes(keywords, rawLabel);
                                    return;
                                }
                            }

                            const label = escapeHtml(rawLabel);
                            const content = escapeHtml(rawContent);
                            let cls = 'text-gray-700';
                            const tone = (sec.tone || '').toLowerCase();
                            const labelLower = rawLabel.toLowerCase();
                            if (tone === 'positive' || labelLower.indexOf('tích cực') >= 0) {
                                cls = 'text-green-700';
                            } else if (tone === 'negative' || labelLower.indexOf('tiêu cực') >= 0) {
                                cls = 'text-red-700';
                            } else if (tone === 'strategy' || labelLower.indexOf('chiến lược') >= 0) {
                                cls = 'text-indigo-800';
                            }
                            html += '<div class="border rounded-lg p-3 bg-gray-50 mb-2">';
                            if (label) {
                                html += '<div class="font-semibold ' + cls + ' mb-1">' + label + '</div>';
                            }
                            html += '<div class="whitespace-pre-line ' + cls + '">' + content + '</div></div>';
                        });
                        return html || '<p class="text-gray-500">Chưa có nội dung cho Thập Thần này.</p>';
                    }

                    function phan5HighlightPillars(slug) {
                        const map = {
                            su_nghiep: ['month', 'year'],
                            tai_chinh: ['month', 'hour'],
                            tinh_duyen: ['day'],
                            phat_trien_ban_than: ['hour'],
                            ket_noi_xa_hoi: ['year']
                        };
                        return map[slug] || [];
                    }

                    function renderPhan5BatTuTable(batTu, highlightPillars) {
                        if (!batTu || typeof batTu !== 'object') {
                            return '';
                        }
                        const hl = Array.isArray(highlightPillars) ? highlightPillars : [];
                        const cols = ['hour', 'day', 'month', 'year'];
                        const colLabels = {
                            hour: 'Giờ sinh',
                            day: 'Ngày sinh',
                            month: 'Tháng sinh',
                            year: 'Năm sinh'
                        };

                        let html = '<div class="phan5-bat-tu-wrap"><div class="table-wrapper">';
                        html += '<div class="phan5-bt-title">KẾT QUẢ LÁ SỐ TỨ TRỤ</div>';
                        html += '<table class="result-table"><thead><tr>';
                        html += '<td>LÁ SỐ BÁT TỰ</td>';
                        cols.forEach(function(col) {
                            html += '<td class="text-center">' + colLabels[col] + '</td>';
                        });
                        html += '</tr></thead><tbody>';

                        html += '<tr><td>Thiên can</td>';
                        cols.forEach(function(col) {
                            const can = (batTu[col] && batTu[col].can) ? batTu[col].can : {};
                            const cellHl = hl.indexOf(col) >= 0 ? ' phan5-hl' : '';
                            const tt = col === 'day' ? '/' : (can.chu_tinh || '');
                            html += '<td class="text-center' + cellHl + '">';
                            html += '<p class="header-cs-0">' + escapeHtml(can.thien_can || '') + '</p>';
                            html += '<p>' + escapeHtml(can.menh || '') + '</p>';
                            html += '<p class="ten-gods">' + escapeHtml(tt) + '</p></td>';
                        });
                        html += '</tr>';

                        html += '<tr><td>Địa chi</td>';
                        cols.forEach(function(col) {
                            const chi = (batTu[col] && batTu[col].chi) ? batTu[col].chi : {};
                            const cellHl = hl.indexOf(col) >= 0 ? ' phan5-hl' : '';
                            html += '<td class="text-center' + cellHl + '">';
                            html += '<p class="header-cs-0">' + escapeHtml(chi.dia_chi || '') + '</p>';
                            html += '<p>' + escapeHtml(chi.menh || '') + '</p>';
                            if (chi.khong_vong) {
                                html += '<p class="text-sm italic text-gray-500">(Không vong)</p>';
                            }
                            html += '</td>';
                        });
                        html += '</tr>';

                        html += '<tr><td>Tàng Can</td>';
                        cols.forEach(function(col) {
                            const tangs = (batTu[col] && batTu[col].can_tang) ? batTu[col].can_tang : [];
                            const cellHl = hl.indexOf(col) >= 0 ? ' phan5-hl' : '';
                            html += '<td class="text-center' + cellHl + '"><div class="flex justify-center">';
                            if (Array.isArray(tangs)) {
                                tangs.forEach(function(tc) {
                                    html += '<div style="text-align:center;padding:0 5px;">';
                                    html += '<p class="font-bold">' + escapeHtml(tc.can_tang || '') + '</p>';
                                    html += '<p>' + escapeHtml(tc.menh || '') + '</p>';
                                    html += '<p class="ten-gods">' + escapeHtml(tc.pho_tinh || '') + '</p></div>';
                                });
                            }
                            html += '</div></td>';
                        });
                        html += '</tr></tbody></table></div></div>';
                        return html;
                    }

                    function renderPhan5KhiaCanh(khiaCanhList, batTu) {
                        const container = $('#phan5KhiaCanhContainer');
                        container.empty();
                        if (!khiaCanhList || !Array.isArray(khiaCanhList) || khiaCanhList.length === 0) {
                            return;
                        }

                        khiaCanhList.forEach(function(block) {
                            if (!block) return;
                            let html = '<div class="phan5-khia-canh-block mb-8 pb-6 border-b border-gray-100 last:border-b-0">';
                            if (block.title) {
                                html += '<h4 class="font-semibold text-indigo-700 mb-4 text-base">' +
                                    escapeHtml(block.title) + '</h4>';
                            }
                            if (block.tong_quan) {
                                html += '<div class="mb-3">';
                                html += '<div class="font-semibold text-gray-800 mb-2">1. Tổng quan:</div>';
                                html += '<div class="text-gray-700 whitespace-pre-line">' +
                                    escapeHtml(block.tong_quan) + '</div></div>';
                            }
                            if (batTu) {
                                html += renderPhan5BatTuTable(batTu, phan5HighlightPillars(block.slug || ''));
                            }
                            if (block.image_vi_tri) {
                                html += '<img src="' + escapeHtml(block.image_vi_tri) +
                                    '" class="max-w-full rounded-lg my-4 block" alt="Vị trí ' +
                                    escapeHtml(block.title || '') + '">';
                            }

                            const items = Array.isArray(block.items) ? block.items : [];
                            if (items.length === 0) {
                                html += '<p class="text-gray-500 italic">Chưa có dữ liệu Thập Thần cho khía cạnh này.</p>';
                            }
                            items.forEach(function(item, idx) {
                                if (!item) return;
                                const itemNum = idx + 2;
                                let heading = escapeHtml(item.label || '');
                                if (item.thap_than) {
                                    heading += ': ' + escapeHtml(item.thap_than);
                                } else if (item.can) {
                                    heading += ': ' + escapeHtml(item.can);
                                }
                                html += '<div class="mt-5">';
                                html += '<h5 class="font-semibold text-indigo-600 mb-2">' +
                                    itemNum + '. ' + heading + '</h5>';
                                if (item.image_minh_hoa) {
                                    html += '<img src="' + escapeHtml(item.image_minh_hoa) +
                                        '" class="max-w-md rounded-lg my-3 block" alt="' +
                                        escapeHtml(item.thap_than || '') + '">';
                                }
                                html += renderPhan5SectionBlocks(item.sections || []);
                                html += '</div>';
                            });

                            html += '</div>';
                            container.append(html);
                        });
                    }

                    // Load Phần 5: khía cạnh theo Thập Thần (II–VII) + Sức khỏe + Giải pháp
                    function loadSuNghiepThapThan(params, batTuFromCalc, chatLuongThapThan) {
                        const section = $('#suNghiepThapThanSection');
                        const khiaCanhContainer = $('#phan5KhiaCanhContainer');
                        const giaiPhapContainer = $('#giaiPhapThapThanContainer');
                        const sucKhoeSection = $('#sucKhoeHyKyThanSection');
                        const sucKhoeNgay = $('#sucKhoeHyKyThanNgay');
                        const sucKhoeHy = $('#sucKhoeHyKyThanHy');
                        const sucKhoeKy = $('#sucKhoeHyKyThanKy');
                        const sucKhoeCounts = $('#sucKhoeHyKyThanCounts');
                        const sucKhoeKetLuan = $('#sucKhoeHyKyThanKetLuan');
                        const sucKhoeChiTietContainer = $('#sucKhoeChiTietContainer');

                        khiaCanhContainer.empty();
                        giaiPhapContainer.empty();
                        sucKhoeNgay.text('');
                        sucKhoeHy.text('');
                        sucKhoeKy.text('');
                        sucKhoeCounts.text('');
                        sucKhoeKetLuan.text('');
                        sucKhoeChiTietContainer.empty();
                        sucKhoeSection.hide();

                        $.ajax({
                            url: '/api/phan-5/su-nghiep',
                            method: 'GET',
                            dataType: 'json',
                            data: $.extend({}, params, {
                                include_bat_tu: 0,
                                chat_luong_thap_than: JSON.stringify(chatLuongThapThan || [])
                            }),
                            success: function(res) {
                                if (res.khia_canh && res.khia_canh.length > 0) {
                                    renderPhan5KhiaCanh(res.khia_canh, batTuFromCalc || res.bat_tu || null);
                                }

                                if (res.suc_khoe) {
                                    renderSucKhoeHyKyThan(res.suc_khoe);
                                }

                                if (res.giai_phap_thap_than && res.giai_phap_thap_than.length > 0) {
                                    renderGiaiPhapThapThan(res.giai_phap_thap_than);
                                }

                                section.show();
                            },
                            error: function() {
                                section.hide();
                            }
                        });
                    }

                    function renderPhan7TamTheBlock(item) {
                        let html = '<div class="border-l-4 border-indigo-200 pl-4 py-2">';
                        if (item.noi_dung === '[image]' && item.image) {
                            html += '<img src="' + escapeHtml(item.image) + '" alt="Biểu đồ ngũ hành bản mệnh" class="max-w-full rounded-lg my-2" />';
                        } else {
                            const nd = escapeHtml(item.noi_dung || '');
                            if (!nd) { return ''; }
                            html += '<div class="text-gray-700 whitespace-pre-line">' + nd + '</div>';
                        }
                        html += '</div>';
                        return html;
                    }

                    function renderPhan7BaiHoc(data) {
                        const muc1El = $('#phan7TamThe');
                        const muc2El = $('#phan7Phan27');
                        const muc1CuoiEl = $('#phan7TamTheCuoi');
                        muc1El.empty();
                        muc2El.empty();
                        muc1CuoiEl.empty();
                        if (!data) return;

                        // Mục I – sheet 1: PHẦN 7 - I.xlsx
                        if (data.muc_1 && data.muc_1.length > 0) {
                            data.muc_1.forEach(function(item) {
                                const html = renderPhan7TamTheBlock(item);
                                if (html) muc1El.append(html);
                            });
                        }

                        // Mục II: nội dung theo % Thập Thần từ PHẦN 7 - II.xlsx
                        if (data.muc_2 && data.muc_2.length > 0) {
                            data.muc_2.forEach(function(entry) {
                                const thapThan = escapeHtml(entry.thap_than || '');
                                const tenTruongHop = escapeHtml(entry.ten_truong_hop || '');
                                const entryImage = entry.image || null;
                                const noiDungList = entry.noi_dung || [];
                                let html = '<div class="border rounded-lg p-4 bg-gray-50">';
                                html += '<h4 class="font-semibold text-indigo-700 mb-1">' + thapThan + '</h4>';
                                if (tenTruongHop) html += '<div class="text-sm text-gray-600 mb-3 italic">' + tenTruongHop + '</div>';
                                noiDungList.forEach(function(block) {
                                    const tieuDe = escapeHtml(block.tieu_de || '');
                                    const lines = block.lines || [];
                                    const isBanChat = tieuDe.indexOf('Bản chất') !== -1 || tieuDe.indexOf('a.') === 0;
                                    html += '<div class="mt-3 pl-3 border-l-2 border-indigo-100">';
                                    if (tieuDe) html += '<div class="font-medium text-gray-800 mb-1">' + tieuDe + '</div>';
                                    lines.forEach(function(line) {
                                        html += '<div class="text-gray-700 whitespace-pre-line text-sm">' + escapeHtml(line) + '</div>';
                                    });
                                    // Hiển thị ảnh minh họa ngay dưới mục "a. Bản chất năng lượng"
                                    if (isBanChat && entryImage) {
                                        html += '<img src="' + escapeHtml(entryImage) + '" alt="' + thapThan + '" class="max-w-full rounded-lg mt-3" />';
                                    }
                                    html += '</div>';
                                });
                                html += '</div>';
                                muc2El.append(html);
                            });
                        }

                        // Sheet 2 – Đoạn nối: cuối Phần 7 (sau Mục II)
                        if (data.muc_1_cuoi && data.muc_1_cuoi.length > 0) {
                            data.muc_1_cuoi.forEach(function(item) {
                                const html = renderPhan7TamTheBlock(item);
                                if (html) muc1CuoiEl.append(html);
                            });
                        }
                    }

                    function loadBaiHocCuocSong(params) {
                        const section = $('#phan7BaiHocSection');
                        section.hide();
                        $.ajax({
                            url: '/api/phan-7/bai-hoc-cuoc-song',
                            method: 'GET',
                            dataType: 'json',
                            data: params,
                            success: function(res) {
                                if (res && (res.muc_1 || res.muc_2 || res.muc_1_cuoi)) {
                                    renderPhan7BaiHoc(res);
                                    section.show();
                                }
                            }
                        });
                    }

                    function escapeHtml(str) {
                        if (str == null || str === '') return '';
                        return String(str)
                            .replace(/&/g, '&amp;')
                            .replace(/</g, '&lt;')
                            .replace(/>/g, '&gt;')
                            .replace(/"/g, '&quot;')
                            .replace(/'/g, '&#39;');
                    }

                    function renderLaSoBatTuTable(tableData) {
                        if (!tableData || !tableData.rows || !tableData.rows.length) return '';
                        const cols = tableData.columns || [];
                        let html = '<div class="border rounded-lg p-3 bg-gray-50 mb-3 overflow-x-auto">';
                        html += '<h5 class="font-semibold text-indigo-700 mb-3">' +
                            escapeHtml(tableData.title || 'Lá số Bát Tự') + '</h5>';
                        html += '<table class="min-w-full text-sm border border-gray-200 rounded-lg overflow-hidden">';
                        html += '<thead class="bg-indigo-50"><tr>';
                        html += '<th class="border border-gray-200 px-3 py-2 text-left font-semibold text-indigo-800 w-28">' +
                            escapeHtml(tableData.title || 'Lá số Bát Tự') + '</th>';
                        cols.forEach(function(col) {
                            html += '<th class="border border-gray-200 px-3 py-2 text-center font-semibold text-indigo-800">' +
                                escapeHtml(col.label || col.key) + '</th>';
                        });
                        html += '</tr></thead><tbody>';
                        tableData.rows.forEach(function(row, idx) {
                            const bg = idx % 2 === 0 ? 'bg-white' : 'bg-gray-50';
                            html += '<tr class="' + bg + '">';
                            html += '<td class="border border-gray-200 px-3 py-2 font-semibold text-gray-800 align-top">' +
                                escapeHtml(row.loai || '') + '</td>';
                            cols.forEach(function(col) {
                                const cell = (row.cells && row.cells[col.key]) ? row.cells[col.key] : '';
                                html += '<td class="border border-gray-200 px-3 py-2 text-gray-700 align-top leading-relaxed whitespace-pre-line">' +
                                    escapeHtml(cell) + '</td>';
                            });
                            html += '</tr>';
                        });
                        html += '</tbody></table></div>';

                        return html;
                    }

                    /**
                     * Mã 1: mục 3 có a./b. và bullet cột C (Thiên Can Hợp/Khắc, Địa Chi Hợp/Xung…).
                     */
                    function formatYNghiaTuTruContent(content) {
                        if (!content) return '';
                        const hasSub = /(^|\n)\s*[abc]\.\s/i.test(content) ||
                            /(^|\n)\s*-\s+/m.test(content);
                        if (!hasSub) {
                            return formatPhan6GioiThieu(content);
                        }
                        const lines = String(content).split(/\r?\n/);
                        let html = '';
                        let inList = false;
                        lines.forEach(function(line) {
                            line = line.trim();
                            if (!line) return;
                            if (/^[abc]\.\s/i.test(line)) {
                                if (inList) {
                                    html += '</ul>';
                                    inList = false;
                                }
                                html += '<h6 class="font-semibold text-indigo-600 mt-3 mb-2">' +
                                    escapeHtml(line) + '</h6>';
                            } else if (/^-\s+/.test(line)) {
                                if (!inList) {
                                    html += '<ul class="list-disc pl-5 space-y-2 mb-2 text-gray-700">';
                                    inList = true;
                                }
                                html += '<li class="leading-relaxed">' +
                                    escapeHtml(line.replace(/^-\s+/, '')) + '</li>';
                            } else {
                                if (inList) {
                                    html += '</ul>';
                                    inList = false;
                                }
                                html += '<p class="mb-2 leading-relaxed whitespace-pre-line">' +
                                    escapeHtml(line) + '</p>';
                            }
                        });
                        if (inList) html += '</ul>';

                        return html;
                    }

                    function formatPhan6GioiThieu(content) {
                        if (!content) return '';
                        const stripped = String(content).replace(/\[\[image:[^\]]+\]\]/g, '');

                        return '<span class="whitespace-pre-line">' + escapeHtml(stripped) + '</span>';
                    }

                    /**
                     * Mã 2–4 theo mẫu Excel: (1) tiêu đề II/III/IV (2) đoạn giới thiệu (3) ảnh thay [CHÈN HÌNH ẢNH]
                     * (4) khối Thiên Can / Địa Chi từ coding + dòng chảy (không hiện dòng bảng mẫu Excel).
                     */
                    function renderPhan6GioiThieuBlock(gt, fallbackTitle) {
                        if (!gt || (!gt.tieu_de && !gt.noi_dung)) return '';
                        let html = '<div class="mb-4 p-4 bg-blue-50 rounded-lg border border-blue-100 text-gray-700">';
                        const title = (gt.tieu_de && String(gt.tieu_de).trim()) || fallbackTitle || '';
                        if (title) {
                            html += '<h4 class="font-semibold text-indigo-700 mb-3">' + escapeHtml(title) + '</h4>';
                        }
                        if (gt.noi_dung) {
                            html += '<div class="mb-4 leading-relaxed text-gray-700">' +
                                formatPhan6GioiThieu(gt.noi_dung) + '</div>';
                        }
                        html += '</div>';

                        return html;
                    }

                    function renderPhan6MaDongChay(sectionData, fallbackTitle, labelTc, labelDc) {
                        if (!sectionData) {
                            return '<p class="text-gray-500">Chưa có dữ liệu.</p>';
                        }
                        let html = '';
                        const gt = sectionData.gioi_thieu;
                        if (gt && (gt.tieu_de || gt.noi_dung)) {
                            html += renderPhan6GioiThieuBlock(gt, fallbackTitle);
                        } else if (fallbackTitle) {
                            html += '<h4 class="font-semibold text-indigo-700 mb-3">' + escapeHtml(fallbackTitle) + '</h4>';
                        }
                        const tcBlocks = Array.isArray(sectionData.thien_can) ? sectionData.thien_can
                            : (sectionData.thien_can ? [sectionData.thien_can] : []);
                        const dcBlocks = Array.isArray(sectionData.dia_chi) ? sectionData.dia_chi
                            : (sectionData.dia_chi ? [sectionData.dia_chi] : []);
                        tcBlocks.forEach(function(block) {
                            if (block && block.moi_quan_he) {
                                html += renderCodingLogicBlock(block, labelTc, false);
                            }
                        });
                        dcBlocks.forEach(function(block) {
                            if (block && block.moi_quan_he) {
                                html += renderCodingLogicBlock(block, labelDc, true);
                            }
                        });
                        if (!html) {
                            html = '<p class="text-gray-500">Chưa có dữ liệu mối quan hệ cho lá số này.</p>';
                        }

                        return html;
                    }

                    function renderFullGioiThieu(gt) {
                        return renderPhan6GioiThieuBlock(gt, '');
                    }

                    /**
                     * Lọc nội dung theo mối quan hệ: giữ các block có key [Hợp],[Khắc],[Xung],[Hình],[Hại],[Phá] khớp,
                     * ẩn block (dòng key + các dòng theo sau) có key không khớp. Dòng đầu không có key được giữ.
                     */
                    function filterNoiDungByMoiQuanHe(text, moiQuanHeStr) {
                        if (!text || typeof text !== 'string') return '';
                        var mqhArr = (moiQuanHeStr || '').split(',').map(function(s) { return s.trim(); }).filter(Boolean);
                        if (mqhArr.length === 0) return text;
                        var lines = text.split(/\r?\n/);
                        var out = [];
                        var keyRe = /^\[(Hợp|Khắc|Xung|Hình|Hại|Phá)\]\s*/;
                        var inValidBlock = true;
                        for (var i = 0; i < lines.length; i++) {
                            var line = lines[i];
                            var m = line.match(keyRe);
                            if (m) {
                                var key = m[1];
                                inValidBlock = mqhArr.indexOf(key) >= 0;
                                if (inValidBlock) {
                                    out.push(line.replace(keyRe, '').trim());
                                }
                            } else if (inValidBlock) {
                                out.push(line);
                            }
                        }
                        return out.join('\n').trim();
                    }

                    function renderPhan8aBlocks(phan8aList) {
                        if (!Array.isArray(phan8aList) || !phan8aList.length) return '';
                        let html = '';
                        phan8aList.forEach(function(p8) {
                            if (!p8 || !Array.isArray(p8.sections) || !p8.sections.length) return;
                            html += '<div class="mb-3 p-3 bg-indigo-50 border border-indigo-200 rounded-lg">';
                            if (p8.tieu_de) {
                                html += '<div class="font-semibold text-indigo-800 mb-2">' + escapeHtml(p8.tieu_de) + '</div>';
                            } else if (p8.moi_quan_he) {
                                html += '<div class="font-semibold text-indigo-700 mb-2">Mối quan hệ: ' +
                                    escapeHtml(p8.moi_quan_he) + '</div>';
                            }
                            p8.sections.forEach(function(sec) {
                                const label = escapeHtml(sec.label || '');
                                const rawContent = (sec.content || '').trim();
                                if (!rawContent) return;
                                let cls = 'text-gray-700';
                                const labelLower = (sec.label || '').toLowerCase();
                                if (labelLower.indexOf('cơ hội') >= 0) {
                                    cls = 'text-green-700';
                                } else if (labelLower.indexOf('rủi ro') >= 0) {
                                    cls = 'text-red-700';
                                } else if (labelLower.indexOf('chiến lược') >= 0) {
                                    cls = 'text-indigo-800';
                                }
                                html += '<div class="border rounded-lg p-3 bg-white mb-2">';
                                if (label) {
                                    html += '<div class="font-semibold ' + cls + ' mb-1">' + label + '</div>';
                                }
                                html += '<div class="whitespace-pre-line ' + cls + '">' +
                                    formatPhan9TextBlock(rawContent) + '</div></div>';
                            });
                            html += '</div>';
                        });
                        return html;
                    }

                    function renderSuNghiepSections(data) {
                        if (!data) return '';
                        const sections = Array.isArray(data.sections) ? data.sections : [];
                        if (sections.length > 0) {
                            return renderPhan5SectionBlocks(sections);
                        }
                        let fallback = '';
                        if (data.tich_cuc) {
                            fallback +=
                                '<div class="text-sm text-green-700 whitespace-pre-line mb-2">' +
                                escapeHtml(data.tich_cuc) + '</div>';
                        }
                        if (data.tieu_cuc) {
                            fallback +=
                                '<div class="text-sm text-red-700 whitespace-pre-line">' +
                                escapeHtml(data.tieu_cuc) + '</div>';
                        }
                        return fallback ||
                            '<p class="text-gray-500">Chưa có nội dung Sự nghiệp cho Thập Thần này.</p>';
                    }

                    function renderCodingLogicBlock(blockData, title, isDiaChi) {
                        if (!blockData || !blockData.moi_quan_he) return '';
                        let html =
                            '<div class="border-l-4 border-indigo-300 pl-4"><div class="font-semibold text-indigo-600 mb-2">' +
                            title + '</div>';
                        html += '<div class="text-gray-700 mb-2">';
                        if (isDiaChi) {
                            html += '<span class="font-semibold">Địa Chi:</span> ' + (blockData.dia_chi_1 || '') +
                                ' &ndash; ' + (blockData.dia_chi_2 || '') + ' &ndash; ';
                        } else {
                            html += '<span class="font-semibold">Thiên Can:</span> ' + (blockData.thien_can_1 || '') +
                                ' &ndash; ' + (blockData.thien_can_2 || '') + ' &ndash; ';
                        }
                        html += '<span class="font-semibold">Thập Thần:</span> ' + (blockData.thap_than || '') +
                            ' &ndash; ';
                        html += '<span class="font-semibold">Mối quan hệ:</span> ' + (blockData.moi_quan_he || '');
                        if (blockData.ngu_hanh_sinh_ra) {
                            html += ' (Ngũ hành sinh ra: ' + blockData.ngu_hanh_sinh_ra + ')';
                        }
                        html += '</div>';
                        const gt = blockData.gioi_thieu || {};
                        const mqhToKey = {
                            'Hợp': 'hop',
                            'Khắc': 'khac',
                            'Xung': 'xung',
                            'Hình': 'hinh',
                            'Hại': 'hai',
                            'Phá': 'pha'
                        };
                        const mqhKey = mqhToKey[blockData.moi_quan_he] || blockData.moi_quan_he;
                        let ndMqh = (gt.noi_dung_theo_moi_quan_he || {})[mqhKey];
                        if (ndMqh) {
                            ndMqh = filterNoiDungByMoiQuanHe(ndMqh, blockData.moi_quan_he);
                        }
                        if (ndMqh) {
                            html +=
                                '<div class="mb-2 p-3 bg-amber-50 rounded-lg text-gray-700 border border-amber-200 whitespace-pre-line">' +
                                escapeHtml(ndMqh) + '</div>';
                        }
                        const phan8aHtml = renderPhan8aBlocks(blockData.phan8a);
                        if (phan8aHtml) {
                            html += phan8aHtml;
                        }

                        const noiDung = blockData.noi_dung || [];
                        if (!noiDung.length && phan8aHtml) {
                            html += '</div>';
                            return html;
                        }
                        var groupedByThapThan = {};
                        var thapThanOrder = [];
                        var labelOrder = (blockData.thap_than || '').split(',').map(function(s) { return s.trim(); }).filter(Boolean);
                        noiDung.forEach(function(item) {
                            const thapThan = (item.thap_than && String(item.thap_than).trim()) ? String(item.thap_than).trim() : 'Chung';
                            if (!groupedByThapThan[thapThan]) {
                                groupedByThapThan[thapThan] = [];
                                thapThanOrder.push(thapThan);
                            }
                            groupedByThapThan[thapThan].push(item);
                        });
                        thapThanOrder.sort(function(a, b) {
                            var i = labelOrder.indexOf(a);
                            var j = labelOrder.indexOf(b);
                            if (i >= 0 && j >= 0) return i - j;
                            if (i >= 0) return -1;
                            if (j >= 0) return 1;
                            return 0;
                        });
                        thapThanOrder.forEach(function(thapThanName) {
                            const items = groupedByThapThan[thapThanName] || [];
                            html += '<div class="mb-4">';
                            html += '<div class="font-semibold text-indigo-600 mb-2">' + escapeHtml(thapThanName) + '</div>';
                            items.forEach(function(item) {
                                const huong = item.huong || '';
                                const nd = item.noi_dung || '';
                                if (!nd) return;
                                const filteredNd = filterNoiDungByMoiQuanHe(nd, blockData.moi_quan_he);
                                if (!filteredNd) return;
                                const isTichCuc = huong.toLowerCase().indexOf('tích') >= 0;
                                const cls = isTichCuc ? 'text-green-700' : 'text-red-700';
                                html += '<div class="border rounded-lg p-3 bg-gray-50 mb-2">';
                                html += '<div class="font-semibold ' + cls + ' mb-1">' + escapeHtml(huong) + '</div>';
                                html += '<div class="whitespace-pre-line text-gray-700">' + escapeHtml(filteredNd) + '</div>';
                                html += '</div>';
                            });
                            html += '</div>';
                        });
                        html += '</div>';
                        return html;
                    }

                    function renderTaiChinhTangCan(data) {
                        const container = $('#taiChinhTangCanContainer');
                        container.empty();
                        if (!data) return;

                        const buildColumn = function(posKey, posLabel, list) {
                            if (!list || !Array.isArray(list) || list.length === 0) return '';
                            let colHtml = '';
                            list.forEach(function(item) {
                                if (!item) return;
                                const canTang = item.can_tang || '';
                                const thapThan = item.thap_than || '';
                                const taiChinh = item.tai_chinh || '';
                                const mqhxh = item.moi_quan_he_xa_hoi || '';
                                if (!taiChinh && !mqhxh) return;
                                let header = posLabel;
                                if (canTang || thapThan) {
                                    header += ': ';
                                    if (canTang) header += canTang;
                                    if (thapThan) header += (canTang ? ' – ' : '') + thapThan;
                                }
                                colHtml += '<div class="mb-4">';
                                colHtml += `<h4 class="font-semibold text-indigo-700 mb-2">${header}</h4>`;
                                if (taiChinh) {
                                    colHtml += `<div class="text-sm text-green-700 whitespace-pre-line mb-2">${taiChinh}</div>`;
                                }
                                if (mqhxh) {
                                    colHtml += `<div class="text-sm text-gray-700 whitespace-pre-line">${mqhxh}</div>`;
                                }
                                colHtml += '</div>';
                            });
                            return colHtml;
                        };

                        const gioCol = buildColumn('gio', 'Tàng Can Giờ', data.gio);
                        const thangCol = buildColumn('thang', 'Tàng Can Tháng', data.thang);
                        const hasGio = gioCol !== '';
                        const hasThang = thangCol !== '';

                        if (hasGio && hasThang) {
                            container.html('<div class="space-y-6">' + gioCol + thangCol + '</div>');
                        } else if (hasGio) {
                            container.html(gioCol);
                        } else if (hasThang) {
                            container.html(thangCol);
                        }
                    }

                    function renderTinhCamTangCanNgay(list) {
                        const container = $('#tinhCamTangCanNgayContainer');
                        container.empty();
                        if (!list || !Array.isArray(list) || list.length === 0) {
                            return;
                        }

                        list.forEach(function(item) {
                            if (!item) return;
                            const canTang = item.can_tang || '';
                            const thapThan = item.thap_than || '';
                            const tichCuc = item.tich_cuc || '';
                            const tieuCuc = item.tieu_cuc || '';

                            if (!tichCuc && !tieuCuc) {
                                return;
                            }

                            let header = 'Tàng Can Ngày';
                            if (canTang || thapThan) {
                                header += ': ';
                                if (canTang) header += canTang;
                                if (thapThan) header += (canTang ? ' – ' : '') + thapThan;
                            }

                            let html = '<div class="mb-4">';
                            html += `<h4 class="font-semibold text-indigo-700 mb-2">${header}</h4>`;
                            if (tichCuc) {
                                html +=
                                    `<div class="text-sm text-green-700 whitespace-pre-line mb-2">${tichCuc}</div>`;
                            }
                            if (tieuCuc) {
                                html +=
                                    `<div class="text-sm text-red-700 whitespace-pre-line">${tieuCuc}</div>`;
                            }
                            html += '</div>';
                            container.append(html);
                        });
                    }

                    function renderPhatTrienBanThan(data) {
                        const container = $('#phatTrienBanThanContainer');
                        container.empty();
                        if (!data) return;

                        const blocks = [];

                        if (data.thien_can_gio) {
                            blocks.push({
                                label: 'Thiên Can Giờ',
                                can: null,
                                thap_than: data.thien_can_gio.thap_than || '',
                                phat_trien: data.thien_can_gio.phat_trien_ban_than || null,
                                tinh_cach: data.thien_can_gio.tinh_cach || null,
                            });
                        }

                        if (data.tang_can_gio) {
                            blocks.push({
                                label: 'Tàng Can Giờ',
                                can: data.tang_can_gio.can_tang || '',
                                thap_than: data.tang_can_gio.thap_than || '',
                                phat_trien: data.tang_can_gio.phat_trien_ban_than || null,
                                tinh_cach: data.tang_can_gio.tinh_cach || null,
                            });
                        }

                        const validBlocks = blocks.filter(function(item) {
                            return item.phat_trien || item.tinh_cach;
                        });

                        validBlocks.forEach(function(item) {
                            let header = item.label;
                            if (item.can || item.thap_than) {
                                header += ': ';
                                if (item.can) header += item.can;
                                if (item.thap_than) header += (item.can ? ' – ' : '') + item.thap_than;
                            }

                            let blockHtml = '<div class="mb-4">';
                            blockHtml += `<h4 class="font-semibold text-indigo-700 mb-2">${header}</h4>`;

                            if (item.phat_trien && (item.phat_trien.tich_cuc || item.phat_trien.tieu_cuc)) {
                                if (item.phat_trien.tich_cuc) {
                                    blockHtml +=
                                        `<div class="text-sm text-green-700 whitespace-pre-line mb-2">${item.phat_trien.tich_cuc}</div>`;
                                }
                                if (item.phat_trien.tieu_cuc) {
                                    blockHtml +=
                                        `<div class="text-sm text-red-700 whitespace-pre-line mb-2">${item.phat_trien.tieu_cuc}</div>`;
                                }
                            }

                            if (item.tinh_cach && (item.tinh_cach.tich_cuc || item.tinh_cach.tieu_cuc)) {
                                if (item.tinh_cach.tich_cuc) {
                                    blockHtml +=
                                        `<div class="text-sm text-green-700 whitespace-pre-line mb-2">${item.tinh_cach.tich_cuc}</div>`;
                                }
                                if (item.tinh_cach.tieu_cuc) {
                                    blockHtml +=
                                        `<div class="text-sm text-red-700 whitespace-pre-line">${item.tinh_cach.tieu_cuc}</div>`;
                                }
                            }

                            blockHtml += '</div>';
                            container.append(blockHtml);
                        });
                    }

                    function renderSucKhoeHyKyThan(data) {
                        const section = $('#sucKhoeHyKyThanSection');
                        const ngayEl = $('#sucKhoeHyKyThanNgay');
                        const hyEl = $('#sucKhoeHyKyThanHy');
                        const kyEl = $('#sucKhoeHyKyThanKy');
                        const countsEl = $('#sucKhoeHyKyThanCounts');
                        const ketLuanEl = $('#sucKhoeHyKyThanKetLuan');
                        const chiTietContainer = $('#sucKhoeChiTietContainer');

                        if (!data) {
                            section.hide();
                            ngayEl.text('');
                            chiTietContainer.empty();
                            return;
                        }

                        const thienCanNgay = data.thien_can_ngay || '';
                        const thapThanNgay = data.thap_than_ngay || '';
                        const hy = data.hy_than_ngu_hanh || '';
                        const ky = data.ky_than_ngu_hanh || '';
                        const soHy = typeof data.so_luong_hy_than === 'number' ? data.so_luong_hy_than : null;
                        const soKy = typeof data.so_luong_ky_than === 'number' ? data.so_luong_ky_than : null;
                        const truongHop = data.truong_hop || null;
                        const ketLuan = data.ket_luan || '';

                        if (thienCanNgay || thapThanNgay) {
                            let ngayText = 'Thiên Can Trụ Ngày: ';
                            if (thienCanNgay) {
                                ngayText += thienCanNgay;
                            }
                            if (thapThanNgay) {
                                ngayText += (thienCanNgay ? ' (' : '(') + thapThanNgay + ')';
                            }
                            ngayEl.text(ngayText);
                        } else {
                            ngayEl.text('');
                        }

                        hyEl.text('Hỷ Thần: ' + (hy !== '' ? hy : 'Không có'));
                        kyEl.text('Kỵ Thần: ' + (ky !== '' ? ky : 'Không có'));

                        let countsText = '';
                        if (soHy !== null || soKy !== null) {
                            countsText = 'Số lượng Hỷ Thần: ' + (soHy !== null ? soHy : 0) +
                                ' – Số lượng Kỵ Thần: ' + (soKy !== null ? soKy : 0);
                        }
                        countsEl.text(countsText);

                        let ketLuanText = '';
                        if (truongHop) {
                            ketLuanText = 'Trường hợp ' + truongHop + ': ' + ketLuan;
                        } else if (ketLuan) {
                            ketLuanText = 'Kết luận: ' + ketLuan;
                        }
                        ketLuanEl.text(ketLuanText);

                        // Chi tiết từ PHAN5_V_SUC_KHOE (nếu có)
                        chiTietContainer.empty();
                        const chiTiet = Array.isArray(data.chi_tiet) ? data.chi_tiet : null;
                        if (chiTiet && chiTiet.length > 0) {
                            chiTiet.forEach(function(item) {
                                if (!item) return;
                                const nhom = item.nhom || '';
                                const content = item.content || '';
                                if (!nhom && !content) return;
                                let html = '<div class="mb-4">';
                                if (nhom) {
                                    html += `<h4 class="font-semibold text-indigo-700 mb-2">${nhom}</h4>`;
                                }
                                if (content) {
                                    html += `<div class="text-sm text-gray-700 whitespace-pre-line">${content}</div>`;
                                }
                                html += '</div>';
                                chiTietContainer.append(html);
                            });
                        }

                        if (hy || ky || countsText || ketLuanText || (chiTiet && chiTiet.length > 0)) {
                            section.show();
                        } else {
                            section.hide();
                        }
                    }

                    function renderKetNoiXaHoi(data) {
                        const container = $('#ketNoiXaHoiContainer');
                        container.empty();
                        if (!data) return;

                        const buildBlockHtml = function(item) {
                            if ((!item.moi_quan_he || (!item.moi_quan_he.tich_cuc && !item.moi_quan_he.tieu_cuc)) &&
                                (!item.tinh_cach || (!item.tinh_cach.tich_cuc && !item.tinh_cach.tieu_cuc))) {
                                return '';
                            }
                            let header = item.label;
                            if (item.can || item.thap_than) {
                                header += ': ';
                                if (item.can) header += item.can;
                                if (item.thap_than) header += (item.can ? ' – ' : '') + item.thap_than;
                            }
                            let html = '<div class="mb-4">';
                            html += `<h4 class="font-semibold text-indigo-700 mb-2">${header}</h4>`;
                            if (item.moi_quan_he && (item.moi_quan_he.tich_cuc || item.moi_quan_he.tieu_cuc)) {
                                if (item.moi_quan_he.tich_cuc) {
                                    html +=
                                        `<div class="text-sm text-green-700 whitespace-pre-line mb-2">${item.moi_quan_he.tich_cuc}</div>`;
                                }
                                if (item.moi_quan_he.tieu_cuc) {
                                    html +=
                                        `<div class="text-sm text-red-700 whitespace-pre-line mb-2">${item.moi_quan_he.tieu_cuc}</div>`;
                                }
                            }
                            if (item.tinh_cach && (item.tinh_cach.tich_cuc || item.tinh_cach.tieu_cuc)) {
                                if (item.tinh_cach.tich_cuc) {
                                    html +=
                                        `<div class="text-sm text-green-700 whitespace-pre-line mb-2">${item.tinh_cach.tich_cuc}</div>`;
                                }
                                if (item.tinh_cach.tieu_cuc) {
                                    html +=
                                        `<div class="text-sm text-red-700 whitespace-pre-line">${item.tinh_cach.tieu_cuc}</div>`;
                                }
                            }
                            html += '</div>';
                            return html;
                        };

                        const thienCanBlock = data.thien_can_nam ? buildBlockHtml({
                            label: 'Thiên Can Năm',
                            can: null,
                            thap_than: data.thien_can_nam.thap_than || '',
                            moi_quan_he: data.thien_can_nam.moi_quan_he_xa_hoi || null,
                            tinh_cach: data.thien_can_nam.tinh_cach_xa_hoi || null,
                        }) : '';
                        let tangCanCol = '';
                        if (data.tang_can_nam && Array.isArray(data.tang_can_nam)) {
                            data.tang_can_nam.forEach(function(item) {
                                if (!item) return;
                                tangCanCol += buildBlockHtml({
                                    label: 'Tàng Can Năm',
                                    can: item.can_tang || '',
                                    thap_than: item.thap_than || '',
                                    moi_quan_he: item.moi_quan_he_xa_hoi || null,
                                    tinh_cach: item.tinh_cach_xa_hoi || null,
                                });
                            });
                        }
                        const hasThienCan = thienCanBlock !== '';
                        const hasTangCan = tangCanCol !== '';

                        if (hasThienCan && hasTangCan) {
                            container.html('<div class="space-y-6">' + thienCanBlock + tangCanCol + '</div>');
                        } else if (hasThienCan) {
                            container.html(thienCanBlock);
                        } else if (hasTangCan) {
                            container.html(tangCanCol);
                        }
                    }

                    function renderGiaiPhapThapThan(list) {
                        const container = $('#giaiPhapThapThanContainer');
                        container.empty();
                        if (!list || !Array.isArray(list) || list.length === 0) return;

                        list.forEach(function(item) {
                            if (!item) return;
                            const thapThan = item.thap_than || '';
                            const content = item.content || '';
                            if (!thapThan && !content) return;

                            let html = '<div class="mb-4">';
                            if (thapThan) {
                                html += `<h4 class="font-semibold text-indigo-700 mb-2">${thapThan}</h4>`;
                            }
                            if (content) {
                                html += `<div class="text-sm text-gray-700 whitespace-pre-line">${content}</div>`;
                            }
                            html += '</div>';
                            container.append(html);
                        });
                    }

                    // Hàm hiển thị kết quả
                    function displayResults(params, result, onPhan2Ready) {
                        // Hiển thị container kết quả
                        $('#resultContainer').show();
                        $('#quyenTabBar').addClass('is-visible');
                        activeQuyenCuon = 1;
                        $('#quyenTabBar .quyen-tab-btn')
                            .removeClass('active')
                            .attr('aria-selected', 'false')
                            .filter('[data-quyen="1"]')
                            .addClass('active')
                            .attr('aria-selected', 'true');
                        $('.quyen-section').not('.quyen-shared').each(function() {
                            $(this).removeData('quyen-was-shown').hide();
                        });
                        mountPhan8Group();
                        refreshQuyenSectionsVisibility();

                        // Load Tổng quan các khía cạnh từ API riêng (không dùng bazi/calc)
                        loadTongQuanKhiaCanh();

                        // PHẦN 2: BIỂU ĐỒ 6 KHÍA CẠNH + Thần Sát (API riêng, không lấy từ bazi/calc)
                        loadPhan2(params, onPhan2Ready);

                        // Load Sự nghiệp Thập Thần từ API riêng (có dùng ngày giờ sinh)
                        loadSuNghiepThapThan(params, result.bat_tu, result.chat_luong_thap_than);

                        // Load PHẦN 7: Bài học cuộc sống (API riêng)
                        loadBaiHocCuocSong(params);

                        // Load PHẦN 6: Mã 1 Ý nghĩa tứ trụ + Mã 2,3,4 Dòng chảy năng lượng
                        loadPhan6(params);

                        // Load PHẦN 9A (cuốn 1) + PHẦN 9B (cuốn 2)
                        loadPhan9(params, result);
                        loadPhan9b(params, result.chat_luong_thap_than, result);

                        // Load PHẦN 8: Đại Vận – mối quan hệ TC/ĐC Đại Vận với 4 Trụ
                        loadPhan8(params);

                        // Load PHẦN 8: Niên Vận – mối quan hệ TC/ĐC Niên Vận với 4 Trụ
                        loadPhan8NienVan(params);

                        // Load PHẦN 8 - III: Dự báo các khía cạnh cuộc sống (API riêng)
                        loadPhan8DuBaoKhiaCanh(params);

                        // Load PHẦN 8 - IV: Những năm cần chú ý
                        loadPhan8NhungNamCanChuY(params);

                        switchQuyenCuon(1);
                        setTimeout(function() {
                            refreshQuyenSectionsVisibility();
                            refreshPhan8QuyenVisibility();
                            refreshPhan9QuyenVisibility();
                        }, 3500);

                        // Điền dữ liệu Dương lịch
                        $('#yangli-year').text(params.y);
                        $('#yangli-month').text(params.m);
                        $('#yangli-day').text(params.d);
                        // Nếu không truyền giờ sinh thì để trống phần giờ
                        if (params.h === undefined || params.h === null || params.h === '') {
                            $('#yangli-hour').text('');
                        } else {
                            $('#yangli-hour').text(params.h + ':' + (params.minute || '00'));
                        }

                        // Nếu không có giờ sinh truyền lên, xóa nội dung các phần liên quan tới giờ
                        if (params.h === undefined || params.h === null || params.h === '') {
                            $('#thien-can-hour-value').text('');
                            $('#thien-can-hour-menh').text('');
                            $('#thien-can-hour-chutinh').text('');
                            $('#dia-chi-hour-value').text('');
                            $('#dia-chi-hour-menh').text('');
                            $('#dia-chi-hour-khongvong').text('');
                            $('#tang-can-hour').empty();
                        }

                        $.each(result.bat_tu, function(key, value) {
                            let thienCan = value.can;
                            // Nếu là trụ giờ và không có giờ sinh, bỏ qua việc gán giá trị
                            if (key === 'hour' && (params.h === undefined || params.h === null || params.h ===
                                    '')) {
                                // skip assigning hour values
                            } else {
                                $('#thien-can-' + key + '-value').text(value.can.thien_can || '');
                                $('#thien-can-' + key + '-menh').text(value.can.menh || '');
                                $('#thien-can-' + key + '-chutinh').text(value.can.chu_tinh || '');
                            }
                            let diaChi = value.chi;
                            if (key === 'hour' && (params.h === undefined || params.h === null || params.h ===
                                    '')) {
                                // skip assigning hour chi
                            } else {
                                $('#dia-chi-' + key + '-value').text(value.chi.dia_chi || '');
                                $('#dia-chi-' + key + '-menh').text(value.chi.menh || '');
                                // Hiển thị (Không vong) nếu có
                                if (value.chi.khong_vong === true) {
                                    $('#dia-chi-' + key + '-khongvong').text('(Không vong)');
                                } else {
                                    $('#dia-chi-' + key + '-khongvong').text('');
                                }
                            }
                            let canTang = value.can_tang;
                            // Xóa nội dung cũ trước khi thêm mới (tránh ghép kết quả khi click nhiều lần)
                            $('#tang-can-' + key).empty();
                            $.each(canTang, function(keyCanTang, valueCanTang) {
                                // Nếu là giờ nhưng không truyền giờ sinh thì bỏ qua
                                if (key === 'hour' && (params.h === undefined || params.h === null || params
                                        .h === '')) return;
                                $('#tang-can-' + key).append(
                                    `<div class="tang-can-cell"><p class="font-bold">${valueCanTang.can_tang}</p><p>${valueCanTang.menh}</p><p class="ten-gods">${valueCanTang.pho_tinh}</p></div>`
                                );
                            });
                        });

                        // Hiển thị Mệnh với màu sắc tương ứng
                        if (result.menh) {
                            const menhValue = result.menh;
                            $('#menhValue').text(menhValue);

                            // Map màu sắc và icon cho từng ngũ hành
                            const menhStyles = {
                                'Kim': {
                                    gradient: 'from-gray-400 to-gray-600',
                                    icon: 'fa-gem'
                                },
                                'Mộc': {
                                    gradient: 'from-green-400 to-green-600',
                                    icon: 'fa-tree'
                                },
                                'Thủy': {
                                    gradient: 'from-blue-400 to-blue-600',
                                    icon: 'fa-water'
                                },
                                'Hỏa': {
                                    gradient: 'from-red-400 to-red-600',
                                    icon: 'fa-fire'
                                },
                                'Thổ': {
                                    gradient: 'from-yellow-400 to-yellow-600',
                                    icon: 'fa-mountain'
                                }
                            };

                            const style = menhStyles[menhValue] || menhStyles['Kim'];
                            $('#menhCard').removeClass().addClass(
                                `bg-gradient-to-r ${style.gradient} rounded-xl p-6 shadow-lg`);
                            $('#menhIcon').removeClass().addClass(`fas ${style.icon} text-white text-4xl`);

                            $('#menhSection').show();
                        } else {
                            $('#menhSection').hide();
                        }

                        // Hiển thị Chất Lượng Thập Thần (API đã lọc chỉ trả bản mệnh > 0%)
                        displayChatLuongThapThan(result.chat_luong_thap_than, new Date().getFullYear());

                        // Hiển thị Ngũ hành động
                        displayNguHanhDong(result.ngu_hanh_dong, result.luc_than, result.phan_tram_nien_van);

                        // Lưu danh sách sim gốc và mệnh
                        allSims = result.sims || [];
                        currentMenh = result.menh || '';

                        // Hiển thị danh sách Sim
                        displaySims(allSims);

                        // Setup event listeners cho filter và search
                        setupSimFilters();

                        // Điền dữ liệu Tinh an
                        if (result.tinh_an && Array.isArray(result.tinh_an)) {
                            let tinhanHtml = '';
                            result.tinh_an.forEach(function(item) {
                                tinhanHtml += `
                            <tr>
                                <td>${item.menh || ''}</td>
                                <td>${item.sao || ''}</td>
                                <td>${item.diem || ''} %</td>
                            </tr>
                        `;
                            });
                            $('#tinhan-body').html(tinhanHtml);
                        }
                        let hy_ky_than = result.hy_ky_than;
                        let dungthannhatchu =
                            `<p>Nhật Chủ: (${hy_ky_than.than_nhuoc_than_vuong}).</p>
                    <p><strong>HỶ THẦN:</strong> <span>${hy_ky_than.hy_than_ngu_hanh}</span></p>
                    <p><strong>Kỵ THẦN:</strong> <span>${hy_ky_than.ky_than_ngu_hanh}</span></p>`;
                        $('#dungthannhatchu').html(dungthannhatchu);

                        displayDungThanInfo(result);
                        displayTongQuanTinhCach(result);
                        displayDiemSimKhachHang(result);
                        displayDaiVan(result.bang_dai_van);
                        displayNienVan(result.nien_van);
                        loadPhan3TongQuanNguHanh(result.hanh_noi_dung_nien_van || []);
                        loadPhan3ChatLuongNhatChu(params);
                        displayNhatChuTruNgay(result.nhat_chu_tru_ngay_view || {});
                    }

                    /** PHẦN 2: chi_so_bieu_do_cot + quy_nhan_van_xuong (gọi lại bazi->calc phía server). */
                    function loadPhan2(params, onComplete) {
                        $.ajax({
                            url: '/api/phan-2/chi-so-khia-canh-than-sat',
                            method: 'GET',
                            dataType: 'json',
                            data: params,
                            success: function(res) {
                                const d = (res && res.data) ? res.data : {};
                                displayChiSoBieuDoCot(d.chi_so_bieu_do_cot, params.g);
                                displayQuyNhanVanXuong(d.quy_nhan_van_xuong);
                                if (typeof onComplete === 'function') {
                                    onComplete(d);
                                }
                            },
                            error: function() {
                                displayChiSoBieuDoCot(null, params.g);
                                displayQuyNhanVanXuong(null);
                                if (typeof onComplete === 'function') {
                                    onComplete(null);
                                }
                            }
                        });
                    }

                    let phan3ImageMap = {};

                    function loadPhan3TongQuanNguHanh(hanhNoiDungNienVan) {
                        $.ajax({
                            url: '/api/phan-3/tong-quan-ngu-hanh',
                            method: 'GET',
                            dataType: 'json',
                            success: function(res) {
                                phan3ImageMap = (res && res.image_map) ? res.image_map : {};
                                const list = (res && res.data) ? res.data : [];
                                displayHanhNoiDungNienVan(hanhNoiDungNienVan || [], list);
                            },
                            error: function() {
                                phan3ImageMap = {};
                                displayHanhNoiDungNienVan(hanhNoiDungNienVan || [], []);
                            }
                        });
                    }

                    function loadPhan3ChatLuongNhatChu(params) {
                        $.ajax({
                            url: '/api/phan-3/chat-luong-nhat-chu',
                            method: 'GET',
                            dataType: 'json',
                            data: params,
                            success: function(res) {
                                displayChatLuongNhatChu((res && res.data) ? res.data : {});
                            },
                            error: function() {
                                $('#chatLuongNhatChuSection').hide();
                            }
                        });
                    }

                    function displayDaiVan(bangDaiVan) {
                        if (!bangDaiVan || bangDaiVan.length === 0) {
                            $('#daiVanSection').hide();
                            return;
                        }

                        const daiVanTable = $('#daiVanTable');
                        daiVanTable.empty();

                        // Start tbody (no thead)
                        let bodyHtml = '<tbody>';

                        // Row 1: Tuổi (Age)
                        bodyHtml += '<tr><td>Tuổi</td>';
                        bangDaiVan.forEach(function(dv) {
                            bodyHtml += `<td>${dv.age}</td>`;
                        });
                        bodyHtml += '</tr>';

                        // Row 2: Thiên Can
                        bodyHtml += '<tr><td>Thiên Can</td>';
                        bangDaiVan.forEach(function(dv) {
                            bodyHtml += '<td>';
                            if (dv.can && typeof dv.can === 'object') {
                                // Hiển thị Thiên Can
                                bodyHtml += `<div class="dai-van-can">${dv.can.thien_can || ''}</div>`;
                                // Hiển thị Âm Dương + Mệnh
                                if (dv.can.am_duong || dv.can.menh) {
                                    bodyHtml +=
                                        `<div class="dai-van-element">${dv.can.am_duong || ''} ${dv.can.menh || ''}</div>`;
                                }
                                // Hiển thị Chủ Tinh (Thập Thần)
                                if (dv.can.chu_tinh) {
                                    bodyHtml += `<div class="dai-van-thapthan">${dv.can.chu_tinh}</div>`;
                                }
                            }
                            bodyHtml += '</td>';
                        });
                        bodyHtml += '</tr>';

                        // Row 3: Địa Chi
                        bodyHtml += '<tr><td>Địa Chi</td>';
                        bangDaiVan.forEach(function(dv) {
                            bodyHtml += '<td>';
                            if (dv.chi && typeof dv.chi === 'object') {
                                // Hiển thị Địa Chi
                                bodyHtml += `<div class="dai-van-can">${dv.chi.dia_chi || ''}</div>`;
                                // Hiển thị Âm Dương + Mệnh
                                if (dv.chi.am_duong || dv.chi.menh) {
                                    bodyHtml +=
                                        `<div class="dai-van-element">${dv.chi.am_duong || ''} ${dv.chi.menh || ''}</div>`;
                                }
                                // Hiển thị (Không vong) nếu có
                                if (dv.chi.khong_vong === true) {
                                    bodyHtml += `<div class="dai-van-khongvong">(Không vong)</div>`;
                                }
                            }
                            bodyHtml += '</td>';
                        });
                        bodyHtml += '</tr>';

                        // Row 4: Tàng Can
                        bodyHtml += '<tr><td>Tàng Can</td>';
                        bangDaiVan.forEach(function(dv) {
                            bodyHtml += '<td>';
                            if (dv.cantang && Array.isArray(dv.cantang)) {
                                dv.cantang.forEach(function(tc) {
                                    bodyHtml += '<div class="tang-can-item">';
                                    bodyHtml += `<div class="tc-can">${tc.can_tang || ''}</div>`;
                                    if (tc.menh) {
                                        bodyHtml += `<div class="tc-element">${tc.menh}</div>`;
                                    }
                                    if (tc.pho_tinh) {
                                        bodyHtml += `<div class="tc-thapthan">${tc.pho_tinh}</div>`;
                                    }
                                    bodyHtml += '</div>';
                                });
                            }
                            bodyHtml += '</td>';
                        });
                        bodyHtml += '</tr>';

                        // Rows for years
                        const maxYears = Math.max(...bangDaiVan.map(dv => dv.list_year ? dv.list_year.length : 0));
                        for (let i = 0; i < maxYears; i++) {
                            bodyHtml += '<tr>';
                            bodyHtml += `<td></td>`; // Empty label cell

                            bangDaiVan.forEach(function(dv) {
                                bodyHtml += '<td>';
                                if (dv.list_year && dv.list_year[i]) {
                                    const year = dv.list_year[i];
                                    bodyHtml += '<div class="year-item">';
                                    bodyHtml += `<div class="year-canchi">${year.can_chi || ''}</div>`;
                                    bodyHtml += `<div class="year-number">${year.nam || ''}</div>`;
                                    // Hiển thị (Không vong) nếu có
                                    if (year.khong_vong === true) {
                                        bodyHtml += `<div class="year-note">(Không vong)</div>`;
                                    }
                                    bodyHtml += '</div>';
                                }
                                bodyHtml += '</td>';
                            });
                            bodyHtml += '</tr>';
                        }

                        bodyHtml += '</tbody>';

                        daiVanTable.html(bodyHtml);
                        $('#daiVanSection').show();
                    }

                    function displayNienVan(nienVan) {
                        if (!nienVan || nienVan.length === 0) {
                            $('#nienVanSection').hide();
                            return;
                        }

                        const nienVanTable = $('#nienVanTable');
                        nienVanTable.empty();

                        // Header
                        let headerHtml = '<thead><tr><td>NIÊN VẬN</td>';
                        nienVan.forEach(function(nv) {
                            headerHtml += `<td class="text-center">${nv.nam}</td>`;
                        });
                        headerHtml += '</tr></thead>';

                        // Start tbody
                        let bodyHtml = '<tbody>';

                        // Row 1: Thiên Can
                        bodyHtml += '<tr><td>Thiên can</td>';
                        nienVan.forEach(function(nv) {
                            bodyHtml += '<td class="text-center">';
                            if (nv.can && typeof nv.can === 'object') {
                                bodyHtml += `<p class="header-cs-0">${nv.can.thien_can || ''}</p>`;
                                bodyHtml += `<p>${nv.can.am_duong || ''} ${nv.can.menh || ''}</p>`;
                                if (nv.can.chu_tinh) {
                                    bodyHtml += `<p class="ten-gods">${nv.can.chu_tinh}</p>`;
                                }
                            }
                            bodyHtml += '</td>';
                        });
                        bodyHtml += '</tr>';

                        // Row 2: Địa Chi
                        bodyHtml += '<tr><td>Địa chi</td>';
                        nienVan.forEach(function(nv) {
                            bodyHtml += '<td class="text-center">';
                            if (nv.chi && typeof nv.chi === 'object') {
                                bodyHtml += `<p class="header-cs-0">${nv.chi.dia_chi || ''}</p>`;
                                bodyHtml += `<p>${nv.chi.am_duong || ''} ${nv.chi.menh || ''}</p>`;
                                if (nv.chi.khong_vong === true) {
                                    bodyHtml += `<p class="text-sm italic text-gray-500">(Không vong)</p>`;
                                }
                            }
                            bodyHtml += '</td>';
                        });
                        bodyHtml += '</tr>';

                        // Row 3: Tàng Can
                        bodyHtml += '<tr><td>Tàng Can</td>';
                        nienVan.forEach(function(nv) {
                            bodyHtml += '<td class="text-center">';
                            bodyHtml += '<div class="flex justify-center">';
                            if (nv.cantang && Array.isArray(nv.cantang)) {
                                nv.cantang.forEach(function(tc) {
                                    bodyHtml +=
                                        '<div style="text-align: center; padding-left: 5px; padding-right: 5px;">';
                                    bodyHtml += `<p class="font-bold">${tc.can_tang || ''}</p>`;
                                    bodyHtml += `<p>${tc.menh || ''}</p>`;
                                    if (tc.pho_tinh) {
                                        bodyHtml += `<p class="ten-gods">${tc.pho_tinh}</p>`;
                                    }
                                    bodyHtml += '</div>';
                                });
                            }
                            bodyHtml += '</div>';
                            bodyHtml += '</td>';
                        });
                        bodyHtml += '</tr>';

                        bodyHtml += '</tbody>';

                        nienVanTable.html(headerHtml + bodyHtml);
                        $('#nienVanSection').show();
                    }

                    function formatPhan3Content(content) {
                        if (!content) return '';
                        const markerRe = /\[\[image:([^\]]+)\]\]/g;
                        let html = '';
                        let last = 0;
                        let match;
                        while ((match = markerRe.exec(content)) !== null) {
                            html += $('<div/>').text(content.slice(last, match.index)).html()
                                .replace(/\n\n/g, '<br><br>')
                                .replace(/\n/g, '<br>');
                            const url = phan3ImageMap[match[1]] || ('/images/pdfs/quyen-1/' + match[1].split('/').pop());
                            html += `<img src="${url}" class="max-w-full rounded-lg my-3 block" alt="">`;
                            last = match.index + match[0].length;
                        }
                        html += $('<div/>').text(content.slice(last)).html()
                            .replace(/\n\n/g, '<br><br>')
                            .replace(/\n/g, '<br>');
                        return html;
                    }

                    function displayHanhNoiDungNienVan(data, dinhViList) {
                        const section = $('#hanhNoiDungNienVanSection');
                        const container = $('#hanhNoiDungNienVanContainer');
                        const hasData = data && data.length > 0;
                        const hasDinhVi = Array.isArray(dinhViList) && dinhViList.length > 0;
                        if (!hasData && !hasDinhVi) {
                            section.hide();
                            return;
                        }
                        let html = '';
                        if (hasDinhVi) {
                            dinhViList.forEach(function(item, idx) {
                                if (!item) return;
                                html +=
                                    '<div class="bg-white rounded-lg shadow-lg p-6 mb-4 border-l-4 border-amber-300">';
                                if (item.title) {
                                    html += `<h4 class="text-lg font-bold mb-3 form-title">${item.title}</h4>`;
                                }
                                if (item.content) {
                                    html += `<div class="text-gray-700">${formatPhan3Content(item.content)}</div>`;
                                }
                                html += '</div>';
                            });
                        }
                        if (hasData) {
                            data.forEach(function(item) {
                                html += '<div class="bg-white rounded-lg shadow-lg p-6 mb-4">';
                                html +=
                                    `<h4 class="text-lg font-bold mb-3 form-title">${item.hanh_name || ''} – ${item.percent}%</h4>`;
                                if (item.items && item.items.length > 0) {
                                    item.items.forEach(function(it) {
                                        if (it.title) {
                                            html +=
                                                `<h5 class="font-semibold text-gray-800 mb-1">${it.title}</h5>`;
                                        }
                                        if (it.content) {
                                            html +=
                                                `<div class="text-gray-600 whitespace-pre-wrap mb-3">${it.content}</div>`;
                                        }
                                    });
                                }
                                html += '</div>';
                            });
                        }
                        container.html(html);
                        section.show();
                    }

                    function displayChatLuongNhatChu(data) {
                        const section = $('#chatLuongNhatChuSection');
                        const container = $('#chatLuongNhatChuContainer');
                        if (!data || ((!data.items || data.items.length === 0) && !data.mua_sinh)) {
                            section.hide();
                            return;
                        }
                        let html = '';
                        if (data.mua_sinh || data.ngu_hanh_mua_sinh) {
                            html += '<div class="mb-4 text-gray-700">';
                            if (data.mua_sinh) {
                                html += `<span class="font-semibold">Mùa sinh:</span> ${data.mua_sinh}`;
                            }
                            if (data.ngu_hanh_mua_sinh) {
                                html += (data.mua_sinh ? ' &nbsp;|&nbsp; ' : '') +
                                    `<span class="font-semibold">Ngũ hành mùa sinh:</span> ${data.ngu_hanh_mua_sinh}`;
                            }
                            html += '</div>';
                        }
                        if (data.items && data.items.length > 0) {
                            data.items.forEach(function(item) {
                                html += '<div class="bg-white rounded-lg shadow-lg p-6 mb-4">';
                                if (item.title) {
                                    html += `<h5 class="font-semibold text-gray-800 mb-1">${item.title}</h5>`;
                                }
                                if (item.trang_thai) {
                                    html += `<p class="text-sm text-indigo-600 mb-2">${item.trang_thai}</p>`;
                                }
                                if (item.content) {
                                    html += `<div class="text-gray-600 whitespace-pre-wrap">${item.content}</div>`;
                                }
                                html += '</div>';
                            });
                        }
                        container.html(html);
                        section.show();
                    }

                    function displayNhatChuTruNgay(data) {
                        const section = $('#nhatChuTruNgaySection');
                        const container = $('#nhatChuTruNgayContainer');
                        if (!data || ((!data.chapters || data.chapters.length === 0) && !data.tru_ngay)) {
                            section.hide();
                            return;
                        }
                        let html = '';
                        if (data.tru_ngay) {
                            html += '<div class="mb-4"><span class="text-lg font-bold text-indigo-600">' + data.tru_ngay +
                                '</span></div>';
                        }
                        html += '<div class="bg-white rounded-lg shadow-lg p-6 mb-4">';
                        if (data.title) {
                            html += '<h5 class="font-semibold text-gray-800 mb-4">' + data.title + '</h5>';
                        }
                        if (data.chapters && data.chapters.length > 0) {
                            data.chapters.forEach(function(ch) {
                                if (ch.chapter) {
                                    html += '<h6 class="font-medium text-gray-700 mt-4 mb-2">' + ch.chapter +
                                        '</h6>';
                                }
                                if (ch.sub_sections && ch.sub_sections.length > 0) {
                                    ch.sub_sections.forEach(function(sec) {
                                        if (sec.sub_title) {
                                            html +=
                                                '<h6 class="text-sm text-gray-600 mt-3 mb-1 font-medium">' +
                                                sec.sub_title + '</h6>';
                                        }
                                        if (sec.content) {
                                            html += '<div class="text-gray-600 whitespace-pre-wrap mb-2">' +
                                                sec.content + '</div>';
                                        }
                                    });
                                }
                            });
                        }
                        if (data.vi_nhan && data.vi_nhan.length > 0) {
                            html += '<h6 class="font-medium text-gray-700 mt-4 mb-2">V. Ví dụ về vĩ nhân</h6>';
                            html += '<div class="text-gray-600">' + data.vi_nhan.join(', ') + '</div>';
                        }
                        html += '</div>';
                        container.html(html);
                        section.show();
                    }

                    function displayDiemSimKhachHang(result) {
                        // Kiểm tra xem có dữ liệu chấm điểm sim không
                        if (result.diem_sim_khach_hang) {
                            const diemSim = result.diem_sim_khach_hang;

                            // Hiển thị section
                            $('#diemSimSection').show();

                            // Cập nhật điểm số (lấy từ tong_diem)
                            $('#diemSimScore').text(diemSim.tong_diem || 0);

                            // Cập nhật loại sim (lấy từ type)
                            $('#diemSimLoai').text(diemSim.type || '-');

                            // Lưu dữ liệu quẻ gốc để hiển thị trong modal
                            $('#btnXemChiTietQue').data('que-goc', diemSim.tong_quan_que_goc);
                        } else {
                            $('#diemSimSection').hide();
                        }
                    }

                    function displayDungThanInfo(result) {
                        const dungThanDetailContent = $('#dungthanDetailContent');
                        dungThanDetailContent.empty();

                        // Lấy thông tin Dụng Thần từ kết quả API
                        let dungThanText = result.hy_ky_than.hy_than_ngu_hanh;

                        if (!dungThanText) {
                            dungThanDetailContent.html('<p class="text-gray-600">Không có thông tin Dụng Thần</p>');
                            return;
                        }

                        // Map icons và màu sắc cho từng ngũ hành
                        const elementIcons = {
                            'Kim': {
                                icon: 'fa-gem',
                                gradient: 'from-gray-400 to-gray-600',
                                bg: 'bg-gray-50',
                                border: 'border-gray-400'
                            },
                            'Mộc': {
                                icon: 'fa-tree',
                                gradient: 'from-green-400 to-green-600',
                                bg: 'bg-green-50',
                                border: 'border-green-500'
                            },
                            'Thủy': {
                                icon: 'fa-water',
                                gradient: 'from-blue-400 to-blue-600',
                                bg: 'bg-blue-50',
                                border: 'border-blue-500'
                            },
                            'Hỏa': {
                                icon: 'fa-fire',
                                gradient: 'from-red-400 to-red-600',
                                bg: 'bg-red-50',
                                border: 'border-red-500'
                            },
                            'Thổ': {
                                icon: 'fa-mountain',
                                gradient: 'from-yellow-400 to-yellow-600',
                                bg: 'bg-yellow-50',
                                border: 'border-yellow-500'
                            }
                        };

                        // Tách các hành từ chuỗi Dụng Thần
                        const elements = dungThanText.split(',').map(el => el.trim());
                        console.log(elements);
                        let htmlContent = '';

                        $.each(result.giai_phap_can_bang, function(key, value) {
                            console.log(value.ngu_hanh);
                            if (elements.includes(value.ngu_hanh)) {
                                const elementStyle = elementIcons[value.ngu_hanh] || elementIcons['kim'];

                                htmlContent += `
            <div class="mb-8 transform transition-all duration-300 hover:scale-[1.02]">
                <!-- Header Card với Gradient -->
                <div class="bg-gradient-to-r ${elementStyle.gradient} rounded-t-2xl p-6 shadow-lg">
                    <div class="flex items-center justify-between text-white">
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center backdrop-blur-sm">
                                <i class="fas ${elementStyle.icon} text-3xl"></i>
                            </div>
                            <div>
                                <h4 class="text-2xl font-bold mb-1">Hỷ Thần: ${value.ngu_hanh}</h4>
                                <p class="text-white text-opacity-90 text-sm">Giải pháp cân bằng năng lượng</p>
                            </div>
                        </div>
                        <div class="hidden md:block">
                            <div class="w-20 h-20 bg-white bg-opacity-10 rounded-full"></div>
                        </div>
                    </div>
                </div>

                <!-- Content Card -->
                <div class="bg-white rounded-b-2xl shadow-xl overflow-hidden border-l-4 ${elementStyle.border}">
                    <!-- Hành Động -->
                    <div class="p-6 border-b border-gray-100 hover:bg-gray-50 transition-colors duration-200">
                        <div class="flex items-start mb-4">
                            <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br ${elementStyle.gradient} rounded-lg flex items-center justify-center mr-4 shadow-md">
                                <i class="fas fa-running text-white text-lg"></i>
                            </div>
                            <div class="flex-1">
                                <h5 class="text-lg font-bold text-gray-800 mb-3">Hành Động Cụ Thể</h5>
                                <div class="space-y-3">
                                    ${value.giai_phap.hanh_dong.map((item, index) => `
                                                                                                                            <div class="flex items-start group">
                                                                                                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gradient-to-br ${elementStyle.gradient} text-white text-xs font-bold mr-3 flex-shrink-0 mt-0.5 group-hover:scale-110 transition-transform">
                                                                                                                                    ${index + 1}
                                                                                                                                </span>
                                                                                                                                <p class="text-gray-700 leading-relaxed flex-1">${item}</p>
                                                                                                                            </div>
                                                                                                                        `).join('')}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tư Duy -->
                    <div class="p-6 border-b border-gray-100 hover:bg-gray-50 transition-colors duration-200">
                        <div class="flex items-start mb-4">
                            <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br ${elementStyle.gradient} rounded-lg flex items-center justify-center mr-4 shadow-md">
                                <i class="fas fa-brain text-white text-lg"></i>
                            </div>
                            <div class="flex-1">
                                <h5 class="text-lg font-bold text-gray-800 mb-3">Tư Duy & Mindset</h5>
                                <div class="space-y-3">
                                    ${value.giai_phap.tu_duy.map(item => `
                                                                                                                            <div class="flex items-start group">
                                                                                                                                <i class="fas fa-check-circle text-${value.ngu_hanh === 'Kim' ? 'gray' : value.ngu_hanh === 'Mộc' ? 'green' : value.ngu_hanh === 'Thủy' ? 'blue' : value.ngu_hanh === 'Hỏa' ? 'red' : 'yellow'}-500 mr-3 mt-1 group-hover:scale-125 transition-transform"></i>
                                                                                                                                <p class="text-gray-700 leading-relaxed flex-1">${item}</p>
                                                                                                                            </div>
                                                                                                                        `).join('')}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Màu Sắc và Đá Phong Thủy -->
                    <div class="p-6 ${elementStyle.bg}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Màu Sắc -->
                            <div class="bg-white p-5 rounded-xl shadow-md hover:shadow-lg transition-shadow duration-300">
                                    <div class="flex items-center mb-4">
                                    <div class="w-10 h-10 bg-gradient-to-br ${elementStyle.gradient} rounded-lg flex items-center justify-center mr-3 shadow-md">
                                        <i class="fas fa-palette text-white"></i>
                                    </div> 
                                    <h5 class="text-lg font-bold text-gray-800">Màu Sắc May Mắn</h5>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    ${value.giai_phap.mau_sac.map(color => `
                                                                                                                            <span class="px-4 py-2 bg-gradient-to-r ${elementStyle.gradient} text-white rounded-full text-sm font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-1 transition-all duration-200 cursor-default">
                                                                                                                                ${color}
                                                                                                                            </span>
                                                                                                                        `).join('')}
                                </div>
                            </div>

                            <!-- Đá Phong Thủy -->
                            <div class="bg-white p-5 rounded-xl shadow-md hover:shadow-lg transition-shadow duration-300">
                                <div class="flex items-center mb-4">
                                    <div class="w-10 h-10 bg-gradient-to-br ${elementStyle.gradient} rounded-lg flex items-center justify-center mr-3 shadow-md">
                                        <i class="fas fa-gem text-white"></i>
                                    </div>
                                    <h5 class="text-lg font-bold text-gray-800">Đá Phong Thủy</h5>
                                </div>
                                <div class="space-y-2">
                                    ${value.giai_phap.da_phong_thuy.map(stone => `
                                                                                                                            <div class="flex items-center px-3 py-2 bg-gray-50 rounded-lg hover:bg-gradient-to-r hover:${elementStyle.gradient} hover:text-white transition-all duration-200 group">
                                                                                                                                <i class="fas fa-circle text-xs mr-2 opacity-50 group-hover:opacity-100"></i>
                                                                                                                                <span class="text-sm font-medium">${stone}</span>
                                                                                                                            </div>
                                                                                                                        `).join('')}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            `;
                            }
                        });

                        if (htmlContent === '') {
                            htmlContent = `
            <div class="text-center py-12">
                <i class="fas fa-info-circle text-gray-400 text-5xl mb-4"></i>
                <p class="text-gray-600 text-lg">Không tìm thấy thông tin chi tiết cho Hỷ Thần này</p>
            </div>
        `;
                        }

                        dungThanDetailContent.html(htmlContent);
                    }

                    function displayQuyNhanVanXuong(data) {
                        const section = $('#quyNhanVanXuongSection');
                        if (!data) {
                            section.addClass('hidden');
                            $('#qnvx-day-stem').text('');
                            $('#qnvx-quy-nhan').text('');
                            $('#qnvx-van-xuong').text('');
                            $('#qnvx-dia-chi-ngay').text('');
                            $('#qnvx-dich-ma').text('');
                            $('#qnvx-dao-hoa').text('');
                            $('#qnvx-co-than').text('');
                            return;
                        }

                        $('#qnvx-day-stem').text(data.thien_can_ngay || '');
                        $('#qnvx-quy-nhan').text(data.quy_nhan || '');
                        $('#qnvx-van-xuong').text(data.van_xuong || '');
                        $('#qnvx-dia-chi-ngay').text(data.dia_chi_ngay || '');
                        $('#qnvx-dich-ma').text(data.dich_ma || '');
                        $('#qnvx-dao-hoa').text(data.dao_hoa || '');
                        $('#qnvx-co-than').text(data.co_than || '');
                        section.removeClass('hidden');
                    }

                    function displayTongQuanTinhCach(result) {
                        const tongQuanContent = $('#tongquantinhcachContent');
                        tongQuanContent.empty();

                        // Lấy thông tin từ API response
                        const tinhCach = result.tong_quan_tinh_cach;

                        if (!tinhCach || Object.keys(tinhCach).length === 0) {
                            tongQuanContent.html('<p class="text-gray-600">Không có thông tin tổng quan tính cách</p>');
                            return;
                        }

                        // Tạo HTML content
                        let htmlContent = `
                            <div class="personality-overview bg-white rounded-lg p-6 shadow-md">
                                <!-- Header Section -->
                                <div class="mb-6 pb-4 border-b-2 border-indigo-500">
                                    <div class="flex items-center mb-3">
                                        <h4 class="text-2xl font-bold text-indigo-600">${tinhCach.can || ''}</h4>
                                        <span class="ml-3 px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm font-semibold">
                                            ${tinhCach.ngu_hanh || ''}
                                        </span>
                                    </div>
                                    ${tinhCach.hinh_tuong ? `
                                                                                                                            <p class="text-gray-600 mb-2">
                                                                                                                                <strong class="text-gray-800">Hình tượng:</strong> ${tinhCach.hinh_tuong}
                                                                                                                            </p>
                                                                                                                        ` : ''}
                                    ${tinhCach.bieu_tuong ? `
                                                                                                                            <p class="text-gray-600">
                                                                                                                                <strong class="text-gray-800">Biểu tượng:</strong> ${tinhCach.bieu_tuong}
                                                                                                                            </p>
                                                                                                                        ` : ''}
                                </div>

                                <!-- Tổng Quan -->
                                ${tinhCach.tong_quan ? `
                                                                                                                        <div class="mb-6">
                                                                                                                            <h5 class="text-lg font-bold text-gray-800 mb-3 flex items-center">
                                                                                                                                <i class="fas fa-user-circle mr-2 text-indigo-500"></i>
                                                                                                                                Tổng quan
                                                                                                                            </h5>
                                                                                                                            <p class="text-gray-700 leading-relaxed bg-gray-50 p-4 rounded-lg">
                                                                                                                                ${tinhCach.tong_quan}
                                                                                                                            </p>
                                                                                                                        </div>
                                                                                                                    ` : ''}

                                <!-- Tư Duy Tính Cách -->
                                ${tinhCach.tu_duy_tinh_cach && Array.isArray(tinhCach.tu_duy_tinh_cach) && tinhCach.tu_duy_tinh_cach.length > 0 ? `
                                                                                                                        <div class="mb-6">
                                                                                                                            <h5 class="text-lg font-bold text-gray-800 mb-3 flex items-center">
                                                                                                                                <i class="fas fa-brain mr-2 text-purple-500"></i>
                                                                                                                                Tư duy & Tính cách
                                                                                                                            </h5>
                                                                                                                            <ul class="space-y-3">
                                                                                                                                ${tinhCach.tu_duy_tinh_cach.map(item => `
                                                <li class="flex items-start">
                                                    <span class="inline-block w-2 h-2 bg-purple-500 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                                                    <span class="text-gray-700 flex-1">${item}</span>
                                                </li>
                                                `).join('')}
                                                                                        </ul>
                                                                                    </div>
                                                                                                                    ` : ''}

                                <!-- Hành Vi Ứng Xử -->
                                ${tinhCach.hanh_vi_ung_xu && Array.isArray(tinhCach.hanh_vi_ung_xu) && tinhCach.hanh_vi_ung_xu.length > 0 ? `
                                                                                                                        <div class="mb-6">
                                                                                                                            <h5 class="text-lg font-bold text-gray-800 mb-3 flex items-center">
                                                                                                                                <i class="fas fa-users mr-2 text-blue-500"></i>
                                                                                                                                Hành vi ứng xử
                                                                                                                            </h5>
                                                                                                                            <ul class="space-y-3">
                                                                                                                                ${tinhCach.hanh_vi_ung_xu.map(item => `
                                                <li class="flex items-start">
                                                    <span class="inline-block w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                                                    <span class="text-gray-700 flex-1">${item}</span>
                                                </li>
                                                `).join('')}
                                                                                        </ul>
                                                                                    </div>
                                                                                                                    ` : ''}

                                <!-- Điểm Mạnh và Điểm Yếu -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <!-- Điểm Mạnh -->
                                    ${tinhCach.diem_manh && Array.isArray(tinhCach.diem_manh) && tinhCach.diem_manh.length > 0 ? `
                                                                                                                            <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-500">
                                                                                                                                <h5 class="text-lg font-bold text-green-700 mb-3 flex items-center">
                                                                                                                                    <i class="fas fa-check-circle mr-2"></i>
                                                                                                                                    Điểm mạnh
                                                                                                                                </h5>
                                                                                                                                <ul class="space-y-2">
                                                                                                                                    ${tinhCach.diem_manh.map(item => `
                                                    <li class="flex items-start">
                                                        <span class="inline-block w-2 h-2 bg-green-500 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                                                        <span class="text-gray-700 text-sm flex-1">${item}</span>
                                                    </li>
                                                `).join('')}
                                                                                        </ul>
                                                                                    </div>
                                                                                                                        ` : ''}

                                    <!-- Điểm Yếu -->
                                    ${tinhCach.diem_yeu && Array.isArray(tinhCach.diem_yeu) && tinhCach.diem_yeu.length > 0 ? `
                                                                                                                            <div class="bg-orange-50 p-4 rounded-lg border-l-4 border-orange-500">
                                                                                                                                <h5 class="text-lg font-bold text-orange-700 mb-3 flex items-center">
                                                                                                                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                                                                                                                    Điểm yếu cần lưu ý
                                                                                                                                </h5>
                                                                                                                                <ul class="space-y-2">
                                                                                                                                    ${tinhCach.diem_yeu.map(item => `
                                                    <li class="flex items-start">
                                                        <span class="inline-block w-2 h-2 bg-orange-500 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                                                        <span class="text-gray-700 text-sm flex-1">${item}</span>
                                                    </li>
                                                `).join('')}
                                                                                        </ul>
                                                                                    </div>
                                                                                                                        ` : ''}
                                </div>

                                <!-- Chiến Lược -->
                                ${tinhCach.chien_luoc ? `
                                                                                                                        <div class="bg-indigo-50 p-5 rounded-lg border-l-4 border-indigo-500">
                                                                                                                            <h5 class="text-lg font-bold text-indigo-700 mb-3 flex items-center">
                                                                                                                                <i class="fas fa-lightbulb mr-2"></i>
                                                                                                                                Chiến lược phát triển
                                                                                                                            </h5>
                                                                                                                            <p class="text-gray-700 leading-relaxed">
                                                                                                                                ${tinhCach.chien_luoc}
                                                                                                                            </p>
                                                                                                                        </div>
                                                                                                                    ` : ''}
                                </div>
                                `;

                        tongQuanContent.html(htmlContent);
                    }

                    function displayChatLuongThapThan(chatLuongData, currentYear = 2026) {
                        const tableContainer = $('#chatLuongThapThanTable');
                        const tableBody = $('#chatLuongThapThanBody');
                        const yearHeader = $('#nienMenhYear');

                        if (!chatLuongData || chatLuongData.length === 0) {
                            tableContainer.hide();
                            return;
                        }

                        // Update year in header
                        yearHeader.text(`NIÊN MỆNH ${currentYear}`);

                        // Clear existing rows
                        tableBody.empty();

                        // API đã lọc chỉ trả Thập Thần có bản mệnh > 0%
                        chatLuongData.forEach(item => {
                            const natalPercent = parseInt(item.natal) || 0;
                            const annualPercent = parseInt(item.annual) || 0;

                            const row = `
                                <tr>
                                    <td>${item.name}</td>
                                    <td style="padding: 4px 8px;">
                                        <div class="bar-container">
                                            ${natalPercent > 0
                                                ? `<div class="bar natal" style="width: ${natalPercent}%;">${natalPercent}%</div>`
                                                : `<span class="bar-zero-label">${natalPercent}%</span>`}
                                        </div>
                                    </td>
                                    <td style="padding: 4px 8px;">
                                        <div class="bar-container">
                                            ${annualPercent > 0
                                                ? `<div class="bar annual" style="width: ${annualPercent}%;">${annualPercent}%</div>`
                                                : `<span class="bar-zero-label">${annualPercent}%</span>`}
                                        </div>
                                    </td>
                                </tr>
                            `;
                            tableBody.append(row);
                        });

                        if (tableBody.children().length === 0) {
                            tableContainer.hide();
                        } else {
                            tableContainer.show();
                        }
                    }

                    function displayNguHanhDong(nguHanhDong, lucThan = null, phanTramNienVan = null) {
                        const nguHanhDongSection = $('#nguHanhDongSection');

                        if (!nguHanhDong || Object.keys(nguHanhDong).length === 0) {
                            nguHanhDongSection.hide();
                            return;
                        }

                        // Map tên ngũ hành từ key sang tiếng Việt có dấu
                        const nguHanhNames = {
                            'kim': 'Kim',
                            'moc': 'Mộc',
                            'thuy': 'Thủy',
                            'hoa': 'Hỏa',
                            'tho': 'Thổ'
                        };

                        // Map màu sắc cho từng ngũ hành (rgba format)
                        const nguHanhColors = {
                            'kim': 'rgba(156, 163, 175, 0.6)', // Gray
                            'moc': 'rgba(34, 197, 94, 0.6)', // Green
                            'thuy': 'rgba(59, 130, 246, 0.6)', // Blue
                            'hoa': 'rgba(239, 68, 68, 0.6)', // Red
                            'tho': 'rgba(234, 179, 8, 0.6)' // Yellow
                        };

                        const nguHanhBorderColors = {
                            'kim': 'rgba(156, 163, 175, 1)',
                            'moc': 'rgba(34, 197, 94, 1)',
                            'thuy': 'rgba(59, 130, 246, 1)',
                            'hoa': 'rgba(239, 68, 68, 1)',
                            'tho': 'rgba(234, 179, 8, 1)'
                        };

                        // Chuẩn bị dữ liệu cho radar chart
                        const labels = [];
                        const data = [];
                        const dataNienVan = [];
                        const backgroundColors = [];
                        const borderColors = [];

                        // Hiển thị theo thứ tự mà API trả về
                        Object.keys(nguHanhDong).forEach(key => {
                            if (nguHanhDong[key] !== undefined) {
                                // Tạo label với lục thần nếu có
                                let label = nguHanhNames[key] || key;
                                if (lucThan && lucThan[key]) {
                                    label = `${label} (${lucThan[key]})`;
                                }
                                labels.push(label);
                                data.push(nguHanhDong[key]);

                                // Thêm data niên vận nếu có
                                if (phanTramNienVan && phanTramNienVan[key] !== undefined) {
                                    dataNienVan.push(phanTramNienVan[key]);
                                } else {
                                    dataNienVan.push(0);
                                }

                                backgroundColors.push(nguHanhColors[key] || 'rgba(128, 128, 128, 0.6)');
                                borderColors.push(nguHanhBorderColors[key] || 'rgba(128, 128, 128, 1)');
                            }
                        });

                        // Hủy chart cũ nếu có
                        const chartCanvas = document.getElementById('nguHanhDongChart');
                        if (window.nguHanhChart instanceof Chart) {
                            window.nguHanhChart.destroy();
                        }

                        // Tạo radar chart mới
                        const ctx = chartCanvas.getContext('2d');

                        // Kiểm tra kích thước màn hình để điều chỉnh font size
                        const isMobile = window.innerWidth < 768;
                        const tickFontSize = isMobile ? 10 : 12;
                        const labelFontSize = isMobile ? 12 : 14;
                        const pointRadius = isMobile ? 5 : 6;
                        const legendFontSize = isMobile ? 12 : 14;

                        // Tạo datasets array
                        const datasets = [{
                            label: 'Bản Mệnh (%)',
                            data: data,
                            backgroundColor: 'rgba(79, 70, 229, 0.2)',
                            borderColor: 'rgba(79, 70, 229, 1)',
                            borderWidth: 2,
                            pointBackgroundColor: borderColors, // ✓ Màu theo ngũ hành
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: borderColors, // ✓ Hover cũng theo ngũ hành
                            pointHoverBorderColor: '#fff',
                            pointRadius: pointRadius,
                            pointHoverRadius: pointRadius + 2,
                            pointBorderWidth: 2
                        }];

                        // Thêm dataset Niên Vận nếu có data
                        if (phanTramNienVan && dataNienVan.some(val => val > 0)) {
                            datasets.push({
                                label: 'Niên Vận (%)',
                                data: dataNienVan,
                                backgroundColor: 'rgba(139, 69, 19, 0.2)', // Màu nâu
                                borderColor: 'rgba(139, 69, 19, 1)', // Màu nâu
                                borderWidth: 2,
                                pointBackgroundColor: borderColors, // ✓ Màu theo ngũ hành (không phải nâu)
                                pointBorderColor: '#fff',
                                pointHoverBackgroundColor: borderColors, // ✓ Hover cũng theo ngũ hành
                                pointHoverBorderColor: '#fff',
                                pointRadius: pointRadius,
                                pointHoverRadius: pointRadius + 2,
                                pointBorderWidth: 2
                            });
                        }

                        window.nguHanhChart = new Chart(ctx, {
                            type: 'radar',
                            data: {
                                labels: labels,
                                datasets: datasets
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                aspectRatio: isMobile ? 1 : 1.5,
                                scales: {
                                    r: {
                                        beginAtZero: true,
                                        max: 100,
                                        min: 0,
                                        // startAngle: 216,
                                        ticks: {
                                            stepSize: 20,
                                            font: {
                                                size: tickFontSize,
                                                weight: 'bold'
                                            }
                                        },
                                        pointLabels: {
                                            font: {
                                                size: labelFontSize,
                                                weight: 'bold'
                                            },
                                            color: '#1f2937',
                                            padding: isMobile ? 8 : 10
                                        },
                                        grid: {
                                            color: 'rgba(0, 0, 0, 0.1)'
                                        },
                                        angleLines: {
                                            color: 'rgba(0, 0, 0, 0.1)'
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'bottom',
                                        labels: {
                                            font: {
                                                size: legendFontSize,
                                                weight: 'bold'
                                            },
                                            padding: isMobile ? 12 : 20,
                                            boxWidth: isMobile ? 30 : 40
                                        }
                                    },
                                    tooltip: {
                                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                        padding: isMobile ? 8 : 12,
                                        titleFont: {
                                            size: 14,
                                            weight: 'bold'
                                        },
                                        bodyFont: {
                                            size: 13
                                        },
                                        callbacks: {
                                            label: function(context) {
                                                return context.dataset.label + ': ' + context.parsed.r + '%';
                                            }
                                        }
                                    }
                                }
                            }
                        });

                        nguHanhDongSection.show();
                    }

                    function displayChiSoBieuDoCot(chiSoBieuDoCot, gender) {
                        const section = $('#chiSoBieuDoCotSection');
                        const chartCanvas = document.getElementById('chiSoBieuDoCotChart');

                        if (!chiSoBieuDoCot || typeof chiSoBieuDoCot !== 'object' || Object.keys(chiSoBieuDoCot).length ===
                            0) {
                            section.hide();
                            return;
                        }

                        const natal = chiSoBieuDoCot.natal || chiSoBieuDoCot;
                        const annual = chiSoBieuDoCot.annual;
                        const hasTwoDatasets = annual && typeof annual === 'object';

                        if (window.chiSoBieuDoCotChart instanceof Chart) {
                            window.chiSoBieuDoCotChart.destroy();
                            window.chiSoBieuDoCotChart = null;
                        }

                        const chenhLech = chiSoBieuDoCot.chenh_lech_phan_tram;
                        const chenhLechByKey = {};
                        if (chenhLech && Array.isArray(chenhLech)) {
                            chenhLech.forEach(function(item) {
                                chenhLechByKey[item.key] = item;
                            });
                        }

                        const labels = [];
                        const dataNatal = [];
                        const dataAnnual = [];

                        const labelKeys = [];

                        function pushRow(label, natalVal, annualVal, key) {
                            labels.push(label);
                            labelKeys.push(key);
                            dataNatal.push(parseFloat(natalVal) || 0);
                            dataAnnual.push(hasTwoDatasets ? (parseFloat(annualVal) || 0) : 0);
                        }

                        if (natal.su_nghiep !== undefined) {
                            pushRow('Sự nghiệp', natal.su_nghiep, annual && annual.su_nghiep, 'su_nghiep');
                        }
                        if (natal.tai_chinh !== undefined) {
                            pushRow('Tài chính', natal.tai_chinh, annual && annual.tai_chinh, 'tai_chinh');
                        }
                        if (natal.phat_trien_ban_than !== undefined) {
                            pushRow('Phát triển bản thân', natal.phat_trien_ban_than, annual && annual.phat_trien_ban_than,
                                'phat_trien_ban_than');
                        }
                        if (natal.ket_noi_xa_hoi !== undefined) {
                            pushRow('Kết nối xã hội', natal.ket_noi_xa_hoi, annual && annual.ket_noi_xa_hoi,
                                'ket_noi_xa_hoi');
                        }
                        if (natal.suc_khoe !== undefined) {
                            pushRow('Sức khỏe', natal.suc_khoe, annual && annual.suc_khoe, 'suc_khoe');
                        }
                        if (gender === 'male' && natal.tinh_cam_nam !== undefined) {
                            pushRow('Tình cảm', natal.tinh_cam_nam, annual && annual.tinh_cam_nam, 'tinh_cam_nam');
                        } else if (gender !== 'male' && natal.tinh_cam_nu !== undefined) {
                            pushRow('Tình cảm', natal.tinh_cam_nu, annual && annual.tinh_cam_nu, 'tinh_cam_nu');
                        }

                        if (labels.length === 0) {
                            section.hide();
                            return;
                        }

                        const datasets = [{
                            label: 'Bản mệnh',
                            data: dataNatal,
                            backgroundColor: 'rgba(79, 70, 229, 0.6)',
                            borderColor: 'rgba(79, 70, 229, 1)',
                            borderWidth: 2
                        }];
                        if (hasTwoDatasets) {
                            datasets.push({
                                label: 'Niên mệnh',
                                data: dataAnnual,
                                backgroundColor: 'rgba(139, 69, 19, 0.6)',
                                borderColor: 'rgba(139, 69, 19, 1)',
                                borderWidth: 2
                            });
                        }

                        const ctx = chartCanvas.getContext('2d');
                        window.chiSoBieuDoCotChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: datasets
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                aspectRatio: 1.5,
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'top'
                                    }
                                },
                                scales: {
                                    x: {
                                        ticks: {
                                            maxRotation: 0,
                                            autoSkip: false,
                                            font: {
                                                size: 11
                                            }
                                        }
                                    },
                                    y: {
                                        beginAtZero: true,
                                        max: 100,
                                        ticks: {
                                            callback: function(value) {
                                                return value + '%';
                                            }
                                        }
                                    }
                                }
                            }
                        });

                        const chenhLechContainer = $('#chiSoBieuDoCotChenhLech');
                        chenhLechContainer.empty();
                        if (hasTwoDatasets && chenhLechByKey && Object.keys(chenhLechByKey).length > 0) {
                            let html =
                                '<p class="text-sm text-gray-600 mt-4 mb-2">Chênh lệch Niên mệnh so với Bản mệnh:</p><div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">';
                            labelKeys.forEach(function(key, idx) {
                                const item = chenhLechByKey[key];
                                if (!item) return;
                                const label = labels[idx] || key;
                                let valueText = item.trang_thai === 'không đổi' ? '0%' : (item.value + '%');
                                let diffHtml = valueText;
                                if (item.trang_thai === 'tăng') {
                                    diffHtml = '<span class="text-green-600 font-medium">' + valueText +
                                        ' \u2191</span>';
                                } else if (item.trang_thai === 'giảm') {
                                    diffHtml = '<span class="text-red-600 font-medium">\u2193 ' + valueText +
                                        '</span>';
                                }
                                html +=
                                    '<div class="bg-gray-50 rounded px-3 py-2 text-center"><div class="text-xs text-gray-600 mb-0.5">' +
                                    label + '</div><div class="text-sm font-semibold">' + diffHtml + '</div></div>';
                            });
                            html += '</div>';
                            chenhLechContainer.html(html).show();
                        } else {
                            chenhLechContainer.hide();
                        }

                        section.show();
                    }

                    function displaySims(sims) {
                        const simsContent = $('#simsContent');
                        const simsSection = $('#simsSection');
                        const simsEmpty = $('#simsEmpty');
                        const simsLoading = $('#simsLoading');

                        simsContent.empty();
                        simsLoading.hide();

                        if (!sims || sims.length === 0) {
                            simsContent.hide();
                            simsEmpty.show();
                            updateSimsCount(0);
                            return;
                        }

                        simsEmpty.hide();
                        simsContent.show();

                        let htmlContent = '';
                        sims.forEach(function(sim) {
                            // Format giá tiền
                            const price = new Intl.NumberFormat('vi-VN').format(sim.price || 0);

                            // Map màu sắc cho telco
                            const telcoColors = {
                                'mobifone': 'bg-red-100 text-red-800 border-red-300',
                                'viettel': 'bg-blue-100 text-blue-800 border-blue-300',
                                'vinaphone': 'bg-green-100 text-green-800 border-green-300',
                                'vietnamobile': 'bg-purple-100 text-purple-800 border-purple-300',
                                'gmobile': 'bg-yellow-100 text-yellow-800 border-yellow-300'
                            };
                            const telcoClass = telcoColors[sim.telco?.toLowerCase()] ||
                                'bg-gray-100 text-gray-800 border-gray-300';

                            // Map icon cho mệnh
                            const menhIcons = {
                                'Kim': 'fa-gem',
                                'Mộc': 'fa-tree',
                                'Thủy': 'fa-water',
                                'Hỏa': 'fa-fire',
                                'Thổ': 'fa-mountain'
                            };
                            const menhIcon = menhIcons[sim.menh] || 'fa-star';

                            htmlContent += `
                                <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 border border-gray-200 overflow-hidden transform hover:-translate-y-1">
                                    <div class="p-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center space-x-2">
                                                <i class="fas ${menhIcon} text-gray-400"></i>
                                                <span class="px-2 py-1 ${telcoClass} text-xs font-semibold rounded border">
                                                    ${sim.telco?.toUpperCase() || 'N/A'}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <p class="text-2xl font-bold text-gray-800 mb-1">${sim.number || ''}</p>
                                            <p class="text-sm text-gray-600">${sim.cat || ''}</p>
                                        </div>
                                        <div class="flex items-center justify-between pt-3 border-t border-gray-200">
                                            <span class="text-lg font-bold text-red-600">${price} đ</span>
                                            <button class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center gap-2">
                                                <i class="fas fa-phone"></i>
                                                Liên hệ
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });

                        simsContent.html(htmlContent);
                        simsSection.show();
                        updateSimsCount(sims.length);
                    }

                    function updateSimsCount(count) {
                        const total = allSims.length;
                        if (count === total) {
                            $('#simsCount').html(`<i class="fas fa-info-circle mr-1"></i>Hiển thị ${count} sim`);
                        } else {
                            $('#simsCount').html(`<i class="fas fa-info-circle mr-1"></i>Hiển thị ${count} / ${total} sim`);
                        }
                    }

                    function filterSims() {
                        const searchTerm = $('#simSearchInput').val().trim().toLowerCase();
                        const telcoFilter = $('#telcoFilter').val().toLowerCase();

                        let filtered = allSims;

                        // Filter theo nhà mạng
                        if (telcoFilter) {
                            filtered = filtered.filter(function(sim) {
                                return sim.telco?.toLowerCase() === telcoFilter;
                            });
                        }

                        // Filter theo số điện thoại
                        if (searchTerm) {
                            filtered = filtered.filter(function(sim) {
                                const number = (sim.number || '').toString().toLowerCase();
                                return number.includes(searchTerm);
                            });
                        }

                        // Hiển thị nút reset nếu có filter
                        if (telcoFilter || searchTerm) {
                            $('#resetFilters').removeClass('hidden');
                        } else {
                            $('#resetFilters').addClass('hidden');
                        }

                        displaySims(filtered);
                    }

                    function setupSimFilters() {
                        // Debounce cho search input
                        let searchTimeout;
                        $('#simSearchInput').on('input', function() {
                            clearTimeout(searchTimeout);
                            searchTimeout = setTimeout(function() {
                                filterSims();
                            }, 300);
                        });

                        // Filter khi thay đổi nhà mạng
                        $('#telcoFilter').on('change', function() {
                            filterSims();
                        });

                        // Reset filters
                        $('#resetFilters').on('click', function() {
                            $('#simSearchInput').val('');
                            $('#telcoFilter').val('');
                            filterSims();
                        });
                    }

                    // Event handler cho nút xem chi tiết quẻ gốc
                    $('#btnXemChiTietQue').on('click', function() {
                        const queGocData = $(this).data('que-goc');

                        if (!queGocData) {
                            showNotification('Không có dữ liệu quẻ gốc', 'error');
                            return;
                        }

                        // Cập nhật title modal
                        $('#modalQueGocTitle').text('Chi tiết: ' + (queGocData.name || 'Quẻ gốc'));

                        // Tạo HTML content cho modal
                        let modalContent = '';

                        // Hiển thị thông tin cơ bản
                        if (queGocData.name) {
                            modalContent += '<div class="mb-6">';
                            modalContent += '<h4 class="text-2xl font-bold mb-3 text-indigo-600 text-center">' +
                                queGocData.name + '</h4>';
                            modalContent += '</div>';
                        }

                        // Hiển thị tổng quan
                        if (queGocData.tong_quan) {
                            modalContent += '<div class="mb-6">';
                            modalContent +=
                                '<h4 class="text-xl font-bold mb-3 text-gray-800"><i class="fas fa-book-open mr-2 text-indigo-600"></i>Tổng quan</h4>';
                            modalContent +=
                                '<div class="text-gray-700 leading-relaxed whitespace-pre-line bg-gray-50 p-4 rounded-lg">' +
                                queGocData.tong_quan + '</div>';
                            modalContent += '</div>';
                        }

                        // Hiển thị các sections khác nếu có
                        const sections = [{
                                key: 'su_nghiep',
                                title: 'Sự nghiệp',
                                icon: 'fa-briefcase'
                            },
                            {
                                key: 'tai_chinh',
                                title: 'Tài chính',
                                icon: 'fa-dollar-sign'
                            },
                            {
                                key: 'tinh_duyen',
                                title: 'Tình duyên',
                                icon: 'fa-heart'
                            },
                            {
                                key: 'suc_khoe',
                                title: 'Sức khỏe',
                                icon: 'fa-heartbeat'
                            },
                            {
                                key: 'phat_trien_ban_than',
                                title: 'Phát triển bản thân',
                                icon: 'fa-user-graduate'
                            },
                            {
                                key: 'ket_noi_xa_hoi',
                                title: 'Kết nối xã hội',
                                icon: 'fa-users'
                            }
                        ];

                        sections.forEach(function(section) {
                            if (queGocData[section.key]) {
                                modalContent += '<div class="mb-6">';
                                modalContent +=
                                    '<h4 class="text-lg font-bold mb-3 text-gray-800"><i class="fas ' +
                                    section.icon + ' mr-2 text-indigo-600"></i>' + section.title + '</h4>';

                                if (queGocData[section.key].tich_cuc) {
                                    modalContent += '<div class="mb-3">';
                                    modalContent +=
                                        '<h5 class="font-semibold text-green-700 mb-2"><i class="fas fa-plus-circle mr-1"></i>Tích cực:</h5>';
                                    modalContent +=
                                        '<div class="text-gray-700 leading-relaxed whitespace-pre-line bg-green-50 p-3 rounded">' +
                                        queGocData[section.key].tich_cuc + '</div>';
                                    modalContent += '</div>';
                                }

                                if (queGocData[section.key].tieu_cuc) {
                                    modalContent += '<div class="mb-3">';
                                    modalContent +=
                                        '<h5 class="font-semibold text-red-700 mb-2"><i class="fas fa-minus-circle mr-1"></i>Tiêu cực:</h5>';
                                    modalContent +=
                                        '<div class="text-gray-700 leading-relaxed whitespace-pre-line bg-red-50 p-3 rounded">' +
                                        queGocData[section.key].tieu_cuc + '</div>';
                                    modalContent += '</div>';
                                }

                                modalContent += '</div>';
                            }
                        });

                        if (!modalContent) {
                            modalContent =
                                '<p class="text-gray-500 text-center py-8">Không có thông tin chi tiết</p>';
                        }

                        $('#modalQueGocContent').html(modalContent);

                        // Hiển thị modal
                        $('#modalQueGoc').removeClass('hidden').addClass('flex');
                        $('body').addClass('overflow-hidden');
                    });

                    // Đóng modal
                    $('#btnCloseModal').on('click', function() {
                        $('#modalQueGoc').addClass('hidden').removeClass('flex');
                        $('body').removeClass('overflow-hidden');
                    });

                    // Click ra ngoài modal để đóng
                    $('#modalQueGoc').on('click', function(e) {
                        if (e.target === this) {
                            $('#modalQueGoc').addClass('hidden').removeClass('flex');
                            $('body').removeClass('overflow-hidden');
                        }
                    });

                    function removeVietnameseTones(str) {
                        return str
                            .normalize("NFD")
                            .replace(/[\u0300-\u036f]/g, "")
                            .replace(/đ/g, "d")
                            .replace(/Đ/g, "D");
                    }

                    // Hàm hiển thị thông báo
                    function showNotification(message, type = 'info') {
                        const bgColor = type === 'error' ? 'bg-red-500' :
                            type === 'success' ? 'bg-green-500' : 'bg-blue-500';

                        const $notification = $(
                            `<div class="fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300 z-50">
                        <div class="flex items-center">
                            <i class="fas ${type === 'error' ? 'fa-exclamation-circle' : type === 'success' ? 'fa-check-circle' : 'fa-info-circle'} mr-2"></i>
                            <span>${message}</span>
                        </div>
                    </div>`
                        );

                        $('body').append($notification);

                        // Hiệu ứng xuất hiện
                        setTimeout(() => {
                            $notification.removeClass('translate-x-full');
                        }, 100);

                        // Tự động ẩn sau 3 giây
                        setTimeout(() => {
                            $notification.addClass('translate-x-full');
                            setTimeout(() => {
                                $notification.remove();
                            }, 300);
                        }, 3000);
                    }

                    // Thêm hiệu ứng cho nút submit khi hover
                    $('#submitBtn').hover(
                        function() {
                            $(this).addClass('transform scale-105');
                        },
                        function() {
                            $(this).removeClass('transform scale-105');
                        }
                    );

                    // Auto-focus vào trường đầu tiên
                    $('input[name="name"]').focus();
                });
            </script>
        </div>
    </div>
</body>

</html>
