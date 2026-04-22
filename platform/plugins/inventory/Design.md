# Design.md

## Mục tiêu
Tài liệu này định nghĩa chuẩn giao diện cần tuân theo khi AI Agent thiết kế màn hình, component, form hoặc dashboard cho hệ thống.  
Phong cách mong muốn dựa trên mẫu giao diện đã cung cấp: **tối giản, cao cấp, mềm mại, hiện đại, nhiều khoảng trắng, bo góc lớn, ưu tiên cảm giác sạch và dễ dùng**.

Agent phải dùng tài liệu này như **nguồn chân lý thiết kế** khi sinh UI, viết prompt thiết kế, tạo component React/Laravel/Blade/Flutter, hoặc đề xuất cải tiến giao diện.

---

## 1. Tinh thần giao diện

### Từ khóa phong cách
- Minimal
- Soft UI
- Calm
- Premium
- Clean
- Spacious
- Rounded
- Gentle contrast
- Modern productivity / wellness app feel

### Cảm giác tổng thể
- Giao diện phải sáng, thoáng, ít nhiễu thị giác
- Không dùng màu quá gắt hoặc quá nhiều màu cùng lúc
- Thành phần nổi bật bằng **màu tím/xanh tím chủ đạo**
- Thành phần phụ dùng nền trắng/ngà rất nhạt
- Border mảnh, tinh tế
- Đổ bóng mềm, nhẹ, không nặng
- Ưu tiên sự “đẹp tinh gọn” hơn là “nhiều chi tiết”

---

## 2. Nguyên tắc bố cục

### 2.1 Khung tổng thể
- Toàn màn hình dùng nền sáng trung tính
- Nội dung đặt trong các card hoặc block riêng biệt
- Mỗi nhóm component nằm trong một card nền trắng
- Card cách nhau rõ ràng bằng khoảng trắng, không cần viền đậm

### 2.2 Grid
- Dùng grid đều, thẳng hàng, cân đối
- Khoảng cách giữa các block phải đồng nhất
- Không để component dính sát nhau
- Ưu tiên layout 2 cột hoặc 3 cột rõ ràng khi là dashboard/design system

### 2.3 Khoảng trắng
- Padding rộng
- Margin giữa các section phải thoáng
- Ưu tiên không gian rỗng để làm nổi bật component
- Không nhồi quá nhiều nội dung trong một card

### 2.4 Bo góc
- Card: bo lớn
- Button/input: bo dạng pill hoặc rounded-xl
- Switch, badge, selector: bo mềm và đồng nhất

---

## 3. Bảng màu định hướng

> Không cần bám tuyệt đối từng mã màu, nhưng phải giữ đúng tinh thần màu sắc dưới đây.

### 3.1 Màu chủ đạo
- Primary: tím xanh / indigo
- Dùng cho:
  - button chính
  - radio được chọn
  - checkbox được chọn
  - trạng thái active
  - tab active
  - spinner
  - border focus
  - icon nhấn mạnh

### 3.2 Màu nền
- Nền ngoài cùng: xám kem hoặc trắng ngà cực nhạt
- Card: trắng hoặc trắng hơi ấm
- Input disabled: xám rất nhạt

### 3.3 Màu chữ
- Tiêu đề: đen mềm / charcoal
- Nội dung: xám đậm vừa phải
- Phụ đề / label: xám trung tính

### 3.4 Màu trạng thái
- Success: xanh lá dịu
- Info: xanh dương dịu
- Warning: vàng/cam nhẹ
- Error: đỏ hồng dịu

### 3.5 Nguyên tắc dùng màu
- Mỗi màn hình chỉ nên có 1 màu chủ đạo chính
- Màu trạng thái chỉ xuất hiện khi có ngữ cảnh rõ ràng
- Tránh dùng nền màu đặc diện rộng, ưu tiên nền sáng trung tính
- Border và surface phải nhẹ hơn text rất nhiều

---

## 4. Typography

### 4.1 Phong cách chữ
- Hiện đại, thanh lịch, dễ đọc
- Tiêu đề có thể dùng serif display hoặc sans cao cấp nếu là hero/title lớn
- Nội dung bên trong app nên dùng sans-serif sạch, rõ

### 4.2 Cấp bậc chữ
- Page title: lớn, nổi bật, gọn
- Section title: nhỏ hơn page title nhưng rõ ràng
- Label: nhỏ, nhẹ
- Button text: rõ, đậm vừa
- Helper text: nhỏ, dịu

### 4.3 Nguyên tắc
- Không dùng quá nhiều cỡ chữ
- Không lạm dụng in đậm
- Text phải thoáng, dễ quét
- Ưu tiên căn trái
- Chữ trong component phải gọn và súc tích

---

## 5. Chuẩn card

### Card nền
- Nền trắng hoặc trắng ấm
- Bo góc lớn
- Có padding thoáng
- Có thể có shadow rất nhẹ
- Không dùng border đậm

### Cấu trúc card
- Dòng đầu là tên nhóm component hoặc tiêu đề
- Nội dung bên dưới chia hàng/cột đều
- Không đặt quá nhiều thành phần khác loại chen chúc

### Card không được phép
- Shadow quá dày
- Viền đen rõ
- Nền gradient mạnh
- Góc vuông cứng

---

## 6. Chuẩn button

### 6.1 Button chính
- Nền primary
- Chữ trắng
- Bo tròn lớn
- Có thể có icon bên trái hoặc bên phải
- Kích thước vừa phải, không quá cao

### 6.2 Button phụ
- Nền trắng hoặc nền rất nhạt
- Border mảnh theo màu primary hoặc xám nhạt
- Chữ đậm vừa

### 6.3 Button trạng thái nguy hiểm
- Dùng nền đỏ dịu
- Chỉ dùng cho hành động như hủy, xóa, từ chối

### 6.4 Hành vi
- Hover: tăng tương phản nhẹ
- Active: tối hơn một chút
- Disabled: mờ, ít tương phản
- Không thêm hiệu ứng phức tạp

### 6.5 Quy tắc
- Button nên có chiều cao đồng nhất
- Padding ngang thoải mái
- Chữ in hoa nhẹ hoặc tracking rộng là được, nhưng không quá cứng
- Icon nếu có phải nhỏ gọn, cân đối với chữ

---

## 7. Chuẩn tag và badge

### Đặc điểm
- Nhỏ, gọn, bo pill
- Màu nền rất nhạt
- Màu chữ theo từng trạng thái
- Khoảng đệm ngang rõ nhưng không quá lớn

### Cách dùng
- Dùng cho trạng thái, phân loại, nhóm nội dung
- Không dùng badge quá sặc sỡ
- Không dùng badge quá to hơn text chính

### Tinh thần
- “Mềm” và “tinh tế”
- Có thể rất nhạt nhưng vẫn nhận diện được

---

## 8. Chuẩn input và form

### 8.1 Input
- Nền trắng
- Border mảnh xám nhạt
- Bo tròn lớn
- Khi focus: border primary rõ hơn
- Chiều cao input đồng đều

### 8.2 Label
- Gọn, nhỏ, dễ đọc
- Có thể đặt trong hoặc trên input tùy ngữ cảnh
- Không quá đậm

### 8.3 Placeholder
- Xám nhạt
- Dễ nhìn nhưng không lấn át nội dung thật

### 8.4 Input trạng thái
- Default: sạch, border nhạt
- Focus: border primary
- Error: border đỏ dịu
- Disabled: nền xám nhạt, giảm tương phản

### 8.5 Select / dropdown / payment input
- Cùng ngôn ngữ thiết kế với input thường
- Icon hoặc flag nằm gọn bên trong
- Không dùng style browser mặc định thô cứng

---

## 9. Chuẩn checkbox, radio, switch

### Checkbox
- Hình tròn hoặc vuông mềm tùy hệ thống, nhưng phải đồng nhất
- Checked dùng màu primary
- Unchecked viền xám nhạt
- Disabled giảm tương phản rõ

### Radio
- Khi chọn: vòng tròn primary với tâm rõ ràng
- Khi chưa chọn: viền xám nhạt
- Khoảng cách text và control đều nhau

### Switch
- Nhỏ gọn, mềm
- Bản bật dùng primary
- Bản tắt dùng xám nhạt
- Disabled phải thể hiện rõ nhưng vẫn đẹp

---

## 10. Chuẩn stepper và selector

### Stepper
- Nút trừ / cộng nhỏ gọn, cân đối
- Ô hiển thị số bo tròn
- Các biến thể màu chỉ dùng khi có ý nghĩa trạng thái
- Không dùng style nặng nề

### Selector
- Dạng pill card nhỏ
- Có selected / unselected rõ ràng
- Selected có viền hoặc nền nhấn nhẹ theo primary
- Dùng tốt cho payment method, choice chips, option cards

---

## 11. Chuẩn alerts / banner

### Nguyên tắc
- Dễ đọc
- Một dòng chính rõ ràng
- Icon trạng thái ở bên trái
- Nút đóng nhỏ, gọn ở bên phải
- Nền rất nhẹ, không chói

### Loại trạng thái
- Info
- Success
- Warning
- Error

### Cảm giác
- Cảnh báo nhưng vẫn thanh lịch
- Không dùng màu đậm toàn khối

---

## 12. Chuẩn icon và hình minh họa

### Icon
- Nét mảnh hoặc vừa
- Tối giản
- Bo góc mềm
- Không dùng icon quá chi tiết hoặc quá “techy”

### Hình minh họa / avatar / profile
- Dùng các khối đơn giản
- Viền nhẹ
- Button thêm ảnh nên nhỏ, gọn, bo tròn
- Ưu tiên cảm giác thân thiện

---

## 13. Chuẩn component container

Khi agent thiết kế một cụm component, phải đảm bảo:
- cùng chiều cao nếu đặt cùng hàng
- cùng ngôn ngữ bo góc
- cùng padding
- cùng nhịp điệu khoảng cách
- không có component nào lệch phong cách so với phần còn lại

---

## 14. Những điều agent phải làm

### Agent PHẢI
- Thiết kế theo hướng sáng, sạch, premium
- Dùng bo góc lớn và spacing rộng
- Ưu tiên card trắng trên nền sáng trung tính
- Dùng primary tím/xanh tím làm điểm nhấn
- Dùng border nhẹ thay vì khối nặng
- Dùng shadow rất mềm hoặc gần như không thấy
- Giữ UI đơn giản, dễ hiểu, sang
- Đồng bộ tất cả component trong cùng một màn hình

### Agent KHÔNG ĐƯỢC
- Dùng màu neon, gradient gắt, hoặc contrast quá mạnh
- Dùng quá nhiều màu chủ đạo trong một màn hình
- Dùng shadow đậm kiểu material nặng
- Dùng góc vuông cứng
- Dùng text quá nhỏ khó đọc
- Dùng layout chật, thiếu khoảng trắng
- Dùng component style khác nhau trên cùng màn hình
- Dùng icon quá to hoặc quá chi tiết
- Dùng table/grid nặng nề nếu có thể thay bằng card sạch hơn

---

## 15. Quy tắc responsive

### Desktop
- Tận dụng không gian ngang
- Grid card cân đối
- Không kéo component quá dài gây loãng

### Tablet
- Giảm số cột
- Giữ nguyên ngôn ngữ bo góc và spacing

### Mobile
- Chuyển về 1 cột hoặc 2 cột nhỏ hợp lý
- Button/input vẫn phải dễ chạm
- Không làm UI dày đặc
- Padding hai bên phải thoáng

---

## 16. Quy tắc nếu agent sinh code UI

Nếu AI Agent tạo code React, Blade, Flutter, HTML/CSS hoặc Tailwind thì phải tuân theo:

- Border radius lớn
- Shadow nhẹ
- Màu nền sáng trung tính
- Card trắng
- Border mảnh
- Primary indigo/purple
- Spacing rộng
- Font sạch, hiện đại
- Trạng thái hover/focus/disabled rõ nhưng tinh tế
- Không dùng animation mạnh
- Animation nếu có chỉ nên nhẹ, nhanh và mềm

---

## 17. Mẫu mô tả ngắn để agent ghi nhớ

> Hãy thiết kế giao diện theo phong cách tối giản cao cấp, nền sáng trung tính, card trắng bo góc lớn, màu nhấn tím/xanh tím, border mảnh, shadow mềm, khoảng trắng rộng, typography sạch, các component đồng bộ, tinh tế và hiện đại.

---

## 18. Kết luận

Đây là một design language theo hướng:
- premium minimal
- soft dashboard
- calm productivity
- elegant component system

Mọi UI do agent tạo ra phải ưu tiên:
**đẹp, sạch, sang, dễ dùng, đồng bộ và mềm mại**.

Nếu có xung đột giữa “nhiều hiệu ứng” và “sự tinh tế”, luôn ưu tiên **sự tinh tế**.
