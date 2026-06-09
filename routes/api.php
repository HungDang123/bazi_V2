<?php

use App\Http\Controllers\Bazicontroller;
use App\Http\Controllers\Que64Controller;
use App\Http\Controllers\TongQuanKhiaCanhController;
use Illuminate\Support\Facades\Route;

// PHẦN 5–8 (canonical)
Route::get('/phan-5/tong-quan', [TongQuanKhiaCanhController::class, 'index']);
Route::get('/phan-5/su-nghiep', [TongQuanKhiaCanhController::class, 'suNghiep']);
Route::get('/phan-6/nang-luong-trong-la-so', [TongQuanKhiaCanhController::class, 'nangLuongTrongLaSo']);
Route::get('/phan-7/bai-hoc-cuoc-song', [TongQuanKhiaCanhController::class, 'baiHocCuocSong']);
Route::get('/phan-8/dai-van', [TongQuanKhiaCanhController::class, 'daiVan']);
Route::get('/phan-8/nien-van', [TongQuanKhiaCanhController::class, 'nienVan']);
Route::get('/phan-8/du-bao-khia-canh', [TongQuanKhiaCanhController::class, 'duBaoKhiaCanh']);
Route::get('/phan-8/nhung-nam-can-chu-y', [TongQuanKhiaCanhController::class, 'nhungNamCanChuY']);
Route::get('/phan-9/giai-phap', [TongQuanKhiaCanhController::class, 'phan9GiaiPhap']);

Route::get('/bazi/calc', [Bazicontroller::class, 'calc']);
Route::get('/phan-2/chi-so-khia-canh-than-sat', [Bazicontroller::class, 'phan2ChiSoKhiaCanhThanSat']);
Route::get('/phan-3/tong-quan-ngu-hanh', [Bazicontroller::class, 'phan3TongQuanNguHanh']);
Route::get('/phan-3/chat-luong-nhat-chu', [Bazicontroller::class, 'phan4ChatLuongNhatChu']);
Route::get('/phan-4/chat-luong-nhat-chu', [Bazicontroller::class, 'phan4ChatLuongNhatChu']);
Route::get('/bazi/calc-phan-tram-ngu-hanh', [Bazicontroller::class, 'calcPhanTramNguHanh']);
Route::get('/sim/diem-vkb', [Bazicontroller::class, 'diemSimVKB']);
Route::get('/que64/{id}', [Que64Controller::class, 'show']);
