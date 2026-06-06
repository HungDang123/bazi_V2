# SIM Import System

Hệ thống import dữ liệu SIM từ file CSV vào database.

## Cấu trúc dữ liệu

### Bảng `sims`
- `id`: ID tự tăng
- `phone_number`: Số điện thoại (unique)
- `cost_price`: Giá cost
- `selling_price`: Giá bán (nullable)
- `network_operator`: Nhà mạng (viettel, vinaphone, mobifone)
- `upper_trigram`: Quẻ thượng (nullable)
- `lower_trigram`: Quẻ hạ (nullable)
- `upper_trigram_name`: Tên quẻ thượng (nullable)
- `lower_trigram_name`: Tên quẻ hạ (nullable)
- `moving_line`: Động hào (nullable)
- `que_id`: ID quẻ dịch (nullable)
- `status`: Trạng thái (pending, confirmed, sold)
- `created_at`, `updated_at`, `deleted_at`

## Commands

### 1. Import dữ liệu từ CSV
```bash
php artisan sim:import --file=filename.csv
```

Options:
- `--file`: Tên file CSV trong thư mục `storage/app/` (default: sims_data.csv)
- `--stats`: Hiển thị thống kê sau khi import
- `--dry-run`: Preview import không lưu dữ liệu

Ví dụ:
```bash
# Import từ file mặc định
php artisan sim:import

# Import từ file khác với thống kê
php artisan sim:import --file=sims_new.csv --stats

# Preview import trước khi thực hiện
php artisan sim:import --dry-run
```

### 2. Xem thống kê
```bash
php artisan sim:stats
```

Options:
- `--operator`: Lọc theo nhà mạng (vinaphone, mobifone, viettel)
- `--status`: Lọc theo trạng thái (pending, confirmed, sold)

Ví dụ:
```bash
# Thống kê tổng quan
php artisan sim:stats

# Thống kê theo nhà mạng
php artisan sim:stats --operator=vinaphone

# Thống kê theo trạng thái
php artisan sim:stats --status=confirmed

# Lọc theo cả hai
php artisan sim:stats --operator=mobifone --status=pending
```

## Cấu trúc file CSV

File CSV phải có cấu trúc như sau (bắt đầu từ dòng 13):
```csv
STT,Số điện thoại,Giá cost,Giá bán,Nhà mạng,Quẻ thượng,Quẻ hạ,Tên quẻ thượng,Tên quẻ hạ,Động hào,ID quẻ,Ngũ hành,Trạng thái
1,0987654321,150000,,vinaphone,1,2,Càn,Đoài,3,Thiên Trạch Lý,Kim,Chốt
```

- Cột 1: STT (số thứ tự)
- Cột 2: Số điện thoại
- Cột 3: Giá cost
- Cột 4: Giá bán (có thể để trống)
- Cột 5: Nhà mạng
- Cột 6: Quẻ thượng
- Cột 7: Quẻ hạ
- Cột 8: Tên quẻ thượng
- Cột 9: Tên quẻ hạ
- Cột 10: Động hào
- Cột 11: ID quẻ
- Cột 12: Ngũ hành
- Cột 13: Trạng thái ("Chốt" = confirmed, khác = pending)

## Model Sim

### Relationships
- `que64()`: Belongs to Que64 model thông qua `que_id`

### Scopes
- `byNetworkOperator($operator)`: Lọc theo nhà mạng
- `byStatus($status)`: Lọc theo trạng thái

### Casts
- `cost_price`: decimal:2
- `selling_price`: decimal:2
- `upper_trigram`: integer
- `lower_trigram`: integer
- `moving_line`: integer

## Sử dụng với Docker

```bash
# Import dữ liệu
docker exec -it dev-vkb-web php artisan sim:import --file=sims_data.csv --stats

# Xem thống kê
docker exec -it dev-vkb-web php artisan sim:stats

# Xem thống kê Vinaphone
docker exec -it dev-vkb-web php artisan sim:stats --operator=vinaphone
```

## Migration

File migration: `database/migrations/2025_12_28_102731_create_sims_table.php`

Chạy migration:
```bash
php artisan migrate
```

## Lưu ý

1. Số điện thoại phải unique, nếu trùng sẽ skip
2. File CSV phải được đặt trong thư mục `storage/app/`
3. Hệ thống sẽ tự động detect và skip các dòng header
4. Nếu có lỗi encoding với tên file có dấu, hãy copy file với tên ASCII đơn giản
5. Command hỗ trợ soft deletes