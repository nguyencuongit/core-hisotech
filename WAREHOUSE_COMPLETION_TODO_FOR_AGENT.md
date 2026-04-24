# Roadmap triển khai hoàn thiện kho theo thứ tự commit

Tài liệu này được chuyển thành roadmap triển khai theo từng commit để agent làm tuần tự, dễ review, dễ rollback và tránh làm lan man.

---

## Commit 01 — Chuẩn hóa rule nền cho pallet/location

### Mục tiêu
- Chốt danh sách `location type` nào được phép chứa pallet
- Chặn tạo/move pallet sang location không hợp lệ
- Làm UI select pallet location chỉ hiện location hợp lệ

### Phạm vi code
- `src/Domains/Warehouse/Services/PalletService.php`
- `src/Domains/Warehouse/Http/Controllers/PalletController.php`
- `src/Domains/Warehouse/Support/*` nếu cần helper/constant chung
- `resources/views/warehouse/show.blade.php`

### Kết quả kỳ vọng
- Pallet chỉ được đặt ở location hợp lệ
- Move pallet có validate cùng kho + đúng location type
- Không còn chọn nhầm node cha như `zone`, `rack`, `floor` nếu policy không cho phép

---

## Commit 02 — Update cây location khi đổi parent

### Mục tiêu
- Khi đổi `parent_id`, tự rebuild `level` và `path`
- Cập nhật toàn bộ node con cháu để không lệch tree

### Phạm vi code
- `src/Domains/Warehouse/Models/WarehouseLocation.php`
- `src/Domains/Warehouse/Services/WarehouseLocationService.php`
- `src/Domains/Warehouse/Http/Controllers/WarehouseLocationController.php`
- migration bổ sung nếu cần index/field hỗ trợ sort

### Kết quả kỳ vọng
- Cây vị trí luôn đúng
- Breadcrumb/path không bị sai sau khi đổi parent
- Map, tree, pallet lookup không lệch dữ liệu

---

## Commit 03 — Xóa an toàn cho location và pallet

### Mục tiêu
- Không xóa cứng dữ liệu đã phát sinh lịch sử
- Nếu đã có liên kết/dữ liệu vận hành thì chỉ inactive/locked/closed

### Phạm vi code
- `src/Domains/Warehouse/Services/WarehouseLocationService.php`
- `src/Domains/Warehouse/Services/PalletService.php`
- `src/Domains/Warehouse/Http/Controllers/WarehouseLocationController.php`
- `src/Domains/Warehouse/Http/Controllers/PalletController.php`

### Kết quả kỳ vọng
- Location có child / map item / pallet / stock history không bị xóa cứng
- Pallet có movement không bị xóa cứng
- Audit trail được giữ nguyên

---

## Commit 04 — Policy preset và rule tương thích với warehouse setting

### Mục tiêu
- Thêm preset policy cho sản phẩm trong kho
- Validate policy không mâu thuẫn với settings của kho

### Phạm vi code
- `src/Domains/Warehouse/Http/Controllers/WarehouseProductPolicyController.php`
- `src/Domains/Warehouse/Http/Requests/WarehouseProductPolicyRequest.php`
- `src/Domains/Warehouse/Services/WarehouseProductPolicyService.php`
- `src/Domains/Warehouse/Support/*` cho preset registry nếu cần
- `resources/views/warehouse/show.blade.php`

### Kết quả kỳ vọng
- User chọn preset dễ hiểu hơn thay vì chỉnh field kỹ thuật
- Kho tắt pallet thì policy không thể bắt buộc pallet
- Kho tắt QC thì policy không thể bắt buộc QC

---

## Commit 05 — Tách warehouse.show thành các tab rõ ràng

### Mục tiêu
- Chia workspace hiện tại thành tab dễ dùng
- Giảm độ dày của view và dễ bảo trì hơn

### Phạm vi code
- `resources/views/warehouse/show.blade.php`
- `resources/views/warehouse/partials/*` để tách nhỏ block UI

### Kết quả kỳ vọng
- Có các tab như:
  - Tổng quan
  - Cài đặt kho
  - Cây vị trí
  - Sơ đồ kho
  - Sản phẩm trong kho
  - Chính sách sản phẩm
  - Pallet
  - Lịch sử pallet
- UI gọn hơn, ít lỗi parse hơn

---

## Commit 06 — Rà soát và khóa lại validation / lint

### Mục tiêu
- Chạy lint các file đã chạm
- Sửa lỗi parse/runtime còn sót
- Đồng bộ route, view, service

### Phạm vi code
- toàn bộ file inventory đã chỉnh ở các commit trên

### Kết quả kỳ vọng
- `php -l` pass trên file PHP liên quan
- `ReadLints` sạch
- warehouse.show load được bình thường

---

## Thứ tự thực hiện thực tế

1. Commit 01 — pallet/location rules
2. Commit 02 — tree rebuild
3. Commit 03 — safe delete flows
4. Commit 04 — policy presets + compatibility
5. Commit 05 — split UI tabs
6. Commit 06 — lint and final validation

---

## Ghi chú cho agent

- Không làm ngược thứ tự vì tree/pallet/settings phụ thuộc nhau.
- Nếu commit nào thay đổi rule nền thì phải kiểm tra lại UI `warehouse.show`.
- Nếu thêm helper/constant dùng chung, đặt trong `src/Domains/Warehouse/Support`.
- Nếu có thay đổi route/controller thì phải đối chiếu permission và route name ngay trong cùng commit.

---

## Checklist hoàn thành từng commit

### Sau Commit 01
- [ ] Pallet only allowed on valid location types
- [ ] Move pallet validates warehouse + location type
- [ ] UI location chooser filtered

### Sau Commit 02
- [ ] Parent change rebuilds current node and descendants
- [ ] Path/level correct after move
- [ ] No cycle allowed

### Sau Commit 03
- [ ] Safe delete/deactivate implemented
- [ ] No hard delete when data exists
- [ ] Audit trail preserved

### Sau Commit 04
- [ ] Policy presets available
- [ ] Setting-policy compatibility enforced
- [ ] UI easier to configure

### Sau Commit 05
- [ ] UI split into tabs/partials
- [ ] Less clutter on warehouse.show
- [ ] No Blade parse issues

### After Commit 06
- [ ] Lint passes
- [ ] Warehouse page renders
- [ ] No runtime errors in scope
