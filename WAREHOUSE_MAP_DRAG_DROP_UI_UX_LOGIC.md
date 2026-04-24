# Logic & UI/UX Chuẩn Cho Sơ Đồ Kho Kéo Thả

## 1. Mục tiêu tài liệu

Tài liệu này hướng dẫn AI Agent / Developer thiết kế và triển khai phần **Sơ đồ kho kéo thả** cho module Inventory.

Mục tiêu:

- Người dùng không rành kho vẫn có thể setup kho dễ dàng.
- Hỗ trợ cả kho nhỏ và kho lớn.
- Có thể kéo thả khu vực, kệ, rack, đường đi.
- Có thể nhân bản khu vực/kệ/rack.
- Có thể kéo dài rack/kệ để tăng sức chứa.
- Có thể cấu hình số tầng, số ô, số pallet mỗi tầng.
- Có thể tự sinh cây vị trí kho từ sơ đồ.
- Sơ đồ kho trực quan nhưng không thay thế cây vị trí kho.

Nguyên tắc quan trọng:

```txt
Cây vị trí kho = dữ liệu chuẩn
Sơ đồ kho = lớp hiển thị trực quan
```

---

# 2. Tư duy tổng thể

Phần kho cần tách thành 3 lớp:

## 2.1. Lớp 1 — Cây vị trí kho

Đây là dữ liệu chuẩn để hệ thống quản lý kho.

Dùng bảng:

```txt
inv_warehouse_locations
```

Ví dụ:

```txt
Kho HCM
├── RECEIVING
├── WAITING_PUTAWAY
├── QC_HOLD
├── STORAGE
│   ├── ZONE-A
│   │   ├── RACK-01
│   │   │   ├── LEVEL-01
│   │   │   │   ├── SLOT-01
│   │   │   │   └── SLOT-02
```

Đây là nơi lưu:

- vị trí thật
- parent/child
- code
- name
- type
- level
- path
- status

## 2.2. Lớp 2 — Sơ đồ kho trực quan

Đây là phần user nhìn thấy và thao tác kéo thả.

Dùng các bảng:

```txt
inv_warehouse_maps
inv_warehouse_map_items
```

Sơ đồ có thể hiển thị:

- khu nhận hàng
- khu QC
- khu chờ xếp
- khu damaged
- đường đi
- zone
- rack
- shelf
- pallet area

Sơ đồ chỉ dùng để hiển thị, thao tác trực quan và liên kết với location tree.

## 2.3. Lớp 3 — Bộ sinh cấu trúc vị trí

Đây là phần giúp user không cần tự tạo từng vị trí.

Ví dụ user kéo một `pallet rack` ra sơ đồ, nhập:

```txt
Số khoang: 5
Số tầng: 4
Số pallet mỗi tầng: 2
```

Hệ thống tự sinh:

```txt
RACK-A01
├── LEVEL-01
│   ├── SLOT-01
│   └── SLOT-02
├── LEVEL-02
│   ├── SLOT-01
│   └── SLOT-02
...
```

Tổng sức chứa:

```txt
5 × 4 × 2 = 40 pallet
```

---

# 3. Hỗ trợ kho nhỏ và kho lớn

## 3.1. Kho nhỏ

Kho nhỏ nên đơn giản.

Luồng:

```txt
Hàng → Vị trí
```

Không bắt buộc pallet.

Cấu trúc thường dùng:

```txt
RECEIVING
STORAGE-A
STORAGE-B
SHELF-01
SHELF-02
DAMAGED
```

Hoặc chi tiết hơn:

```txt
SHELF-A
├── LEVEL-01
│   ├── BIN-01
│   ├── BIN-02
│   └── BIN-03
├── LEVEL-02
│   ├── BIN-01
│   ├── BIN-02
│   └── BIN-03
```

### UI cho kho nhỏ

Không hỏi các khái niệm khó như:

- bay count
- pallet position
- aisle side
- double depth

Chỉ hỏi:

- Bạn muốn tạo khu hay kệ?
- Kệ có mấy tầng?
- Mỗi tầng có mấy ô?
- Có dùng pallet không?

## 3.2. Kho lớn

Kho lớn có thể dùng pallet.

Luồng:

```txt
Hàng → Pallet → Vị trí
```

Cấu trúc thường dùng:

```txt
ZONE-A
├── RACK-01
│   ├── LEVEL-01
│   │   ├── PALLET-POS-01
│   │   └── PALLET-POS-02
│   ├── LEVEL-02
│   │   ├── PALLET-POS-01
│   │   └── PALLET-POS-02
```

### UI cho kho lớn

Cho phép cấu hình:

- số khoang rack
- số tầng
- số pallet mỗi tầng
- duplicate rack
- đường đi
- khu pallet floor
- khu QC
- khu staging

---

# 4. Các loại item trên sơ đồ

Trên sơ đồ kho cần phân loại rõ các item.

## 4.1. Area / Zone

Là khu vực lớn.

Ví dụ:

- Receiving
- QC
- Storage
- Damaged
- Dispatch
- Zone A
- Zone B

### Vai trò

- chia khu
- có thể chứa item con
- thường là location cha
- không nhất thiết là nơi chứa hàng cuối cùng

## 4.2. Aisle / Path

Là đường đi.

Ví dụ:

- lối đi chính
- lối đi xe nâng
- lối đi giữa 2 dãy rack

### Rule

- chỉ để hiển thị
- không chứa hàng
- không chứa pallet
- không post stock vào đây
- `is_stockable = false`

## 4.3. Simple Shelf

Dùng cho kho nhỏ.

Ví dụ:

```txt
Kệ A
├── Tầng 1
│   ├── Ô 1
│   ├── Ô 2
│   └── Ô 3
```

### Cấu hình

- số tầng
- số ô mỗi tầng
- có dùng pallet hay không
- sức chứa tối đa nếu cần

## 4.4. Pallet Rack

Dùng cho kho lớn.

Ví dụ:

```txt
RACK-A01
├── LEVEL-01
│   ├── PALLET-POS-01
│   └── PALLET-POS-02
```

### Cấu hình

- số khoang / bay
- số tầng
- số vị trí pallet mỗi tầng
- tổng pallet capacity
- zone cha

## 4.5. Floor Pallet Area

Dùng cho khu để pallet dưới sàn.

Ví dụ:

```txt
FLOOR-PALLET-A
├── POS-01
├── POS-02
├── POS-03
```

### Cấu hình

- số vị trí pallet
- số hàng
- số cột
- khoảng cách giữa pallet

## 4.6. Leaf Storage Slot

Là vị trí nhỏ nhất có thể chứa hàng/pallet.

Ví dụ:

- BIN-01
- SLOT-01
- PALLET-POS-01

Đây là nơi dùng cho:

- storage item
- pallet
- stock balance
- putaway

---

# 5. Logic kéo thả chuẩn

## 5.1. Toolbar trên sơ đồ

Cần có thanh công cụ:

```txt
Khu vực
Đường đi
Kệ đơn giản
Rack pallet
Khu pallet sàn
Text/Label
Duplicate
Delete
Zoom in/out
Save layout
```

## 5.2. Kéo item vào canvas

Khi user kéo item vào sơ đồ:

1. tạo `map item`
2. hiển thị trên canvas
3. mở panel cấu hình bên phải
4. cho user nhập thông tin
5. nếu item là storage module thì cho phép sinh location tree

## 5.3. Resize item

Khi resize item:

- cập nhật `x`, `y`, `width`, `height`
- nếu item là rack, có thể cập nhật số khoang theo chiều dài
- không tự ý thay đổi số tầng bằng resize dọc, trừ khi user xác nhận

### Rule chuẩn

```txt
Kéo ngang rack = tăng/giảm số khoang
Số tầng = chỉnh trong panel
Số pallet mỗi tầng = chỉnh trong panel
```

## 5.4. Move item

Khi kéo item sang vị trí khác:

- chỉ đổi tọa độ hiển thị
- không đổi parent location trừ khi user chọn “Cập nhật cây vị trí”
- sau khi move, phải lưu `x`, `y`

## 5.5. Rotate item

Cho phép rotate item nếu cần.

Dùng field:

```txt
rotation
```

Ví dụ:

- 0
- 90
- 180
- 270

---

# 6. Logic kéo dài rack/kệ

## 6.1. Với rack pallet

Khi user kéo dài rack theo chiều ngang:

```txt
bay_count = round(width / bay_width_unit)
```

Ví dụ:

```txt
bay_width_unit = 60px
width = 300px
bay_count = 5
```

Nếu kéo rộng thành 420px:

```txt
bay_count = 7
```

Hệ thống tự tính:

```txt
total_pallet_capacity = bay_count × level_count × positions_per_level
```

## 6.2. Với kệ đơn giản

Khi user kéo dài kệ:

```txt
bin_count_per_level = round(width / bin_width_unit)
```

Ví dụ:

```txt
level_count = 3
bin_count_per_level = 5
total_bins = 15
```

## 6.3. Không tự thay đổi tầng khi kéo dọc

Số tầng nên chỉnh bằng panel:

```txt
Số tầng: [ - ] 3 [ + ]
```

Vì tầng là logic kho, không nên phụ thuộc hoàn toàn vào chiều cao pixel.

---

# 7. Logic cấu hình sức chứa

## 7.1. Pallet rack

Công thức:

```txt
total_pallet_capacity = bay_count × level_count × positions_per_level
```

Ví dụ:

```txt
bay_count = 5
level_count = 4
positions_per_level = 2
total_pallet_capacity = 40
```

## 7.2. Simple shelf

Công thức:

```txt
total_bins = level_count × bin_count_per_level
```

Ví dụ:

```txt
level_count = 3
bin_count_per_level = 5
total_bins = 15
```

## 7.3. Floor pallet area

Công thức:

```txt
total_pallet_positions = row_count × column_count
```

Ví dụ:

```txt
row_count = 4
column_count = 5
total_pallet_positions = 20
```

---

# 8. Logic nhân bản khu vực/kệ/rack

## 8.1. Duplicate một item

Khi duplicate:

- copy item hiện tại
- tăng tọa độ x/y một khoảng nhỏ
- sinh code mới
- copy cấu hình sức chứa
- copy style/màu/shape
- không dùng trùng code cũ

Ví dụ:

```txt
RACK-A01 → RACK-A02
```

## 8.2. Duplicate nhiều item theo hàng/cột

Cho phép user chọn:

```txt
Số bản sao: 5
Hướng: ngang / dọc
Khoảng cách: 20px
Prefix: RACK-A
Bắt đầu từ: 02
```

Hệ thống sinh:

```txt
RACK-A02
RACK-A03
RACK-A04
RACK-A05
RACK-A06
```

## 8.3. Duplicate có sinh location tree

Khi duplicate storage module, hỏi user:

```txt
Bạn có muốn sinh cây vị trí cho các bản sao không?
```

Nếu có:

- tạo location cha mới cho từng rack/kệ
- sinh level/slot/bin tương ứng
- tự tính `path`
- tránh trùng code trong cùng kho

Nếu không:

- chỉ duplicate sơ đồ, chưa tạo location

---

# 9. Logic sinh cây vị trí từ sơ đồ

## 9.1. User chọn item và bấm “Sinh vị trí”

Nếu item là `simple_shelf`, sinh:

```txt
SHELF-A
├── LEVEL-01
│   ├── BIN-01
│   ├── BIN-02
```

Nếu item là `pallet_rack`, sinh:

```txt
RACK-A01
├── LEVEL-01
│   ├── SLOT-01
│   └── SLOT-02
├── LEVEL-02
│   ├── SLOT-01
│   └── SLOT-02
```

Nếu item là `floor_pallet_area`, sinh:

```txt
PALLET-AREA-A
├── POS-01
├── POS-02
├── POS-03
```

## 9.2. Code generation

Cần sinh code rõ ràng.

Ví dụ pallet rack:

```txt
RACK-A01
RACK-A01-L01
RACK-A01-L01-S01
RACK-A01-L01-S02
```

Ví dụ simple shelf:

```txt
SHELF-A
SHELF-A-L01
SHELF-A-L01-B01
```

## 9.3. Chống trùng code

Trước khi tạo location:

- kiểm tra `warehouse_id + code`
- nếu trùng thì tăng số thứ tự
- hoặc báo user đổi prefix

## 9.4. Map item link với location

Sau khi sinh location:

- map item cha link với location cha
- nếu có slot/bin con, có thể lưu trong `meta_json`
- nếu muốn chi tiết hơn thì tạo map item con sau

---

# 10. Sync giữa sơ đồ và cây vị trí

## 10.1. Tree → Map

Khi click location trong tree:

- tìm map item có `location_id`
- highlight map item
- scroll canvas tới item
- mở panel chi tiết item

Nếu không có map item:

```txt
Vị trí này chưa được gắn trên sơ đồ
```

## 10.2. Map → Tree

Khi click map item:

- nếu có `location_id`, focus node trong tree
- mở các node cha nếu đang collapsed
- hiển thị chi tiết location

Nếu chưa link location:

```txt
Vùng này chưa gắn vị trí
```

---

# 11. DB đề xuất cho map item

Có thể mở rộng `inv_warehouse_map_items` với các field sau.

## 11.1. Field cơ bản

```txt
id
warehouse_map_id
location_id
parent_map_item_id
item_type
label
shape_type
x
y
width
height
rotation
color
z_index
is_clickable
meta_json
created_at
updated_at
```

## 11.2. Field cấu hình storage module

Có thể thêm trực tiếp hoặc lưu trong `meta_json`:

```txt
item_role
storage_mode
generator_type
is_stockable
capacity_mode
bay_count
level_count
positions_per_level
bin_count_per_level
row_count
column_count
max_pallets
max_qty
bay_width_unit
bin_width_unit
```

## 11.3. Enum gợi ý

### `item_type`

```txt
zone
rack
shelf
aisle
receiving_area
qc_area
staging_area
dispatch_area
damaged_area
pallet_area
bin_area
label
```

### `shape_type`

```txt
rect
polygon
line
image
label
```

### `storage_mode`

```txt
simple
advanced
none
```

### `generator_type`

```txt
none
simple_shelf_generator
pallet_rack_generator
floor_pallet_generator
```

### `capacity_mode`

```txt
none
pallet
qty
sku
bin
```

---

# 12. UI/UX chuẩn cho màn sơ đồ

## 12.1. Layout đề xuất

```txt
Header
├── Tên kho
├── Chọn map
├── Nút save
├── Toggle Edit/View

Main
├── Left sidebar: Tree location
├── Center: Canvas sơ đồ
└── Right sidebar: Properties panel
```

## 12.2. Left sidebar — Location tree

Hiển thị:

- cây vị trí
- icon theo type
- badge active/inactive
- trạng thái đã map hay chưa
- search location

Action:

- click location highlight map
- right click thêm con/sửa/xóa
- drag location vào map để tạo map item

## 12.3. Center — Canvas

Cần có:

- zoom in/out
- pan canvas
- grid nền
- snap to grid
- drag/drop item
- resize handle
- rotate handle
- chọn nhiều item
- duplicate
- delete
- align left/right/top/bottom nếu có thể

## 12.4. Right sidebar — Properties panel

Khi chọn item, hiển thị:

### Thông tin chung

- label
- code prefix
- item type
- shape type
- color
- linked location

### Vị trí hiển thị

- x
- y
- width
- height
- rotation

### Nếu là simple shelf

- số tầng
- số ô mỗi tầng
- sức chứa tối đa
- nút sinh vị trí

### Nếu là pallet rack

- số khoang
- số tầng
- số pallet mỗi tầng
- tổng sức chứa pallet
- nút sinh vị trí

### Nếu là aisle

- hướng
- độ rộng
- màu
- label

---

# 13. UX cho người không rành kho

## 13.1. Simple mode

Dành cho kho nhỏ.

Ẩn các phần:

- bay count
- positions per level
- pallet rack nâng cao
- aisle nâng cao
- require pallet

Chỉ hiển thị:

- khu vực
- kệ
- tầng
- ô
- sức chứa đơn giản

## 13.2. Advanced mode

Dành cho kho lớn.

Hiển thị:

- pallet rack
- floor pallet area
- aisle
- bay count
- level count
- positions per level
- duplicate rack
- capacity preview

## 13.3. Tooltip / mô tả dễ hiểu

Không dùng từ quá kỹ thuật nếu không cần.

Ví dụ:

Thay vì:

```txt
bay_count
```

Hiển thị:

```txt
Số khoang theo chiều dài
```

Thay vì:

```txt
positions_per_level
```

Hiển thị:

```txt
Mỗi tầng chứa bao nhiêu pallet
```

Thay vì:

```txt
generator_type
```

Hiển thị:

```txt
Kiểu sinh vị trí
```

---

# 14. Validation cần có

## 14.1. Map item

- `warehouse_map_id` tồn tại
- `location_id` nếu có phải thuộc cùng warehouse
- `x`, `y`, `width`, `height` hợp lệ
- `item_type` hợp lệ
- `shape_type` hợp lệ
- không cho item stockable là aisle/path

## 14.2. Sinh location

- code prefix required
- không trùng code trong cùng warehouse
- parent location nếu có phải cùng warehouse
- `level_count >= 1`
- `bay_count >= 1` nếu pallet rack
- `positions_per_level >= 1` nếu pallet rack
- `bin_count_per_level >= 1` nếu simple shelf

## 14.3. Duplicate

- không tạo code trùng
- không vượt ngoài canvas nếu có giới hạn
- nếu duplicate có sinh location, phải rollback nếu một location bị lỗi

---

# 15. Các thao tác chính cần API/service

## 15.1. Map item service

Cần có các method:

```php
createMapItem(array $data)
updateMapItem(MapItem $item, array $data)
moveMapItem(MapItem $item, int $x, int $y)
resizeMapItem(MapItem $item, int $width, int $height)
duplicateMapItem(MapItem $item, array $options)
deleteMapItem(MapItem $item)
linkLocation(MapItem $item, Location $location)
unlinkLocation(MapItem $item)
```

## 15.2. Location generator service

Cần có các method:

```php
generateSimpleShelfLocations(MapItem $item, array $config)
generatePalletRackLocations(MapItem $item, array $config)
generateFloorPalletLocations(MapItem $item, array $config)
```

## 15.3. Sync service

Cần có:

```php
getMapItemByLocation(Location $location)
getLocationByMapItem(MapItem $item)
```

---

# 16. Acceptance Criteria

Agent/dev phải đảm bảo:

## Kéo thả

- kéo item vào canvas được
- di chuyển item được
- resize item được
- save tọa độ được
- reload vẫn giữ đúng vị trí

## Nhân bản

- duplicate một item được
- duplicate nhiều item theo hàng/cột được
- sinh code mới không trùng
- có thể chọn có/không sinh location cho bản sao

## Sinh location

- simple shelf sinh đúng level/bin
- pallet rack sinh đúng level/slot
- floor pallet area sinh đúng pallet position
- tự tính path/level
- không trùng code

## Đồng bộ map/tree

- click tree highlight map
- click map focus tree
- location chưa map có thông báo rõ
- map item chưa link location có thông báo rõ

## Kho nhỏ

- có simple mode
- không bắt pallet
- UI không hiển thị field nâng cao gây rối

## Kho lớn

- có advanced mode
- có pallet rack
- có aisle/path
- có capacity pallet
- có duplicate rack

---

# 17. Những điều không được làm sai

- Không dùng sơ đồ làm dữ liệu chuẩn thay cho location tree
- Không cho aisle/path chứa hàng
- Không bắt kho nhỏ phải dùng pallet
- Không bắt user nhập `level` và `path`
- Không để duplicate sinh trùng code
- Không để resize chỉ thay đổi hình mà không cập nhật cấu hình rack nếu item là rack
- Không cho map item link location thuộc kho khác
- Không tạo location khi user chưa xác nhận
- Không cho pallet nằm trên aisle/path

---

# 18. Kết luận

Logic chuẩn cho phần sơ đồ kho kéo thả là:

```txt
Template kho
→ Cây vị trí nền
→ Sơ đồ trực quan
→ Kéo thả item
→ Cấu hình module lưu trữ
→ Sinh location tree
→ Link map item với location
→ Sync tree ↔ map
```

Kho nhỏ dùng:

```txt
Area / Shelf / Level / Bin
Không bắt pallet
```

Kho lớn dùng:

```txt
Zone / Aisle / Pallet Rack / Level / Pallet Slot
Có pallet capacity
```

Cách làm này giúp UI dễ dùng cho người mới, nhưng vẫn đủ mạnh để mở rộng cho kho lớn.
