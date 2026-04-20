plugin inventory

chức năng:
+ nhân viên kho
+ thông tin sản phẩm
+ phiếu mua hàng
+ nhập kho
+ xuất kho
+ quản lý danh sách đóng gói
+ phiếu chuyển kho nội bộ
+ điều chỉnh tồn kho (kiểm kê kho, phiếu điều chỉnh tồn kho)
+ Quản lý Nhập-Xuất đơn trả
+ kho
+ lịch sử kho
+ các báo cáo về kho (xuất nhập tồn, phân tích tồn kho, định giá hàng tồn, thời gian bảo hành, biết động hàng tồn, số dư hàng tồn...)
+ cài đặt chung (phân quyền, đơn vị tính, nhóm hàng hoá,cấu hình tồn kho ..)

=> trạng thái chứng từ
luồng hoạt động:
nhập kho
- phiếu mua hàng -> nhập kho(có thẻ chọn luôn vị trí, chú ý trạng thái chứng từ) -> điều chỉnh tồn kho( nếu sp chưa có vị trí thì vào đây chỉnh) -> lưu vào lịch sử kho
xuất kho
- xuất kho -> quản lý danh sách đóng gói -> lịch sử kho
- phiếu chuyển kho nội bộ -> xuất kho -> quản lý danh sách đóng gọi -> lịch sử -> nhập kho ( thay phiếu mua hàng -> phiếu chuyển kho nội bộ)
trả hàng
- quản lý nhập - xuất đơn trả -> nhập kho (nếu là khách hàng trả kho)
                                -> xuất kho (nếu kho yêu cầu trả hàng cho đối tác)

bảng DB
inv_warehouse_staff
inv_warehouse_staff_assignments (phân công nhân sự + phân quyền)
inv_warehouse_positions (chức vụ trong kho)

inv_suppliers (kho mua hàng)
inv_customers ( khách hàng )
inv_product
inv_warehouses
inv_warehouse_locations
inv_export
inv_export_items
inv_import
inv_import_items
inv_packing_lists (danh sách đóng gói)
inv_packing_list_items 
inv_setting
inv_internal_transfers
inv_internal_transfers_item
inv_returns (trả hàng)
inv_return_items
inv_stock_movements (lịch sử)
inv_stock_adjustments (lưu phiếu khi thực tế khác với trên dữ liệu (mất hàng, vỡ hàng , thừa hàng phải điều chỉnh ....))
inv_stock_adjustment_items

inv_stocks (tồn hiện tại)
inv_stock_movements (tại sao tồn kho đó) 
    #1: before 20 | +50 | after 70
    #2: before 70 | -10 | after 60
    #3: before 60 | +5  | after 65

có thể có 
inv_product_categories (nhóm hàng hoá)
inv_units (đơn vị tính)


code

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
    │   ├── usecase/
    │   └── DTO/
    ├── Warehouse/
    │   
    ├── Import/
    │  
    ├── Export/
    │   
    ├── Transfer/
    │  
    ├── Stock/
    │  
    ├── Packing/
    ├── Adjustment/
    ├── Report/
    └── Setting/

    chú ý lúc làm phân quyền. là làm 1 middleware để định kho + tạo 1 warehouseContext dùng cho toàn request 
    để lưu 
    WarehouseContext
    {
        getWarehouseId(): ?int
        getAccessibleWarehouseIds(): array 
        isSuperAdmin(): bool
    }
    lúc gọi ra:
    $warehouseId = app(WarehouseContext::class)->getWarehouseId();
    xong trong Repositories tạo 1 function query($warehouseId) xong từ đó về sau khi thay vì dùng 