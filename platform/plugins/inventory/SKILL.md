# SKILL.md — Inventory Plugin Development Guide
**Target agents:** Codex, Claude Code  
**Project:** `platform/plugins/inventory`  
**Tech context:** Laravel / Botble-style plugin structure, domain-oriented organization, repository/service pattern, warehouse-scoped permissions.

---

## 1) Mục tiêu của skill

Skill này hướng dẫn agent triển khai plugin **Inventory** theo đúng:
- nghiệp vụ kho vận đã mô tả
- kiến trúc domain module rõ ràng
- chuẩn phân quyền theo **kho**
- chuẩn luồng chứng từ nhập / xuất / chuyển kho / trả hàng / kiểm kê
- chuẩn lịch sử tồn kho bằng `inv_stock_movements`
- khả năng mở rộng báo cáo và audit lịch sử

Agent phải ưu tiên:
1. Không phá vỡ luồng tồn kho.
2. Mọi thay đổi số lượng tồn đều phải có **movement log**.
3. Mọi truy vấn nghiệp vụ phải tôn trọng **WarehouseContext**.
4. Trạng thái chứng từ phải rõ ràng, có thể kiểm soát được bằng enum / state machine đơn giản.
5. Code dễ bảo trì, chia domain hợp lý.

---

## 2) Phạm vi chức năng plugin

Plugin inventory bao gồm các nhóm chức năng sau:

### 2.1. Danh mục và cấu hình
- Nhân viên kho
- Chức vụ trong kho
- Phân công nhân sự + phân quyền
- Sản phẩm
- Nhóm hàng hoá
- Đơn vị tính
- Nhà cung cấp
- Khách hàng
- Kho
- Vị trí kho
- Cài đặt chung:
  - phân quyền
  - đơn vị tính
  - nhóm hàng hoá
  - cấu hình tồn kho
  - cấu hình chứng từ

### 2.2. Nghiệp vụ chứng từ
- Phiếu mua hàng
- Nhập kho
- Xuất kho
- Danh sách đóng gói
- Phiếu chuyển kho nội bộ
- Điều chỉnh tồn kho / kiểm kê kho
- Quản lý nhập - xuất đơn trả
- Lịch sử kho

### 2.3. Báo cáo
- Xuất nhập tồn
- Phân tích tồn kho
- Định giá hàng tồn
- Thời gian bảo hành
- Biến động hàng tồn
- Số dư hàng tồn
- Báo cáo theo kho / sản phẩm / vị trí / khoảng thời gian

---

## 3) Cấu trúc thư mục chuẩn

```txt
platform/plugins/inventory/src/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── DataTables/
├── Models/
├── Tables/
├── Forms/
├── Enums/
├── Services/
├── Repositories/
├── Supports/
└── Domains/
    ├── Product/
    │   ├── Models/
    │   ├── Services/
    │   ├── Repositories/
    │   ├── Http/
    │   ├── Enums/
    │   ├── UseCases/
    │   └── DTO/
    ├── Warehouse/
    ├── Import/
    ├── Export/
    ├── Transfer/
    ├── Stock/
    ├── Packing/
    ├── Adjustment/
    ├── Report/
    └── Setting/
```

### 3.1. Quy ước tổ chức
- `Domains/Product`: quản lý sản phẩm, category, đơn vị tính.
- `Domains/Warehouse`: kho, vị trí kho, nhân sự kho, phân công kho.
- `Domains/Import`: phiếu mua hàng, nhập kho, supplier inbound flow.
- `Domains/Export`: xuất kho, xuất bán, outbound flow.
- `Domains/Transfer`: chuyển kho nội bộ.
- `Domains/Stock`: tồn hiện tại, movement log, stock services.
- `Domains/Packing`: danh sách đóng gói.
- `Domains/Adjustment`: kiểm kê và điều chỉnh tồn.
- `Domains/Report`: báo cáo.
- `Domains/Setting`: cấu hình plugin.

### 3.2. Khi nào để ở root `src/`
Chỉ để các thành phần dùng chung toàn plugin:
- base service
- base repository
- shared enums
- middleware
- warehouse context
- helper / support classes

---

## 4) Danh sách bảng dữ liệu

## 4.1. Nhân sự kho
- `inv_warehouse_staff`
- `inv_warehouse_positions`
- `inv_warehouse_staff_assignments`

## 4.2. Đối tác
- `inv_suppliers`
- `inv_customers`

## 4.3. Sản phẩm và cấu hình
- `inv_product`
- `inv_product_categories`
- `inv_units`

## 4.4. Kho
- `inv_warehouses`
- `inv_warehouse_locations`

## 4.5. Chứng từ nhập xuất
- `inv_import`
- `inv_import_items`
- `inv_export`
- `inv_export_items`

## 4.6. Đóng gói
- `inv_packing_lists`
- `inv_packing_list_items`

## 4.7. Chuyển kho nội bộ
- `inv_internal_transfers`
- `inv_internal_transfers_item`

## 4.8. Trả hàng
- `inv_returns`
- `inv_return_items`

## 4.9. Điều chỉnh và lịch sử
- `inv_stock_adjustments`
- `inv_stock_adjustment_items`
- `inv_stocks`
- `inv_stock_movements`

## 4.10. Cài đặt
- `inv_setting`

> Lưu ý: tên bảng có thể giữ nguyên theo yêu cầu hiện tại để đồng bộ code nghiệp vụ.

---

## 5) Tư duy dữ liệu cốt lõi

Có 2 nguồn dữ liệu quan trọng nhất:

### 5.1. `inv_stocks`
Lưu tồn hiện tại, thường là số lượng cuối cùng.

Gợi ý cột:
- `id`
- `warehouse_id`
- `warehouse_location_id` nullable
- `product_id`
- `batch_no` nullable
- `serial_no` nullable
- `quantity`
- `reserved_quantity` default 0
- `available_quantity`
- `unit_id`
- `created_at`
- `updated_at`

### 5.2. `inv_stock_movements`
Lưu lịch sử biến động tồn và là nguồn giải thích vì sao tồn kho thay đổi.

Ví dụ:
- `before_quantity = 20`
- `change_quantity = +50`
- `after_quantity = 70`

hoặc
- `before_quantity = 70`
- `change_quantity = -10`
- `after_quantity = 60`

Gợi ý cột:
- `id`
- `warehouse_id`
- `warehouse_location_id` nullable
- `product_id`
- `stock_id` nullable
- `reference_type`  
  ví dụ: `import`, `export`, `transfer_out`, `transfer_in`, `return_in`, `return_out`, `adjustment`
- `reference_id`
- `reference_code`
- `movement_type`
- `direction` (`in`, `out`, `adjust`)
- `before_quantity`
- `change_quantity`
- `after_quantity`
- `unit_id`
- `occurred_at`
- `note`
- `created_by`
- `created_at`

### 5.3. Nguyên tắc vàng
- `inv_stocks` là **current snapshot**
- `inv_stock_movements` là **audit trail**
- Tuyệt đối không thay đổi tồn kho mà không sinh movement log
- Báo cáo có thể đọc từ `inv_stock_movements`, còn màn hình tồn hiện tại đọc từ `inv_stocks`

---

## 6) Luồng nghiệp vụ chuẩn

## 6.1. Nhập kho
Luồng:
1. Phiếu mua hàng
2. Nhập kho
3. Chọn vị trí kho nếu có
4. Nếu sản phẩm chưa có vị trí phù hợp hoặc cần sửa thực tế -> điều chỉnh tồn kho
5. Ghi lịch sử kho

### Quy tắc
- Phiếu nhập chỉ làm tăng tồn khi chứng từ đạt trạng thái hợp lệ như `completed` hoặc `confirmed`.
- Nếu chứng từ mới ở `draft` hoặc `pending`, chưa cộng tồn.
- Nếu nhập kho theo phiếu mua hàng, cần liên kết `purchase_reference`.

---

## 6.2. Xuất kho
Luồng:
1. Xuất kho
2. Quản lý danh sách đóng gói
3. Ghi lịch sử kho

### Quy tắc
- Chỉ được xuất nếu tồn khả dụng đủ.
- Nếu có đóng gói, trạng thái đóng gói phải hỗ trợ truy vết trước khi hoàn tất xuất.
- Trừ tồn khi chứng từ được xác nhận xuất thực tế.

---

## 6.3. Chuyển kho nội bộ
Luồng:
1. Phiếu chuyển kho nội bộ
2. Xuất kho tại kho nguồn
3. Danh sách đóng gói
4. Ghi lịch sử chuyển ra
5. Nhập kho tại kho đích

### Quy tắc
- Một phiếu chuyển nội bộ có 2 chiều:
  - `transfer_out` ở kho nguồn
  - `transfer_in` ở kho đích
- Cần tách movement theo từng kho.
- Không được cộng tồn kho đích nếu hàng chưa được nhận.
- Nếu hàng đang trên đường, có thể dùng trạng thái trung gian `in_transit`.

---

## 6.4. Trả hàng
Luồng:
- Quản lý nhập - xuất đơn trả
  - nếu khách hàng trả hàng về kho -> nhập kho
  - nếu kho trả hàng cho đối tác -> xuất kho

### Quy tắc
- Return cần có `return_type`:
  - `customer_return_in`
  - `supplier_return_out`
- Movement direction tương ứng:
  - khách trả kho -> `in`
  - kho trả đối tác -> `out`

---

## 6.5. Điều chỉnh tồn kho / kiểm kê
Dùng khi:
- mất hàng
- vỡ hàng
- thừa hàng
- kiểm kê thực tế lệch so với hệ thống
- sản phẩm cần cập nhật lại vị trí

### Quy tắc
- Adjustment không xoá movement cũ.
- Adjustment sinh movement mới dạng `adjustment`.
- Nên lưu:
  - số lượng hệ thống
  - số lượng thực tế
  - chênh lệch
  - lý do
  - người kiểm kê
  - người duyệt

---

## 7) Trạng thái chứng từ

Agent nên dùng enum cho từng loại chứng từ, nhưng thống nhất một bộ trạng thái cơ bản để dễ dùng chung.

## 7.1. Bộ trạng thái gợi ý
- `draft`
- `pending`
- `confirmed`
- `processing`
- `completed`
- `cancelled`

## 7.2. Áp dụng
### Phiếu mua hàng
- `draft`
- `pending`
- `confirmed`
- `completed`
- `cancelled`

### Nhập kho
- `draft`
- `pending`
- `confirmed`
- `completed`
- `cancelled`

### Xuất kho
- `draft`
- `pending`
- `picking`
- `packing`
- `completed`
- `cancelled`

### Chuyển kho nội bộ
- `draft`
- `pending`
- `approved`
- `in_transit`
- `received`
- `cancelled`

### Trả hàng
- `draft`
- `pending`
- `confirmed`
- `completed`
- `cancelled`

### Điều chỉnh tồn
- `draft`
- `pending_approval`
- `approved`
- `completed`
- `cancelled`

## 7.3. Quy tắc trạng thái
- Chỉ `completed`, `received`, hoặc trạng thái nghiệp vụ tương đương mới tác động tồn thật nếu business yêu cầu.
- Nếu hệ thống cần “giữ hàng” từ sớm, dùng `reserved_quantity` thay vì trừ tồn thật.
- Không cho phép sửa item khi chứng từ đã `completed`, trừ khi có nghiệp vụ đảo chứng từ hoặc tạo phiếu điều chỉnh.

---

## 8) WarehouseContext và phân quyền theo kho

Đây là yêu cầu bắt buộc.

## 8.1. Mục tiêu
Mọi request đều phải biết:
- user đang thao tác trong kho nào
- user được phép truy cập những kho nào
- user có phải super admin không

## 8.2. Contract đề xuất

```php
interface WarehouseContext
{
    public function getWarehouseId(): ?int;

    public function getAccessibleWarehouseIds(): array;

    public function isSuperAdmin(): bool;
}
```

## 8.3. Middleware bắt buộc
Tạo middleware để resolve context theo request + user hiện tại.

Ví dụ nhiệm vụ middleware:
- đọc warehouse hiện tại từ request, route param, session hoặc header
- đọc danh sách kho user được quyền truy cập
- nếu là super admin thì cho full access
- bind dữ liệu vào service container cho toàn request lifecycle

Ví dụ dùng:
```php
$warehouseId = app(WarehouseContext::class)->getWarehouseId();
```

## 8.4. Rule phân quyền
- Super admin: xem / thao tác mọi kho
- Warehouse manager: chỉ kho được gán
- Warehouse staff: chỉ thao tác theo quyền assignment
- Auditor / viewer: chỉ xem, không sửa tồn

## 8.5. Dữ liệu phân quyền
`inv_warehouse_staff_assignments` nên lưu:
- `staff_id`
- `warehouse_id`
- `position_id`
- `permissions` JSON hoặc role key
- `is_primary`
- `status`

---

## 9) Chuẩn repository có scope kho

Đây là nguyên tắc bắt buộc để tránh quên lọc kho.

## 9.1. Không query thẳng model bừa bãi
Hạn chế:
```php
InvImport::query()->get();
```

Ưu tiên:
```php
$warehouseId = app(WarehouseContext::class)->getWarehouseId();
$this->importRepository->query($warehouseId)->get();
```

## 9.2. Base repository pattern gợi ý
Mỗi repository nên có:

```php
public function query(?int $warehouseId = null): Builder
{
    $query = $this->model->newQuery();

    if ($warehouseId) {
        $query->where('warehouse_id', $warehouseId);
    }

    return $query;
}
```

## 9.3. Repository nâng cao
Nếu entity không trực tiếp có `warehouse_id`, cần join hoặc scope qua quan hệ.

Ví dụ:
- export items -> scope theo export header
- packing list items -> scope theo packing list header

## 9.4. Global rule
- Danh sách màn hình: luôn scope theo `WarehouseContext`
- Thống kê báo cáo: scope theo kho được truy cập
- Chi tiết chứng từ: validate quyền xem trên warehouse trước khi trả dữ liệu

---

## 10) Domain responsibilities

## 10.1. Product domain
Chịu trách nhiệm:
- sản phẩm
- nhóm hàng
- đơn vị tính
- metadata sản phẩm
- cấu hình bảo hành nếu có

Không chịu trách nhiệm:
- cộng trừ tồn

## 10.2. Warehouse domain
Chịu trách nhiệm:
- kho
- vị trí kho
- nhân sự kho
- phân công
- phân quyền thao tác theo kho

## 10.3. Import domain
Chịu trách nhiệm:
- phiếu mua hàng
- nhập kho
- import items
- xác nhận nhập
- trigger stock in

## 10.4. Export domain
Chịu trách nhiệm:
- phiếu xuất
- export items
- validate stock availability
- trigger stock out

## 10.5. Transfer domain
Chịu trách nhiệm:
- phiếu chuyển kho
- logic kho nguồn / kho đích
- transit state
- stock out source, stock in destination

## 10.6. Packing domain
Chịu trách nhiệm:
- danh sách đóng gói
- trạng thái picking / packing
- liên kết với export hoặc transfer

## 10.7. Stock domain
Đây là domain cực kỳ quan trọng.

Chịu trách nhiệm:
- tồn hiện tại
- movement log
- tăng tồn
- giảm tồn
- điều chỉnh tồn
- truy vấn lịch sử tồn
- tính tồn khả dụng

Không cho domain khác tự cập nhật `inv_stocks` trực tiếp.

## 10.8. Adjustment domain
Chịu trách nhiệm:
- phiếu kiểm kê
- phiếu điều chỉnh
- chênh lệch số lượng
- lý do điều chỉnh
- approval flow

## 10.9. Report domain
Chỉ đọc dữ liệu:
- tồn hiện tại
- movements
- import/export/transfer/return
- không được ghi dữ liệu nghiệp vụ

## 10.10. Setting domain
- cấu hình tồn kho
- policy
- numbering format
- behavior khi âm kho
- cấu hình bắt buộc vị trí kho hay không

---

## 11) Service layer bắt buộc cho thay đổi tồn

Agent phải tập trung toàn bộ logic cộng trừ tồn vào một service trung tâm, ví dụ:

```php
StockService
```

hoặc tách nhỏ:
- `StockInboundService`
- `StockOutboundService`
- `StockAdjustmentService`
- `StockMovementService`

## 11.1. Chức năng tối thiểu của StockService
- `increase(...)`
- `decrease(...)`
- `adjust(...)`
- `transferOut(...)`
- `transferIn(...)`
- `getAvailableStock(...)`
- `assertSufficientStock(...)`

## 11.2. Quy tắc bắt buộc
Mỗi function phải:
1. lock dữ liệu phù hợp nếu cần transaction
2. lấy tồn hiện tại
3. tính before/change/after
4. update `inv_stocks`
5. insert `inv_stock_movements`
6. trả về kết quả rõ ràng

## 11.3. Ví dụ pseudo-code
```php
DB::transaction(function () use ($dto) {
    $stock = $this->stockRepository->findOrCreateStock(
        warehouseId: $dto->warehouseId,
        productId: $dto->productId,
        locationId: $dto->locationId,
    );

    $before = $stock->quantity;
    $change = $dto->quantity;
    $after = $before + $change;

    $stock->quantity = $after;
    $stock->available_quantity = max(0, $after - $stock->reserved_quantity);
    $stock->save();

    $this->movementRepository->create([
        'warehouse_id' => $dto->warehouseId,
        'warehouse_location_id' => $dto->locationId,
        'product_id' => $dto->productId,
        'reference_type' => $dto->referenceType,
        'reference_id' => $dto->referenceId,
        'movement_type' => 'import',
        'direction' => 'in',
        'before_quantity' => $before,
        'change_quantity' => $change,
        'after_quantity' => $after,
        'occurred_at' => now(),
        'created_by' => auth()->id(),
    ]);
});
```

---

## 12) Chuẩn transaction

Các nghiệp vụ sau bắt buộc chạy transaction:
- xác nhận nhập kho
- xác nhận xuất kho
- nhận hàng chuyển kho
- điều chỉnh tồn
- tạo nhiều movement cùng lúc

## 12.1. Khi cần lock
Khi cập nhật tồn:
- dùng row lock trên stock record
- hoặc cơ chế đảm bảo không race condition

Ví dụ:
```php
->lockForUpdate()
```

## 12.2. Không làm
- Không update stock ngoài transaction
- Không insert movement trước rồi mới update stock nếu không có rollback bảo vệ
- Không để domain import/export tự ghi stock table theo kiểu copy-paste logic

---

## 13) Thiết kế model gợi ý

## 13.1. Các model chính
- `Warehouse`
- `WarehouseLocation`
- `WarehouseStaff`
- `WarehousePosition`
- `WarehouseStaffAssignment`
- `Supplier`
- `Customer`
- `Product`
- `ProductCategory`
- `Unit`
- `Import`
- `ImportItem`
- `Export`
- `ExportItem`
- `PackingList`
- `PackingListItem`
- `InternalTransfer`
- `InternalTransferItem`
- `ReturnOrder`
- `ReturnItem`
- `Stock`
- `StockMovement`
- `StockAdjustment`
- `StockAdjustmentItem`
- `InventorySetting`

## 13.2. Quan hệ tiêu biểu
### Warehouse
- hasMany locations
- hasMany staffs through assignments
- hasMany imports
- hasMany exports
- hasMany stocks

### Product
- belongsTo category
- belongsTo unit
- hasMany importItems
- hasMany exportItems
- hasMany stocks
- hasMany stockMovements

### Import
- belongsTo warehouse
- belongsTo supplier
- hasMany items

### Export
- belongsTo warehouse
- belongsTo customer nullable
- hasMany items
- hasOne packingList nullable

### InternalTransfer
- belongsTo sourceWarehouse
- belongsTo destinationWarehouse
- hasMany items

### Stock
- belongsTo warehouse
- belongsTo product
- belongsTo location nullable

---

## 14) DTO và UseCase

Agent nên dùng DTO / UseCase cho nghiệp vụ lớn thay vì nhét toàn bộ vào controller.

## 14.1. DTO gợi ý
- `CreateImportDTO`
- `ConfirmImportDTO`
- `CreateExportDTO`
- `ConfirmExportDTO`
- `CreateTransferDTO`
- `ReceiveTransferDTO`
- `CreateAdjustmentDTO`

## 14.2. UseCase gợi ý
- `CreateImportUseCase`
- `ConfirmImportUseCase`
- `CreateExportUseCase`
- `ConfirmExportUseCase`
- `CreateTransferUseCase`
- `DispatchTransferUseCase`
- `ReceiveTransferUseCase`
- `CreateStockAdjustmentUseCase`

## 14.3. Rule
- Controller chỉ nhận request, validate, gọi use case / service
- Use case điều phối domain services
- Repository chỉ lo query / persist
- Không nhồi business phức tạp trong controller

---

## 15) Quy tắc cho import / export item

Nên lưu item snapshot để tránh dữ liệu đổi về sau làm sai lịch sử.

Mỗi item nên có:
- `product_id`
- `product_name`
- `sku` nullable
- `unit_id`
- `unit_name`
- `quantity`
- `price` nullable
- `total` nullable
- `warehouse_location_id` nullable
- `note`

Lý do:
- nếu sau này tên sản phẩm đổi, chứng từ cũ vẫn đúng
- thuận tiện audit

---

## 16) Gợi ý enum

Agent nên tạo enum riêng theo domain:
- `DocumentStatusEnum`
- `ImportStatusEnum`
- `ExportStatusEnum`
- `TransferStatusEnum`
- `ReturnTypeEnum`
- `MovementTypeEnum`
- `MovementDirectionEnum`
- `AdjustmentReasonEnum`
- `AssignmentRoleEnum`

Ví dụ:
```php
enum MovementDirectionEnum: string
{
    case IN = 'in';
    case OUT = 'out';
    case ADJUST = 'adjust';
}
```

---

## 17) Màn hình và module UI nên có

## 17.1. Product
- danh sách sản phẩm
- tạo / sửa / xoá mềm
- nhóm hàng
- đơn vị tính

## 17.2. Warehouse
- danh sách kho
- vị trí kho
- nhân viên kho
- phân công nhân sự

## 17.3. Import
- danh sách phiếu mua hàng
- danh sách nhập kho
- chi tiết nhập kho
- xác nhận nhập

## 17.4. Export
- danh sách xuất kho
- chi tiết xuất kho
- xác nhận xuất

## 17.5. Packing
- danh sách đóng gói
- chi tiết đóng gói
- trạng thái picking / packed / shipped nội bộ

## 17.6. Transfer
- danh sách chuyển kho
- điều phối chuyển
- xác nhận nhận hàng

## 17.7. Returns
- danh sách trả hàng
- nhập trả / xuất trả

## 17.8. Stock
- tồn hiện tại theo kho
- tồn theo vị trí
- thẻ kho / lịch sử kho
- movement timeline

## 17.9. Adjustment
- phiếu kiểm kê
- phiếu điều chỉnh
- approval

## 17.10. Reports
- xuất nhập tồn
- tồn theo kho
- tồn theo sản phẩm
- biến động tồn
- định giá tồn kho
- cảnh báo tồn thấp / âm kho / hàng lâu không luân chuyển

---

## 18) Thiết kế báo cáo

## 18.1. Báo cáo xuất nhập tồn
Nguồn dữ liệu:
- opening balance
- movements in kỳ
- movements out kỳ
- closing balance

Có thể tính từ `inv_stock_movements` + snapshot trước kỳ.

## 18.2. Phân tích tồn kho
- tồn thấp
- tồn âm
- tồn chậm luân chuyển
- top nhập nhiều
- top xuất nhiều

## 18.3. Định giá hàng tồn
Nếu có giá vốn:
- moving average
- latest cost
- hoặc cost snapshot theo item

Agent cần giữ khả năng mở rộng, chưa chắc phải triển khai full ngay.

## 18.4. Báo cáo bảo hành
Nếu sản phẩm có warranty:
- ngày nhập
- hạn bảo hành
- còn / hết bảo hành

---

## 19) Rule code cho agent

## 19.1. Ưu tiên
- Code rõ ràng
- Tên class đúng nghĩa vụ
- Dễ test
- Ít side-effect ẩn

## 19.2. Cấm
- Không update tồn trực tiếp từ controller
- Không query bỏ qua WarehouseContext trong màn hình danh sách nghiệp vụ
- Không trộn logic import/export/transfer vào một controller khổng lồ
- Không dùng duplicated stock update logic ở nhiều nơi
- Không hardcode trạng thái rải rác, phải gom enum / constants

## 19.3. Nên làm
- Dùng Form Request cho validate
- Dùng service/usecase cho nghiệp vụ
- Dùng repository cho truy vấn
- Dùng enum cho trạng thái
- Dùng transaction cho thao tác thay đổi tồn
- Dùng events nếu cần mở rộng audit / notification

---

## 20) Kiến trúc permission khuyến nghị

Có 2 lớp quyền:

### 20.1. Lớp hệ thống
Ví dụ:
- `inventory.access`
- `inventory.settings`
- `inventory.report.view`

### 20.2. Lớp nghiệp vụ theo kho
Ví dụ:
- `inventory.product.view`
- `inventory.import.create`
- `inventory.import.confirm`
- `inventory.export.create`
- `inventory.export.confirm`
- `inventory.transfer.create`
- `inventory.transfer.receive`
- `inventory.adjustment.approve`
- `inventory.stock.view`

Kết hợp với WarehouseContext để giới hạn theo kho.

---

## 21) Đề xuất middleware và support classes

## 21.1. Middleware
- `ResolveWarehouseContext`
- `EnsureWarehouseAccess`
- `EnsureInventoryPermission`

## 21.2. Support classes
- `WarehouseContext`
- `DocumentCodeGenerator`
- `StockNumberFormatter`
- `InventoryPermissionRegistrar`

---

## 22) Mã chứng từ

Nên có generator riêng.

Ví dụ:
- Phiếu nhập: `NK-20260420-0001`
- Phiếu xuất: `XK-20260420-0001`
- Chuyển kho: `CK-20260420-0001`
- Điều chỉnh: `DC-20260420-0001`
- Trả hàng: `TH-20260420-0001`

Không nên generate code thủ công trong controller.

---

## 23) Hướng triển khai theo phase

## Phase 1 — nền tảng
- migrations
- models
- enums
- warehouse context
- permission middleware
- repositories base
- stock service
- warehouse / product / supplier / customer CRUD

## Phase 2 — nhập xuất cơ bản
- import
- export
- stock movement
- stock current snapshot
- lịch sử kho cơ bản

## Phase 3 — nâng cao
- packing list
- transfer nội bộ
- returns
- stock adjustment

## Phase 4 — báo cáo và tối ưu
- báo cáo xuất nhập tồn
- cảnh báo tồn
- performance tuning
- caching read reports nếu cần

---

## 24) Ưu tiên test

## 24.1. Unit tests
- StockService increase
- StockService decrease
- StockService adjust
- insufficient stock
- movement log generation
- warehouse scope query

## 24.2. Feature tests
- user chỉ xem được kho được gán
- confirm import làm tăng tồn
- confirm export làm giảm tồn
- transfer tạo movement cho 2 kho
- adjustment ghi chênh lệch đúng
- return inbound / outbound đúng chiều

## 24.3. Edge cases
- âm kho
- double submit chứng từ
- confirm 2 lần
- huỷ chứng từ sau completed
- đổi location sau khi đã nhập
- cùng lúc nhiều người xuất một sản phẩm

---

## 25) Performance notes

- Index tối thiểu:
  - `warehouse_id`
  - `product_id`
  - `warehouse_location_id`
  - `reference_type + reference_id`
  - `occurred_at`
- Bảng `inv_stock_movements` sẽ lớn rất nhanh, cần chuẩn bị:
  - filter theo thời gian
  - pagination
  - export async nếu sau này cần
- Báo cáo nặng nên ưu tiên query aggregate chuẩn hoặc snapshot/report table nếu dữ liệu lớn

---

## 26) Chuẩn triển khai cho Codex

Codex nên:
1. Ưu tiên tạo skeleton chuẩn folder/domain trước.
2. Tạo enum, DTO, repository interface, service contract trước khi viết logic sâu.
3. Khi viết CRUD hoặc query, luôn nghĩ tới `WarehouseContext`.
4. Khi đụng tới nhập/xuất/chuyển/điều chỉnh, phải route qua `StockService`.
5. Tách migration hợp lý, không nhồi một migration quá lớn nếu dễ gây khó bảo trì.
6. Viết code theo hướng có thể chạy từng phase.

### Prompting hint cho Codex
Khi được giao task, hãy tự kiểm tra:
- Task này có đụng tới tồn kho không?
- Có cần warehouse scope không?
- Có cần transaction không?
- Có cần enum trạng thái không?
- Có cần movement log không?

Nếu câu trả lời là có, phải đưa chúng vào thiết kế ngay.

---

## 27) Chuẩn triển khai cho Claude Code

Claude Code nên:
1. Ưu tiên phân tích nghiệp vụ trước khi sửa code.
2. Khi refactor, giữ nguyên logic dữ liệu quan trọng nếu chưa chắc.
3. Với mọi thay đổi liên quan chứng từ, luôn chỉ ra rõ:
   - trạng thái nào tác động tồn
   - bảng nào bị ghi
   - movement nào được sinh ra
4. Nếu thêm repository/service mới, phải đảm bảo naming nhất quán với domain.
5. Nếu đề xuất cải tiến, ưu tiên cải tiến tính đúng đắn trước UI.

### Prompting hint cho Claude Code
Trước khi sinh code, hãy tự trả lời:
- Đây là thao tác đọc hay ghi?
- Nếu ghi, có phải nghiệp vụ làm thay đổi stock không?
- Nếu có, before/after quantity được tính ở đâu?
- Có rollback an toàn chưa?
- Có scope warehouse chưa?
- Có chặn người dùng ngoài quyền chưa?

---

## 28) Checklist bắt buộc trước khi merge

- [ ] Có dùng WarehouseContext ở các query nghiệp vụ liên quan kho
- [ ] Có enum cho trạng thái hoặc loại movement
- [ ] Có transaction cho thao tác thay đổi tồn
- [ ] Có ghi `inv_stock_movements`
- [ ] Không update stock trực tiếp từ controller
- [ ] Permission theo kho hợp lệ
- [ ] Code đặt đúng domain
- [ ] Có request validation
- [ ] Có test tối thiểu cho flow chính
- [ ] Chứng từ completed không bị sửa tuỳ tiện

---

## 29) Định nghĩa thành công

Một implementation được coi là đúng khi:
- nhập kho làm tăng tồn chính xác
- xuất kho làm giảm tồn chính xác
- chuyển kho không làm mất dấu lịch sử giữa kho nguồn và kho đích
- trả hàng đi đúng hướng nhập/xuất
- điều chỉnh tồn không xoá lịch sử cũ
- lịch sử kho luôn giải thích được current stock
- user chỉ nhìn thấy và thao tác được trên kho họ có quyền
- báo cáo có thể dựa trên movement log một cách đáng tin cậy

---

## 30) Kết luận

Inventory plugin này phải được xây theo tư duy:
- **warehouse-scoped**
- **stock-safe**
- **audit-friendly**
- **domain-oriented**
- **easy-to-extend**

Bất kỳ agent nào tham gia code cũng phải tuân thủ 4 nguyên tắc tối quan trọng:
1. Không thay đổi tồn kho ngoài `StockService`.
2. Không bỏ qua `WarehouseContext`.
3. Không thiếu `inv_stock_movements`.
4. Không để trạng thái chứng từ mơ hồ.

---
## 31) Gợi ý file/class nên tạo đầu tiên

```txt
src/Supports/WarehouseContext.php
src/Http/Middleware/ResolveWarehouseContext.php
src/Enums/DocumentStatusEnum.php

src/Domains/Stock/Services/StockService.php
src/Domains/Stock/Repositories/StockRepository.php
src/Domains/Stock/Repositories/StockMovementRepository.php

src/Domains/Warehouse/Models/Warehouse.php
src/Domains/Warehouse/Models/WarehouseLocation.php

src/Domains/Product/Models/Product.php
src/Domains/Product/Models/ProductCategory.php
src/Domains/Product/Models/Unit.php

src/Domains/Import/Models/Import.php
src/Domains/Import/Models/ImportItem.php
src/Domains/Import/UseCases/ConfirmImportUseCase.php

src/Domains/Export/Models/Export.php
src/Domains/Export/Models/ExportItem.php
src/Domains/Export/UseCases/ConfirmExportUseCase.php

src/Domains/Transfer/Models/InternalTransfer.php
src/Domains/Transfer/Models/InternalTransferItem.php

src/Domains/Adjustment/Models/StockAdjustment.php
src/Domains/Adjustment/Models/StockAdjustmentItem.php
```

---

## 32) One-line principle cho agent

> Mọi chứng từ kho chỉ thật sự hoàn tất khi tồn kho hiện tại, movement log, trạng thái chứng từ, và phân quyền theo kho đều đồng bộ với nhau.
