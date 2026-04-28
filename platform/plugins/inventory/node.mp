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


inv_ncc ( nhà cung cấp )
    id, name_compaly, phone_compaly, gmail_compaly, type(cá nhân, tổ chức), note
   
inv_ncc_infomation 
    id ncc_id  name_bank, number_bank , hunder_bank , cccd/ , MST(mã số thuế) , name_liên_hệ, phone, gmail, chức vụ, address, contry, state, district 

inv_ncc_items
    id ncc_id product_id

inv_customers ( khách hàng )

inv_suppliers (hoá đơn kho mua hàng) 
    id, ncc_id, id_warehouse, user_id, code, status, document_date, total

inv_suppliers_items
    id suppliers_id, warehouses_product_id, price, qty

inv_warehouses_product  
    id,  prd_id , id_warehouse

//o
inv_warehouses
    id,code,name,type, manager_id, address, province_id / ward_id, phone, email, is_active,description

inv_warehouse_locations


inv_export

inv_export_items
inv_import  (type : purchase(đối tác mua) , transfer(chuyển kho), return(trả hàng) , manual(khác))  (partner_type:supplier, warehouse , customer )
    id type warehouse_id warehouse_code warehouse_name partner_type partner_id partner_code partner_name
    partner_phone partner_email partner_address province_id ward_id 
    doc_code(tự sinh ra.) reference_code (mã tham chiếu), posting_date( ngày hạch toán), document_date( ngày chứng từ) , received_at ngày nhập kho , receiver_name
    receiver_phone status['draft','confirmed','completed','cancelled'] note , created_by( người tạo phiếu) , approved_by (người duyệt phiếu), deleted_at.
inv_import_items
    id import_id product_id product_name product_code document_qty(trên phiếu) , received_qty(thực), note, delete_at, unit_id, unit_name,warehouse_location_id, amount


inv_packing_lists (danh sách đóng gói)
inv_packing_list_items
inv_packages  


inv_returns (trả hàng)
inv_return_items


inv_setting


inv_internal_transfers
inv_internal_transfers_item







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
    ├── ncc/ (xong)
    ├── Product/
    │   ├── Models/
    │   ├── Services/
    │   ├── Repositories/
    │   ├── Http/
    │   ├── Enums/
    │   ├── usecase/
    │   └── DTO/
    │
    ├── WarehouseStaff/ (xong)
    │
    ├── Warehouse/
    │   
    ├── Import/  (80/100) chưa test crud
    │  
    ├── Export/ (80/100) chưa test crud
    │   
    ├── Transfer/ chuyển qua các kho     (0/100)
    │  
    ├── Stock/Stock history (lịch sử di chuyển)   (làm cuối cùng)
    │  
    ├── Packing/ () (50/100) chưa test crud1 ( lấy phiếu của export đã đc xác nhận để lấy sản phẩm )
    ├── Adjustment/  (điều chỉnh tồn) (0/100)
    ├── Report/ báo cáo kho (0/100)1
    ├── return/ báo cáo kho (0/100)1    ( 2 trường hợp ( khách trả hàng tìm đơn export để tạo thành đơn ( lấy sản phẩm), kho trả nhà sản xuất ( import ) ) )
    └── Setting/  (0/100) (bổ xung gần cuối)
    └──document history (lịch sử tạo phiếu, ai duyệt, trạng thái thay đổi,...) (làm cuối cùng)

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

    tạo phiếu xác nhận thì mới thay đổi dữ liệu

    usecase request -> controller -> Usecase(sử lý logic) ->repository(sử lý DB) (khai báo trong provider)
