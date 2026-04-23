# Hướng Dẫn Phát Triển Plugin Inventory

Tài liệu này dành cho lập trình viên và AI agent đang bảo trì `platform/plugins/inventory`.

## Nguyên Tắc Kiến Trúc

Inventory được tổ chức theo domain. Hãy đặt code theo từng tính năng bên trong `src/Domains/<Domain>`. Chỉ giữ các thư mục gốc trong `src/` cho phần hạ tầng dùng chung của plugin.

Các domain hiện tại:

- `WarehouseStaff`
- `Warehouse`
- `Supplier`
- `GoodsReceipt`

Code gốc của plugin nên giữ gọn:

- `src/Providers/InventoryServiceProvider.php`
- `src/Http/Middleware/InventoryContextMiddleware.php`
- helper dùng chung trong `src/Helpers`
- code context/support dùng chung trong `src/Support`
- enum dùng chung trong `src/Enums`
- model/form/table/controller inventory gốc kiểu legacy nếu vẫn còn được sử dụng

## Chuẩn Provider

`InventoryServiceProvider` chịu trách nhiệm tải tài nguyên dùng chung của plugin và đăng ký các domain provider:

```php
use Botble\Inventory\Domains\GoodsReceipt\Providers\GoodsReceiptProvider;
use Botble\Inventory\Domains\Supplier\Providers\SupplierProvider;
use Botble\Inventory\Domains\Warehouse\Providers\WarehouseProvider;
use Botble\Inventory\Domains\WarehouseStaff\Providers\WarehouseStaffProvider;

$this->app->register(SupplierProvider::class);
$this->app->register(GoodsReceiptProvider::class);
$this->app->register(WarehouseStaffProvider::class);
$this->app->register(WarehouseProvider::class);
```

Root provider nên phụ trách:

- tải namespace/config/translation/route/view/migration
- menu cha gốc của inventory
- singleton `InventoryContext`
- alias middleware `inventory.context`
- đăng ký các domain provider

Domain provider phụ trách menu riêng của domain, binding repository, và logic boot riêng của domain.

## Routes Và Permissions

Routes đặt trong `routes/web.php` bên trong admin route group:

```php
Route::group([
    'prefix' => 'inventories',
    'as' => 'inventory.',
    'middleware' => ['web', 'core', 'auth', 'inventory.context'],
], function () {
    // domain route groups
});
```

Hãy dùng import controller theo domain, route name tường minh, và permission key tường minh:

```php
use Botble\Inventory\Domains\WarehouseStaff\Http\Controllers\WarehouseStaffController;
```

Giữ cho route permissions, table action permissions, menu permissions và `config/permissions.php` đồng bộ với nhau. Không trộn cờ permission có tiền tố và không có tiền tố trong cùng một domain, trừ khi bạn migrate toàn bộ domain đó.

Đặt route literal trước route tham số. Ví dụ: `products/search` phải được khai báo trước `/{supplier}`.

## Chuẩn Domain WarehouseStaff

`WarehouseStaff` quản lý nhân sự kho, chức danh trong kho, và logic phân công nhân sự vào kho.

Các file quan trọng:

```txt
src/Domains/WarehouseStaff/
  Forms/WarehouseStaffForm.php
  Forms/WarehousePositionForm.php
  Http/Controllers/WarehouseStaffController.php
  Http/Controllers/WarehousePositionController.php
  Http/Requests/WarehouseStaffRequest.php
  Http/Requests/WarehousePositionRequest.php
  Models/WarehouseStaff.php
  Models/WarehouseStaffAssignments.php
  Models/WarehousePosition.php
  Models/UserWarehouse.php
  Providers/WarehouseStaffProvider.php
  Repositories/Eloquent/WarehouseStaffAssignmentRepository.php
  Repositories/Interfaces/WarehouseStaffAssignmentInterface.php
  Tables/WarehouseStaffTable.php
  Tables/WarehousePositionTable.php
  Usecase/AssignmentsUsercase.php
```

Khi mở rộng domain này, hãy giữ nguyên cách đặt tên thư mục/lớp đang có. Code hiện tại dùng `Usecase` và `AssignmentsUsercase`.

### Các Bảng WarehouseStaff

Migration hiện tại tạo các bảng:

- `inv_warehouse_positions`
- `inv_warehouse_staff`
- `inv_warehouse_staff_assignments`
- `inv_user_warehouses`

`inv_warehouse_positions` lưu vai trò trong kho:

- `code`
- `name`
- `level`
- `is_active`

`inv_warehouse_staff` lưu hồ sơ nhân sự kho:

- `user_id`
- `staff_code`
- `full_name`
- `phone`
- `email`
- `status`

`inv_warehouse_staff_assignments` lưu quyền truy cập kho của nhân sự:

- `staff_id`
- `warehouse_id`
- `position_id`
- `is_primary`
- `status`
- `start_date`
- `end_date`
- unique `staff_id + warehouse_id`

`inv_user_warehouses` có tồn tại, nhưng middleware scope kho hiện tại dùng `inv_warehouse_staff_assignments`, không dùng `inv_user_warehouses`.

### Provider Của WarehouseStaff

`WarehouseStaffProvider` nên:

- bind `WarehouseStaffAssignmentInterface` tới `WarehouseStaffAssignmentRepository`
- truyền model `WarehouseStaffAssignments` vào repository đó
- đăng ký menu admin Warehouse Staff
- đăng ký menu admin Warehouse Positions

Giữ các menu này trong `Domains/WarehouseStaff/Providers/WarehouseStaffProvider.php`. Không chuyển ngược chúng về `InventoryServiceProvider`.

### Luồng Ghi Dữ Liệu Nhân Sự Kho

`WarehouseStaffController` hiện là bộ điều phối luồng ghi dữ liệu:

1. Tạo hoặc cập nhật model staff thông qua `WarehouseStaffForm`.
2. Chuẩn hóa danh sách `warehouse_id[]` được chọn từ request.
3. Gọi `AssignmentsUsercase::updateWarehouseId()`.
4. Bọc thao tác lưu staff và đồng bộ assignment trong `DB::transaction()`.

`AssignmentsUsercase` phụ trách đồng bộ assignment:

1. Xóa các assignment của những kho không còn được chọn.
2. `firstOrNew()` cho từng cặp `staff_id + warehouse_id`.
3. Gán `start_date` khi assignment là mới.
4. Gán `position_id`.
5. Lưu assignment.

Khi thêm logic assignment, không đưa chi tiết repository vào controller.

### Luồng Ghi Dữ Liệu Chức Danh

`WarehousePositionController` dùng luồng form chuẩn của Botble:

1. Validate với `WarehousePositionRequest`.
2. Lưu thông qua `WarehousePositionForm`.
3. Trả về Botble HTTP response với previous/next URLs.

Các thao tác xóa dùng `DeleteResourceAction`.

### Form WarehouseStaff

`WarehouseStaffForm` hiện đang tải:

- system users từ `users`
- danh sách kho từ `Domains/Warehouse/Models/Warehouse`
- danh sách chức danh từ `WarehousePosition`
- các kho đã chọn thông qua `assignments()`

Select nhiều kho được đặt tên là `warehouse_id[]`. Code controller hiện tại kỳ vọng dữ liệu dạng mảng lồng nhau và lấy `$item[0]`. Nếu refactor phần này, hãy chuẩn hóa request input trong một helper nhỏ và vẫn giữ backward compatibility với dạng dữ liệu form đang submit.

Khi sửa label, hãy dùng translation keys hoặc label UTF-8 hợp lệ. Không giữ lại text lỗi font/mojibake.

### Validation Của WarehouseStaff

`WarehouseStaffRequest` nên validate:

- `full_name` bắt buộc, kiểu string, max 220
- `phone` bắt buộc, kiểu string, max 220
- `email` bắt buộc, kiểu string, max 220
- `staff_code` unique trong `inv_warehouse_staff`, bỏ qua route model hiện tại khi update
- `warehouse_id` bắt buộc, kiểu array

Khi cải thiện validation, hãy kiểm tra thêm:

- mọi kho được chọn đều tồn tại
- `position` được chọn tồn tại trong `inv_warehouse_positions`
- `user_id` tùy chọn phải tồn tại trong `users`
- email có đúng format email nếu nghiệp vụ yêu cầu validate email thật

`WarehousePositionRequest` nên validate:

- `name` bắt buộc, kiểu string, max 220
- `code` unique trong `inv_warehouse_positions`, bỏ qua route model hiện tại khi update
- `level` là số nguyên trong khoảng từ 0 đến 100

### Context Theo Phạm Vi Kho

Các route admin của inventory dùng `inventory.context`.

`InventoryContextMiddleware`:

1. Reset danh sách warehouse IDs trong context và cờ super-admin.
2. Đánh dấu người dùng có `super_user === 1` là super admin.
3. Tìm bản ghi `WarehouseStaff` của người dùng hiện tại theo `user_id`.
4. Tải danh sách warehouse IDs được phân công từ `WarehouseStaffAssignments`.
5. Lưu các IDs đó vào `InventoryContext`.

Các helper dùng chung:

- `inventory_context()`
- `inventory_warehouse_ids()`
- `inventory_is_super_admin()`

Hãy dùng các helper này cho các truy vấn admin có phạm vi theo kho. Ví dụ, `WarehouseStaffTable` giới hạn người dùng không phải super admin theo `assignments.warehouse_id`.

### Bảng Hiển Thị WarehouseStaff

`WarehouseStaffTable` nên:

- dùng `WarehouseStaff::class`
- eager-load `assignments.warehouse`
- hiển thị tên các kho được phân công thông qua formatted column không orderable, không searchable
- giới hạn người dùng không phải super admin theo `inventory_warehouse_ids()`
- dùng route names theo `inventory.warehouse-staff.*`

`WarehousePositionTable` nên:

- dùng `WarehousePosition::class`
- hiển thị trạng thái active/inactive nhất quán
- dùng route names theo `inventory.warehouse-positions.*`

### Routes Và Cờ Permission Chuẩn Của WarehouseStaff

Route names chuẩn:

- `inventory.warehouse-staff.index`
- `inventory.warehouse-staff.create`
- `inventory.warehouse-staff.store`
- `inventory.warehouse-staff.edit`
- `inventory.warehouse-staff.update`
- `inventory.warehouse-staff.destroy`
- `inventory.warehouse-positions.index`
- `inventory.warehouse-positions.create`
- `inventory.warehouse-positions.store`
- `inventory.warehouse-positions.edit`
- `inventory.warehouse-positions.update`
- `inventory.warehouse-positions.destroy`

Cờ permission chuẩn cho domain này:

- `warehouse-staff.index`
- `warehouse-staff.create`
- `warehouse-staff.edit`
- `warehouse-staff.destroy`
- `warehouse-positions.index`
- `warehouse-positions.create`
- `warehouse-positions.edit`
- `warehouse-positions.destroy`

Khi chỉnh sửa domain này, hãy đồng bộ tất cả các nơi sau:

- key `permission` trong route
- permission của table header/action/bulk
- permission trong menu provider
- `config/permissions.php`

Tránh các trường hợp lệch nhau như:

- `warehouse-positions.delete` trong routes trong khi table/config dùng `warehouse-positions.destroy`
- `inventory.warehouse-staff.destroy` trong bulk action của table trong khi domain dùng `warehouse-staff.destroy`
- thiếu các mục `warehouse-positions.*` trong `config/permissions.php`

### Quy Tắc Dọn Dẹp WarehouseStaff

Repository của WarehouseStaff phải nằm trong `Domains/WarehouseStaff/Repositories`.

Nếu một file trong `Domains/Warehouse` lại khai báo namespace `Botble\Inventory\Domains\WarehouseStaff`, thì đó không phải file chuẩn của WarehouseStaff. Hãy xem đó là việc cleanup trong một task riêng, và tránh copy từ đó.

Không dựa vào `WarehouseStaff::warehouse()` cho truy cập đa kho. Quan hệ đúng là `assignments()` và mỗi assignment thuộc về một warehouse.

## Domain Warehouse

Code thuộc Warehouse phải nằm trong `src/Domains/Warehouse`.

Một số file hiện tại quan trọng:

- `Models/Warehouse.php`
- `Models/WarehouseLocation.php`
- `Models/WarehouseProduct.php`
- `Http/Controllers/WarehouseController.php`
- `Http/Controllers/WarehouseProductController.php`
- `Http/Requests/WarehouseProductRequest.php`
- `Services/WarehouseProductService.php`
- `Tables/WarehouseTable.php`
- `Providers/WarehouseProvider.php`

`WarehouseProvider` đăng ký menu warehouse và bind `WarehouseInterface` tới `WarehouseRepository`.

Các truy vấn danh sách kho nên dùng `inventory_warehouse_ids()` cho người dùng không phải super admin khi màn hình có phạm vi theo kho.

## Cấu Hình Sản Phẩm Theo Kho

Cấu hình sản phẩm theo kho thuộc domain Warehouse.

Bảng bridge bắt buộc là `inv_warehouse_products`. Bảng này trả lời câu hỏi: sản phẩm hoặc biến thể nào được phép quản lý trong kho nào.

Các file quan trọng:

- `Models/WarehouseProduct.php`
- `Http/Controllers/WarehouseProductController.php`
- `Http/Requests/WarehouseProductRequest.php`
- `Services/WarehouseProductService.php`
- `resources/views/warehouse/show.blade.php`

Các quy tắc validation:

- warehouse trên route phải tồn tại
- product phải tồn tại
- variation phải thuộc product đã chọn nếu có truyền vào
- default location phải thuộc warehouse trên route
- supplier product phải khớp với supplier và product
- tổ hợp warehouse/product/variation trùng nhau phải bị chặn trong service vì MySQL unique index vẫn cho phép lặp lại giá trị nullable

Quy tắc xóa:

- nếu chưa có lịch sử goods receipt, stock transaction hoặc stock balance thì có thể xóa dòng đó
- nếu đã có lịch sử thì cập nhật `is_active = false` thay vì xóa để báo cáo không bị sai lệch

Tìm kiếm sản phẩm trong goods receipt chỉ được trả về các sản phẩm đang active và đã được cấu hình trong `inv_warehouse_products` cho warehouse đã chọn.

## Domain Supplier

Code của Supplier nằm tại:

```txt
src/Domains/Supplier/
  Forms/SupplierForm.php
  Http/Controllers/SupplierController.php
  Http/Requests/SupplierRequest.php
  Models/Supplier.php
  Models/SupplierAddress.php
  Models/SupplierApproval.php
  Models/SupplierBank.php
  Models/SupplierContact.php
  Models/SupplierProduct.php
  Providers/SupplierProvider.php
  Services/SupplierService.php
  Tables/SupplierTable.php
```

Không thêm lại các file Supplier kiểu root cũ vào:

```txt
src/Forms/SupplierForm.php
src/Http/Controllers/SupplierController.php
src/Http/Requests/SupplierRequest.php
src/Models/Supplier*.php
src/Services/SupplierService.php
src/Tables/SupplierTable.php
src/Repositories/*Supplier*
```

`SupplierService` phụ trách các thao tác ghi nhiều bảng của supplier và các thao tác approval. Hãy dùng `DB::transaction()` khi đồng bộ contacts, addresses, banks và supplied products.

Bảng danh sách supplier nên hiển thị các thông tin vận hành:

- mã nhà cung cấp
- tên nhà cung cấp
- loại nhà cung cấp
- mã số thuế
- liên hệ chính
- số lượng sản phẩm cung ứng
- badge trạng thái
- ngày tạo

Hãy dùng eager loading và `withCount()` cho dữ liệu danh sách supplier. Các cột tính toán phải không orderable và không searchable.

## Domain GoodsReceipt

Code của goods receipt nằm trong `src/Domains/GoodsReceipt`.

Domain này quản lý:

- model header `GoodsReceipt` cho `inv_goods_receipts`
- model dòng `GoodsReceiptItem` cho `inv_goods_receipt_items`
- các model batch và stock cho schema receipt/stock
- `GoodsReceiptService` cho create/update có transaction
- endpoint tìm kiếm sản phẩm và gợi ý supplier product
- `GoodsReceiptTable`
- `GoodsReceiptProvider`

Các thao tác create/update phải giữ transaction vì một receipt header có nhiều item lines. Service sẽ tính subtotal, discount, tax và total từ các dòng dữ liệu.

Gợi ý supplier product nên đọc từ `inv_supplier_products` và tự điền product, supplier price, MOQ và ghi chú lead time.

Không tự động ghi inventory movement khi một receipt được đánh dấu `completed`, trừ khi có task định nghĩa rõ quy tắc posting. Khi posting, phải cập nhật `inv_stock_transactions`, `inv_stock_balances`, và có thể cả `ec_products.quantity` cùng lúc.

## Migrations

Các migration của Inventory nằm trong `platform/plugins/inventory/database/migrations`.

Trước khi debug lỗi runtime, hãy kiểm tra trạng thái migration:

```powershell
php artisan migrate:status --path=platform/plugins/inventory/database/migrations
```

Khi thay đổi schema:

- tạo migration cộng thêm thay vì sửa migration đã chạy
- giữ tên bảng có tiền tố `inv_`
- thêm index cho foreign keys và các cột tìm kiếm nhiều
- chỉ thêm foreign key khi bảng được tham chiếu tồn tại trong cùng đường cài đặt
- giữ đúng kiểu ID UUID hay bigint theo bảng hiện có

## Giao Diện Và Ngôn Ngữ

Hãy dùng các quy ước Form/Table của Botble đang có sẵn trong domain.

Đối với label ở admin:

- ưu tiên translation keys trong `resources/lang/en/inventory.php` và `resources/lang/vi/inventory.php`
- giữ file ngôn ngữ ở dạng UTF-8 hợp lệ
- tránh label hard-coded bị lỗi font/mojibake
- dùng `FormattedColumn` cho giá trị tính toán và badge trong bảng

Với các màn hình warehouse/product, hãy làm theo `Design.md` khi thay đổi layout hoặc view.

## Cleanup

`backup.php` trong thư mục gốc của plugin không phải code runtime. Nó có thể chứa nội dung backup kiểu diff và làm lỗi PHP lint. Không đưa file này vào quy trình lint source tự động.

Khi chuyển một tính năng vào `Domains/<Domain>`:

- cập nhật namespace
- cập nhật routes
- cập nhật import và đăng ký provider
- cập nhật permissions
- chỉ xóa các bản copy root cũ khi xác nhận chúng đã lỗi thời
- giữ nguyên các thay đổi worktree bẩn nhưng không liên quan

## Các Lệnh Kiểm Tra

Chạy lint cho các file đã chỉnh:

```powershell
php -l platform/plugins/inventory/src/Domains/WarehouseStaff/Providers/WarehouseStaffProvider.php
php -l platform/plugins/inventory/src/Domains/WarehouseStaff/Http/Controllers/WarehouseStaffController.php
php -l platform/plugins/inventory/src/Domains/WarehouseStaff/Usecase/AssignmentsUsercase.php
php -l platform/plugins/inventory/src/Providers/InventoryServiceProvider.php
```

Chạy lint toàn bộ source của plugin:

```powershell
Get-ChildItem -Path 'platform/plugins/inventory/src' -Recurse -Filter '*.php' | ForEach-Object { php -l $_.FullName }
```

Kiểm tra routes của WarehouseStaff:

```powershell
php artisan route:list --name=inventory.warehouse-staff
php artisan route:list --name=inventory.warehouse-positions
```

Khi có chỉnh sửa, kiểm tra routes của Supplier hoặc GoodsReceipt:

```powershell
php artisan route:list --name=inventory.suppliers
php artisan route:list --name=inventory.goods-receipts
```

Kiểm tra Laravel boot/autoload cho một class:

```powershell
php -r "require 'vendor/autoload.php'; `$app = require 'bootstrap/app.php'; `$kernel = `$app->make('Illuminate\\Contracts\\Console\\Kernel'); `$kernel->bootstrap(); echo class_exists('Botble\\Inventory\\Domains\\WarehouseStaff\\Providers\\WarehouseStaffProvider') ? 'ok' : 'missing';"
```

## Checklist Review

Trước khi hoàn thành một thay đổi trong plugin Inventory:

- Code tính năng nằm đúng trong thư mục domain phù hợp.
- Routes trỏ đúng tới domain controllers.
- Domain provider được đăng ký từ `InventoryServiceProvider`.
- Menu và repository bindings của domain nằm trong domain provider.
- Permissions khớp với route keys, table actions, menus và `config/permissions.php`.
- Các màn hình có phạm vi theo kho dùng `inventory.context` và các helper khi cần.
- Các thao tác ghi nhiều bảng có dùng transaction.
- Các cột tính toán trong bảng không vô tình bị cho phép SQL search/order.
- Label admin là UTF-8 hợp lệ hoặc dùng translation keys.
- PHP lint và route checks đều pass ở các phần đã chỉnh.
