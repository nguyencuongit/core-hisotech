# Prompt Cho Agent: Redesign Màn Sơ Đồ Kho Kéo Thả Dễ Dùng, Đẹp Và Chuẩn Logic

## 1. Bối cảnh

Hiện tại module kho đã có tab **Sơ đồ kho** với các thành phần:

- Template sơ đồ kho
- Nút tạo sơ đồ trống
- Toolbar module:
  - Khu vực
  - Đường đi
  - Kệ đơn giản
  - Rack pallet
  - Khu pallet sàn
  - Text / Labclaudeel
- Snap grid
- Vẽ liên tiếp
- Duplicate
- Delete
- Zoom
- Lưu layout
- Canvas sơ đồ
- Cây vị trí
- Panel cấu hình vùng đang chọn
- Chú giải
- Mẫu sơ đồ có sẵn

Về logic tổng thể, hướng hiện tại là đúng:

```txt
inv_warehouse_locations = dữ liệu vị trí thật
inv_warehouse_maps = sơ đồ kho
inv_warehouse_map_items = vùng vẽ trên sơ đồ
```

Nhưng UI hiện tại đang bị rối vì:

- quá nhiều chức năng trên một màn hình
- user không biết bắt đầu từ đâu
- chưa tách rõ mode xem và mode chỉnh sửa
- drag/drop chưa hoạt động thật hoặc chưa rõ ràng
- một số nút hiển thị nhưng chưa dùng được tốt
- panel phải chưa hướng dẫn đủ rõ khi chưa chọn item
- cây vị trí bên phải đang chiếm nhiều chỗ
- chú giải và mẫu sơ đồ hiển thị quá nhiều gây nặng giao diện

Mục tiêu của task này là **redesign màn Sơ đồ kho** để dễ sử dụng hơn, trực quan hơn, đẹp hơn và đúng logic vận hành kho.

---

## 2. Nguyên tắc quan trọng

Agent phải tuân thủ các nguyên tắc sau:

```txt
Cây vị trí = dữ liệu chuẩn
Sơ đồ kho = lớp hiển thị trực quan
Map item = vùng vẽ trên sơ đồ, có thể gắn hoặc không gắn location
Tồn kho không nằm ở map item
Tồn kho vẫn nằm theo warehouse_location_id / pallet_id / batch / product
```

Không được để sơ đồ thay thế cây vị trí.

Không được để map item tự tạo nghiệp vụ tồn kho.

Map chỉ dùng để:

- hiển thị bố cục kho
- chọn nhanh location
- hỗ trợ putaway
- hỗ trợ move pallet
- hỗ trợ xem tồn theo vị trí sau này
- hỗ trợ kiểm kê sau này

---

## 3. Mục tiêu UX mới

Màn Sơ đồ kho phải giúp người dùng trả lời được 3 câu hỏi rất nhanh:

```txt
1. Tôi bắt đầu từ đâu?
2. Tôi đang ở chế độ xem hay chỉnh sửa?
3. Tôi cần làm gì tiếp theo?
```

Giao diện phải phù hợp cho người không rành kho.

Không bắt user hiểu ngay các khái niệm kỹ thuật như:

- map item
- location_id
- x/y/width/height
- generator_type
- bay_count
- positions_per_level

Các thông tin kỹ thuật chỉ nên nằm trong panel nâng cao hoặc được hiển thị bằng nhãn tiếng Việt dễ hiểu.

---

## 4. Chia màn Sơ đồ kho thành 3 trạng thái rõ ràng

Màn này không nên vừa là màn khởi tạo, vừa là editor, vừa là preview cùng một lúc.

Cần tách thành 3 trạng thái:

---

# Trạng thái 1: Khởi tạo sơ đồ

Dùng khi kho chưa có sơ đồ hoặc user muốn tạo sơ đồ mới.

## UI cần có

Chỉ hiển thị phần đơn giản:

```txt
Sơ đồ kho
Tạo sơ đồ trực quan để dễ sắp xếp khu vực, kệ, rack và vị trí trong kho.

[ Dùng mẫu có sẵn ]
[ Tạo sơ đồ trống ]
```

## Nếu chọn “Dùng mẫu có sẵn”

Hiển thị các card template:

### Template 1: Kho cơ bản

Mô tả:

```txt
Phù hợp kho nhỏ, ít khu vực, không cần pallet phức tạp.
```

Gồm:

```txt
RECEIVING
STORAGE
DAMAGED
REJECTED
```

### Template 2: Kho có QC

Mô tả:

```txt
Phù hợp kho cần kiểm tra chất lượng trước khi lưu trữ.
```

Gồm:

```txt
RECEIVING
QC_HOLD
WAITING_PUTAWAY
STORAGE
DAMAGED
REJECTED
```

### Template 3: Kho nhiều rack

Mô tả:

```txt
Phù hợp kho lớn có nhiều dãy kệ/rack, có đường đi và khu lưu trữ lớn.
```

Gồm:

```txt
RECEIVING
WAITING_PUTAWAY
QC_HOLD
ZONE-A
RACK-01
RACK-02
RACK-03
DAMAGED
REJECTED
```

Mỗi card cần có:

- tên mẫu
- mô tả ngắn
- preview nhỏ
- nút `Dùng mẫu này`

## Nếu chọn “Tạo sơ đồ trống”

Hiển thị form ngắn:

- Tên sơ đồ
- Kích thước canvas
- Grid mặc định
- Nút `Tạo sơ đồ`

## Không hiển thị trong trạng thái khởi tạo

Không hiển thị các nút:

- Duplicate
- Delete
- Zoom
- Snap grid
- Vẽ liên tiếp
- Cây vị trí dài
- Chú giải
- Panel cấu hình chi tiết

Chỉ hiển thị những gì cần để tạo sơ đồ ban đầu.

---

# Trạng thái 2: Chỉnh sửa sơ đồ

Dùng khi admin hoặc người setup kho muốn kéo thả, sắp xếp, gắn location.

## Layout mới đề xuất

```txt
Header
├── Tên sơ đồ
├── Badge: Chế độ chỉnh sửa
├── [Xem sơ đồ]
├── [Lưu layout]

Main layout
├── Left toolbar
├── Center canvas
└── Right properties panel
```

---

## 4.1. Header

Header nên có:

- Tên sơ đồ
- Trạng thái lưu:
  - Đã lưu
  - Có thay đổi chưa lưu
- Nút:
  - `Xem sơ đồ`
  - `Lưu layout`
  - `Tùy chọn`

Ví dụ:

```txt
Sơ đồ kho cơ bản        [Có thay đổi chưa lưu]
[ Xem sơ đồ ] [ Lưu layout ] [ ... ]
```

---

## 4.2. Left toolbar

Toolbar trái chỉ nên hiển thị các công cụ chính.

### Nhóm “Thêm vùng”

```txt
+ Khu vực
+ Lối đi
+ Kệ đơn giản
+ Rack pallet
+ Khu pallet sàn
+ Text / Label
```

### Nhóm “Thao tác”

```txt
Duplicate
Delete
```

### Nhóm “Hiển thị”

```txt
Zoom -
Zoom +
Fit screen
```

### Nhóm “Nâng cao”

Đưa vào collapse hoặc menu phụ:

```txt
Snap grid
Grid size
Vẽ liên tiếp
Rotate
Align
```

Không show tất cả option nâng cao ra ngoài ngay từ đầu.

---

## 4.3. Center canvas

Canvas phải là vùng chính, rộng và sạch.

Cần hỗ trợ:

- grid nền
- snap grid
- click để chọn item
- kéo item để đổi vị trí
- resize item
- nếu item là rack thì resize ngang có thể cập nhật số khoang
- scroll/pan canvas
- zoom
- highlight item đang chọn
- hiển thị label item rõ ràng

## Drag/drop bắt buộc phải hoạt động thật

Ít nhất phải có:

```txt
1. Chọn module từ toolbar
2. Click hoặc kéo vào canvas để tạo item
3. Click item để chọn
4. Kéo item để đổi vị trí
5. Resize item
6. Lưu x/y/width/height
7. Reload trang vẫn giữ đúng layout
```

Nếu tính năng nào chưa hoạt động, phải disable hoặc gắn nhãn `Sắp có`.

Không được hiển thị chức năng như đã chạy nếu thực tế chưa dùng được.

---

## 4.4. Right properties panel

Panel phải thay đổi theo trạng thái.

### Khi chưa chọn item

Không chỉ ghi “Chưa có item nào được chọn”.

Phải hiển thị hướng dẫn 3 bước:

```txt
Bắt đầu chỉnh sơ đồ

1. Chọn một module bên trái
2. Bấm hoặc kéo vào canvas để thêm vùng
3. Bấm vào vùng để chỉnh thông tin và gắn location
```

Có thể thêm nút nhanh:

```txt
+ Thêm khu vực nhận hàng
+ Thêm lối đi
+ Thêm rack
```

### Khi chọn item

Hiển thị form cấu hình:

#### Thông tin chung

- Tên vùng
- Loại vùng
- Màu sắc
- Shape
- Trạng thái

#### Gắn location

- Select location
- Nút `Gắn location`
- Nút `Bỏ gắn`
- Cảnh báo nếu chưa gắn location

#### Kích thước

- X
- Y
- Width
- Height
- Rotation

#### Nếu là kệ đơn giản

Hiển thị:

- Số tầng
- Số ô mỗi tầng
- Tổng số ô
- Nút `Sinh vị trí`

Nhãn dễ hiểu:

```txt
Số tầng
Mỗi tầng có bao nhiêu ô
Tổng số vị trí tạo ra
```

#### Nếu là rack pallet

Hiển thị:

- Số khoang theo chiều dài
- Số tầng
- Mỗi tầng chứa bao nhiêu pallet
- Tổng sức chứa pallet
- Nút `Sinh vị trí`

Công thức:

```txt
Tổng sức chứa = số khoang × số tầng × số pallet mỗi tầng
```

#### Nếu là lối đi

Hiển thị:

- Hướng lối đi
- Độ rộng
- Màu
- Label

Cảnh báo:

```txt
Lối đi chỉ dùng để hiển thị, không chứa hàng và không gắn pallet.
```

---

# Trạng thái 3: Xem sơ đồ

Dùng cho nhân viên hoặc user chỉ muốn xem kho.

## UI cần có

- Canvas sơ đồ lớn
- Cây vị trí dạng gọn
- Search location
- Click item để xem chi tiết
- Click location để highlight map
- Nút `Chỉnh sửa sơ đồ` nếu user có quyền

## Không hiển thị

- Toolbar kéo thả
- Duplicate
- Delete
- Grid setting
- Save layout
- Properties kỹ thuật

## Khi click vào vùng

Hiển thị popup/card:

```txt
Tên vùng
Loại vùng
Location gắn với vùng
Số pallet tại vị trí này
Số sản phẩm tại vị trí này
Trạng thái
```

Nếu chưa có dữ liệu tồn kho thì chỉ hiển thị location information.

---

# 5. Luồng sử dụng chuẩn

## Luồng A: User mới tạo sơ đồ bằng mẫu

```txt
Vào tab Sơ đồ kho
→ thấy màn khởi tạo
→ chọn Dùng mẫu có sẵn
→ chọn Kho cơ bản / Kho có QC / Kho nhiều rack
→ hệ thống tạo map + map items
→ chuyển sang chế độ Xem
→ user bấm Chỉnh sửa nếu muốn thay đổi
```

---

## Luồng B: User chỉnh sơ đồ

```txt
Bấm Chỉnh sửa sơ đồ
→ chọn module bên trái
→ kéo vào canvas
→ click item
→ chỉnh tên/màu/kích thước
→ gắn location
→ lưu layout
```

---

## Luồng C: User tạo rack có sức chứa

```txt
Chọn Rack pallet
→ kéo vào canvas
→ chọn item
→ nhập số khoang
→ nhập số tầng
→ nhập số pallet mỗi tầng
→ xem tổng sức chứa
→ bấm Sinh vị trí
→ hệ thống tạo location tree
→ map item gắn với location cha
```

---

## Luồng D: User dùng kho nhỏ

```txt
Chọn mode kho nhỏ
→ UI chỉ hiển thị Khu vực / Kệ đơn giản / Label
→ không hiện Rack pallet nâng cao nếu kho không dùng pallet
→ user tạo kệ với số tầng và số ô
→ sinh location đơn giản
```

---

# 6. Rule hiển thị theo kho nhỏ / kho lớn

## Nếu kho nhỏ

Điều kiện:

```txt
warehouse_settings.warehouse_mode = simple
hoặc use_pallet = false
```

UI nên:

- ẩn Rack pallet nếu không cần
- ẩn Khu pallet sàn nếu không dùng pallet
- ẩn field “mỗi tầng chứa bao nhiêu pallet”
- ưu tiên Khu vực, Kệ đơn giản, Label
- dùng ngôn ngữ đơn giản

Hiển thị:

```txt
Khu vực
Kệ đơn giản
Lối đi
Text / Label
```

## Nếu kho lớn

Điều kiện:

```txt
warehouse_settings.warehouse_mode = advanced
hoặc use_pallet = true
```

UI có thể hiển thị:

```txt
Khu vực
Lối đi
Kệ đơn giản
Rack pallet
Khu pallet sàn
Text / Label
```

Có thể cấu hình:

- số khoang
- số tầng
- pallet mỗi tầng
- sức chứa pallet
- duplicate rack

---

# 7. Logic kéo thả và resize

## 7.1. Tạo item

Khi user chọn module rồi click canvas:

```txt
create map item
x = clickX
y = clickY
width = defaultWidth
height = defaultHeight
item_type = selectedTool
```

Sau đó chọn item vừa tạo và mở panel phải.

## 7.2. Move item

Khi user kéo item:

```txt
update x/y trên UI
mark dirty = true
```

Khi bấm lưu:

```txt
save x/y vào inv_warehouse_map_items
```

## 7.3. Resize item

Khi user resize:

```txt
update width/height trên UI
mark dirty = true
```

Nếu item là rack pallet và resize ngang:

```txt
bay_count = round(width / bay_width_unit)
```

Không tự thay đổi `level_count` khi kéo dọc.

Số tầng chỉnh trong panel.

## 7.4. Save layout

Khi bấm save:

```txt
validate all changed items
save x/y/width/height/rotation/color/label/location_id/meta_json
```

Sau khi save:

```txt
dirty = false
show toast: Đã lưu layout
```

---

# 8. Logic duplicate

## Duplicate một item

Khi bấm duplicate:

```txt
copy selected item
new x = old x + 24
new y = old y + 24
new code/label = auto increment
copy color/shape/size/config
location_id = null hoặc hỏi user
```

Không tự link cùng location cũ để tránh 2 vùng map trỏ cùng 1 location nếu không có chủ ý.

Hiển thị thông báo:

```txt
Đã nhân bản vùng. Hãy gắn location mới nếu cần.
```

## Duplicate nhiều item

Có thể làm sau, nhưng nếu làm thì UI nên có modal:

```txt
Số bản sao
Hướng: ngang / dọc
Khoảng cách
Prefix code
Bắt đầu từ số
Có sinh location không?
```

---

# 9. Logic gắn location

## Gắn map item với location

Rule:

```txt
location phải thuộc cùng warehouse với map
location phải active
một location nên chỉ gắn với một map item chính, trừ khi cho phép nhiều view
```

Nếu location đã gắn với item khác, hỏi:

```txt
Location này đã được gắn với vùng khác. Bạn muốn chuyển sang vùng này không?
```

## Map item chưa gắn location

Vẫn được tồn tại như item hình học.

Ví dụ:

- đường đi
- label
- dock
- mũi tên hướng di chuyển

Nhưng không được dùng để chọn vị trí nghiệp vụ.

---

# 10. Sync Tree ↔ Map

## Click location trong tree

Hành động:

```txt
find map item by location_id
highlight item
scroll canvas tới item
open properties panel
```

Nếu không có map item:

```txt
Vị trí này chưa được gắn trên sơ đồ.
```

## Click map item

Nếu có `location_id`:

```txt
focus location trong tree
mở node cha
show location info
```

Nếu không có location:

```txt
Vùng này chưa gắn vị trí.
```

---

# 11. Thu gọn các phần gây rối

## Chú giải

Không hiển thị lớn ngay dưới canvas.

Đưa vào accordion/collapse:

```txt
[ Chú giải ]
```

Mặc định đóng.

## Mẫu sơ đồ có sẵn

Không hiển thị dưới canvas sau khi đã có map.

Đưa vào nút:

```txt
Đổi mẫu / Thêm từ mẫu
```

hoặc chỉ hiện ở màn khởi tạo.

## Thống kê tổng vùng

Có thể hiển thị gọn trong header:

```txt
Tổng vùng: 10 | Đã gắn: 2 | Chưa gắn: 8
```

Không cần card lớn chiếm diện tích.

---

# 12. Thiết kế giao diện đẹp hơn

## Visual style

Giữ phong cách hiện tại:

- nền trắng
- bo góc lớn
- shadow nhẹ
- màu chính xanh/tím
- badge mềm
- spacing rộng

## Màu gợi ý theo loại vùng

```txt
receiving = xanh dương nhạt
waiting_putaway = tím nhạt
qc_hold = vàng/tím
storage/rack = xanh lá nhạt
aisle = xám
damaged = đỏ nhạt
rejected = hồng/đỏ nhạt
label = trắng/xám
```

## Icon gợi ý

- Receiving: inbox/truck
- QC: shield/check
- Waiting: clock
- Rack: grid/shelves
- Aisle: arrows
- Damaged: alert triangle
- Rejected: x-circle
- Label: text

---

# 13. Validation bắt buộc

## Map item

- `warehouse_map_id` required
- `item_type` hợp lệ
- `shape_type` hợp lệ
- `x/y/width/height` hợp lệ
- `location_id` nếu có phải thuộc cùng warehouse
- aisle/path không được stockable
- item chưa link location không được dùng để post tồn

## Location generation

- code prefix required
- không trùng code trong cùng warehouse
- level_count >= 1
- bay_count >= 1 nếu rack
- positions_per_level >= 1 nếu rack pallet
- bin_count_per_level >= 1 nếu kệ đơn giản

## Duplicate

- không copy location_id mặc định
- không tạo code trùng
- nếu duplicate có sinh location thì phải dùng DB transaction

---

# 14. Acceptance criteria

Sau khi làm xong, cần đạt:

## UX

- User vào tab sơ đồ biết bắt đầu từ đâu
- Có màn khởi tạo rõ ràng
- Có mode xem và mode chỉnh sửa
- Không còn quá nhiều nút gây rối
- Các tính năng chưa chạy phải ẩn/disable

## Editor

- Tạo item được
- Chọn item được
- Kéo item đổi vị trí được
- Resize item được
- Lưu layout được
- Reload vẫn giữ layout

## Location link

- Gắn item với location được
- Bỏ gắn được
- Không gắn location khác kho
- Tree click highlight map
- Map click focus tree

## Kho nhỏ

- UI đơn giản
- Không bắt pallet
- Không hiện field pallet nâng cao

## Kho lớn

- Có rack pallet
- Có cấu hình số khoang/tầng/pallet mỗi tầng
- Tính tổng sức chứa pallet
- Có thể duplicate rack

---

# 15. Những điều không được làm sai

- Không để map là dữ liệu chuẩn thay location tree
- Không để map item tự tạo tồn kho
- Không hiển thị chức năng chưa dùng được như đã hoàn thiện
- Không bắt user nhập thông số kỹ thuật khi chưa cần
- Không copy `location_id` khi duplicate nếu không có xác nhận
- Không cho item khác warehouse link location
- Không cho aisle/path chứa hàng/pallet
- Không làm màn editor quá nhiều nút như hiện tại

---

# 16. Thứ tự triển khai khuyên dùng

Agent nên làm theo thứ tự:

```txt
1. Tách state: empty/init, view, edit
2. Làm drag/drop thật sự hoạt động
3. Làm select item + properties panel
4. Làm save x/y/width/height
5. Làm link/unlink location
6. Làm tree → map highlight
7. Làm map → tree focus
8. Làm duplicate đơn giản
9. Thu gọn legend/template/stats vào collapse/header
10. Thêm simple mode / advanced mode theo warehouse settings
11. Thêm cấu hình rack/kệ + capacity preview
12. Sau cùng mới làm duplicate nhiều item và generator location nâng cao
```

---

# 17. Báo cáo cuối cùng Agent cần trả về

Sau khi làm xong, báo cáo theo format:

```txt
1. Đã sửa UI/UX gì
2. Đã thêm/sửa file nào
3. Drag/drop đã hoạt động tới mức nào
4. Những tính năng nào đã ẩn/disable vì chưa hoàn thiện
5. Tree ↔ Map sync đã làm tới đâu
6. Validation nào đã thêm
7. Còn phần nào cần làm tiếp
```

---

# 18. Kết luận

Mục tiêu cuối cùng của redesign này:

```txt
Người dùng vào màn Sơ đồ kho phải biết:
1. chọn mẫu hoặc tạo trống
2. xem sơ đồ hoặc chỉnh sửa sơ đồ
3. kéo/thả vùng dễ dàng
4. gắn vùng với location
5. lưu layout
```

Sơ đồ kho phải trở thành một **công cụ setup trực quan**, không phải một màn chứa quá nhiều nút gây rối.

Logic đúng:

```txt
Location tree = dữ liệu thật
Map = giao diện trực quan
Stock = ledger/balance riêng
```

UI đúng:

```txt
Init mode → View mode → Edit mode
```

Triển khai đúng thứ tự sẽ giúp màn Sơ đồ kho dễ dùng hơn, đẹp hơn và không gây rối cho user.
