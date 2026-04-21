# Logistics Plugin — Architecture & Skills Documentation

> **Plugin:** `botble/logistics` | **Version:** `1.0.0` | **Core:** `7.6.3+`
> **Namespace:** `Botble\Logistics`

---

## 1. Tổng quan Architecture

Plugin Logistics **KHÔNG follow đúng pattern `Domains/`** mà dùng **flat structure** — tất cả logic nằm trực tiếp trong `src/`, tổ chức theo **architectural concern** (layer) thay vì business domain. Plugin đủ nhỏ nên không cần chia domain.

```
logistics/
├── config/          # Cấu hình quyền permissions
├── database/
│   ├── migrations/   # Tạo bảng DB
│   └── seeders/     # Seed dữ liệu mặc định
├── helpers/          # Helper functions (public)
├── public/js/       # Vue components & JS phía frontend
├── resources/
│   ├── lang/        # i18n (en, vi)
│   └── views/       # Blade templates (admin)
├── routes/          # Định nghĩa routes
├── src/             # ✅ Core business logic (FLAT — không có Domains/)
└── plugin.json      # Metadata plugin
```

### So sánh: Expected Pattern vs Thực tế

| Component | Pattern gốc | Logistics Plugin |
|-----------|-------------|-----------------|
| `Http/Controllers/` | ✅ | ✅ |
| `Http/Requests/` | ✅ | ✅ |
| `Http/DataTables/` | ✅ | ❌ Nằm trong `Tables/` |
| `Models/` | ✅ | ✅ |
| `Tables/` | ✅ | ✅ |
| `Forms/` | ✅ | ✅ |
| `Enums/` | ✅ | ✅ |
| `Services/` | ✅ | ✅ (thêm Drivers/, Factories/, Mappers/) |
| `Repositories/` | ✅ | ✅ (Interfaces/ + Eloquent/ + Caches/) |
| `Supports/` | ✅ | ✅ |
| `Domains/` | ✅ | ❌ Không có |
| `DTO/` | ❌ | ✅ |
| `Usecase/` | ❌ | ✅ (thay cho domain services) |
| `Events/` | ❌ | ✅ |
| `Listeners/` | ❌ | ✅ |
| `Widgets/` | ❌ | ✅ |
| `Exceptions/` | ❌ | ✅ |
| `Facades/` | ❌ | ✅ |
| `Providers/` | ❌ | ✅ |

---

## 2. Chi tiết từng thư mục trong `src/`

### 2.1. `src/Http/Controllers/` — Điểm vào xử lý request

Chứa **8 Controllers**, mỗi controller xử lý 1 nhóm chức năng riêng:

| File | Chức năng | Route chính |
|------|-----------|-------------|
| `LogisticsController.php` | CRUD resource cho settings logistics (tên, trạng thái) | `/logistics` |
| `ShippingProviderController.php` | CRUD các đơn vị vận chuyển (GHN, ViettelPost) | `/logistics/shipping-providers` |
| `CreateOrderShippingController.php` | Tạo mới / xem / hủy đơn ship từ đơn hàng e-commerce | `/logistics/shipping-create` |
| `AddressShippingController.php` | Lookup tỉnh/thành, quận/huyện theo mã provider | `/logistics/shipping-address/provincen/{code}` |
| `GetAddressController.php` | AJAX endpoint lấy quận/huyện, phường/xã | `/ajax/districts`, `/ajax/ward` |
| `ShippingFeeController.php` | Tính phí ship tại checkout và trang product | `/ajax/shipping-fee`, `/ajax/shipping-fee-checkout` |
| `WebhookController.php` | Nhận webhook từ provider (ViettelPost) | `/webhook/shipping/{provider}` |
| `DashboardController.php` | Dashboard widget report | `/logistics/dashboard` |

> **Cách hoạt động chung:** Controller nhận HTTP request → validate bằng Request class → gọi Usecase xử lý business logic → trả về view JSON response.

---

### 2.2. `src/Http/Requests/` — Validate input

| File | Validate gì |
|------|------------|
| `LogisticsRequest.php` | Settings logistics — name, status |
| `CreateOrderShippingRequest.php` | Tạo đơn ship — địa chỉ sender/receiver, kích thước kiện, sản phẩm, COD, mã khuyến mãi. Có message lỗi tiếng Việt |

---

### 2.3. `src/Models/` — Data layer (Eloquent Models)

| Model | Bảng | Mô tả |
|-------|------|-------|
| `Logistics.php` | `logistics` | Bảng settings đơn giản (id, name, status) |
| `shippingOrder.php` | `shipping_orders` | Liên kết `order_id` với provider, lưu status, mã vận đơn, phí ship |
| `shippingOrderInformation.php` | `shipping_order_information` | Thông tin đầy đủ người gửi/người nhận, kích thước kiện, COD, danh sách sản phẩm |
| `shippingProvider.php` | `shipping_providers` | Cấu hình provider (GHN, ViettelPost) — lưu JSON `information` (token, shop_id...) |
| `shippingProvinceMapping.php` | `shipping_province_mappings` | Map mã tỉnh/thành nội bộ ↔ mã tỉnh của provider |
| `shippingDistrictMapping.php` | `shipping_district_mappings` | Map mã quận/huyện nội bộ ↔ mã quận của provider |
| `Shipment.php` | (kế thừa từ `Botble\Ecommerce`) | Model shipment của e-commerce — dùng để sync trạng thái |

> **Quy tắc đặt tên:** Plugin dùng snake_case cho model files (`shippingOrder.php`) nhưng vẫn dùng PascalCase trong class name. Table name giữ nguyên snake_case.

---

### 2.4. `src/Repositories/` — Repository Pattern (Data Access)

```
Repositories/
├── Interfaces/         # Contract — định nghĩa methods
├── Eloquent/           # Implementation thực tế — truy xuất DB
└── Caches/             # Cache decorator — cache kết quả (decorator pattern)
```

**Tất cả Interface → Eloquent binding được register trong `LogisticsServiceProvider`.**

| Interface | Eloquent Repo | Mô tả |
|-----------|---------------|-------|
| `ShippingOrderInterface` | `ShippingOrderRepository` | Tìm đơn theo order_id/code, update status |
| `ShippingOrderInformationInterface` | `ShippingOrderInformationRepository` | Lưu/thông tin người gửi/người nhận |
| `ShippingProviderInterface` | `ShippingProviderRepository` | CRUD đơn vị vận chuyển |
| `ShippingProvinceMappingInterface` | `ShippingProvinceMappingRepository` | Map mã tỉnh |
| `ShippingDistrictMappingInterface` | `ShippingDistrictMappingRepository` | Map mã quận |
| `OrderShippingInterface` | `OrderShippingRepository` | Tìm shipment theo order_id |
| `OrderAddressInterface` | `OrderAddressRepository` | Dữ liệu địa chỉ đơn hàng |
| `StoreInterface` | `StoreRepository` | Dữ liệu store (Marketplace) |
| `OrderInterface` | `OrderRepository` | Dữ liệu đơn hàng e-commerce |

**Cache Decorators** (đến từ Marketplace plugin, có thể chưa dùng hết):
- `RevenueCacheDecorator`
- `StoreCacheDecorator` ⚠️ deprecated stub
- `VendorInfoCacheDecorator`
- `WithdrawalCacheDecorator`

> **Cách hoạt động:** Controller/Usecase gọi Interface → Laravel DI resolve ra Eloquent implementation → truy xuất DB. Muốn cache? Wrap bằng CacheDecorator.

---

### 2.5. `src/Services/` — Business Logic & External Integrations

```
Services/
├── Contracts/           # Interface định nghĩa driver API
├── Drivers/              # Implementation cho từng provider
│   ├── GHNDriver.php     # Giao Hàng Nhanh
│   └── ViettelPostDriver.php
├── Factories/            # Factory pattern — tạo driver theo provider
│   └── ShippingFactory.php
└── Mappers/              # Chuyển đổi data format
    ├── ProvinceMapper.php
    ├── DistrictMapper.php
    └── ShippingOrderInformationMapper.php
```

#### `Contracts/ShippingServiceInterface.php`
Định nghĩa contract chung cho tất cả driver:
```php
public function calculateFee(...);
public function ShippingCreate(...);
public function cancelOrderShipping(...);
public function getProvince(...);
public function getDistrict(...);
```

#### `Drivers/` — Driver Pattern
| Driver | Đặc điểm |
|--------|----------|
| `GHNDriver.php` | API GHN — tính phí, tạo đơn, hủy, lấy tỉnh/quận. ⚠️ Token/shop_id hardcoded (chỉ dev) |
| `ViettelPostDriver.php` | API ViettelPost — full driver, auth token, webhook parser, cache config |

#### `Factories/ShippingFactory.php`
```php
ShippingFactory::make('ghn');        // → GHNDriver
ShippingFactory::make('viettelpost'); // → ViettelPostDriver
```

#### `Mappers/` — Data Transformation
| Mapper | Chức năng |
|--------|----------|
| `ProvinceMapper.php` | Map mã tỉnh nội bộ ↔ mã tỉnh provider. ViettelPost dùng static map. |
| `DistrictMapper.php` | Map mã quận nội bộ ↔ mã quận provider |
| `ShippingOrderInformationMapper.php` | Convert DTO → array để lưu vào Model |

---

### 2.6. `src/Usecase/` — Application Services (Business Logic Orchestration)

Đây là **Application Layer** — nơi điều phối business logic, thay vì đặt trong Controller. Mỗi Usecase xử lý **1 use-case cụ thể**.

| Usecase | Mô tả | Flow |
|---------|-------|------|
| `CreateShippingUsecase.php` | Tạo đơn ship mới | Validate duplicate → Map địa chỉ → Gọi provider API → Save order + info → Dispatch event |
| `WebhookUsecase.php` | Xử lý webhook từ provider | Parse payload → Map status → Update shipping order → Update e-commerce shipment |
| `OrderShippingUsecase.php` | Lấy data cho form tạo đơn | Fetch thông tin order, sản phẩm, địa chỉ, providers, danh sách tỉnh/thành |
| `CancelShippingOrderUsecase.php` | Hủy đơn ship | Gọi provider API cancel → Update internal status |
| `ShippingFeeUsecase.php` | Tính phí ship tại checkout | Lấy data từ request → Gọi provider fee API → Trả về response |

> **Khi nào dùng Usecase?** Khi logic phức tạp, cần nhiều bước, gọi nhiều repository/services → đặt trong Usecase thay vì Controller.

---

### 2.7. `src/DTO/` — Data Transfer Objects

DTO dùng để **truyền data giữa các layer** thay vì dùng array thuần. Mỗi DTO represent 1 nhóm data cụ thể.

| DTO | Mô tả |
|-----|-------|
| `ShippingData.php` | Data tính phí ship (từ, đến, kích thước, trọng lượng) |
| `ShippingCreateDTO.php` | Data tạo đơn ship (sender, receiver, package, COD...) |
| `ShippingCreateResponseDTO.php` | Response từ provider khi tạo đơn thành công |
| `CancelOrderShippingDTO.php` | Data hủy đơn |
| `ProvinceDTO.php` | Data tỉnh/thành |
| `DistrictDTO.php` | Data quận/huyện |
| `ShippingShowOrderDTO.php` | Data hiển thị chi tiết đơn |
| `WebhookDataDTO.php` | Data webhook từ provider |
| `infAddressDTO.php` | Data địa chỉ thông tin |

---

### 2.8. `src/Enums/` — Constants dạng Enum

| Enum | Giá trị | Dùng ở đâu |
|------|---------|-----------|
| `ShippingStatus.php` | `CREATED`, `PICKED`, `SHIPPING`, `DELIVERED`, `CANCEL`, `FAILED` | shippingOrder model, Status filter |
| `ShipmentStatus.php` | `READY_TO_SHIP`, `PICKED`, `DELIVERING`, `DELIVERED`, `CANCELED` | E-commerce Shipment sync |

**Map ShippingStatus ↔ ShipmentStatus** trong `OrderShippingTable.php` để filter DataTable.

---

### 2.9. `src/Events/` + `src/Listeners/` — Event-Driven Architecture

```
Events/
└── ShippingOrderStatusUpdated.php    # Bắn khi trạng thái đơn ship thay đổi

Listeners/
├── UpdateOrderStatusListener.php      # Lắng nghe → sync sang E-commerce Shipment
└── RegisterLogisticWidget.php         # Đăng ký dashboard widgets
```

**Event registration** trong `EventServiceProvider.php`:
```php
ShippingOrderStatusUpdated::class → UpdateOrderStatusListener::class
```

**Flow hoàn chỉnh:**
```
Webhook Provider
    ↓
WebhookUsecase
    ↓ (map status)
Update shippingOrder model (DB)
    ↓ (fire ShippingOrderStatusUpdated)
UpdateOrderStatusListener
    ↓ (sync to E-commerce Shipment)
E-commerce plugin nhận biến trạng thái
```

---

### 2.10. `src/Tables/` — DataTable Presentation (Admin)

| Table | Mô tả |
|-------|-------|
| `LogisticsTable.php` | DataTable danh sách logistics settings — id, name, created_at, status |
| `OrderShippingTable.php` | DataTable phức tạp cho danh sách đơn ship — filter theo shipment status map, hiển thị customer, payment, trạng thái vận chuyển |

> **DataTables nằm trong `Tables/`** chứ không phải `Http/DataTables/` như pattern gốc.

---

### 2.11. `src/Forms/` — Form Presentation (Admin)

| Form | Mô tả |
|------|-------|
| `LogisticsForm.php` | Form cài đặt logistics — name + status |
| `OrderShippingInformationForm.php` | Form thông tin đơn ship — sender/receiver, kích thước kiện |

---

### 2.12. `src/Widgets/` — Dashboard Report Cards

| Widget | Mô tả |
|--------|-------|
| `ShippingCard.php` | Base card ship |
| `CreatedShippingCard.php` | Card số đơn đã tạo |
| `DeliveredShippingCard.php` | Card số đơn đã giao |
| `FailedShippingCard.php` | Card số đơn thất bại |
| `FeeShippingCard.php` | Card tổng phí ship |
| `ShippingProviderCard.php` | Card thông tin provider |
| `ShippingProviderFeeChart.php` | Chart phí theo provider |
| `ShippingProviderOrdersChart.php` | Chart số đơn theo provider |
| `Traits/HasCategory.php` | Trait dùng chung cho category widgets |

> **Đăng ký widgets** qua `RegisterLogisticWidget` listener trong `EventServiceProvider`.

---

### 2.13. `src/Supports/LogisticHelper.php` — Helper Class

⚠️ **Code smell:** File này chứa ~2000 dòng — gần như copy nguyên `Botble\Ecommerce\Supports\EcommerceHelper`. Cung cấp helpers cho:
- Cart, wishlist, reviews, shipping, tax
- Country/state data, price formatting
- Shipping fee helpers
- E-commerce settings

**Facade:** `Botble\Logistics\Facades\LogisticHelper` → trỏ đến class trên.

---

### 2.14. `src/Exceptions/`

| Exception | Mô tả |
|-----------|-------|
| `ShippingException.php` | Custom exception với `rawMessage` và `provider` context — dùng khi provider API trả lỗi |

---

### 2.15. `src/Providers/` — Service Provider (DI + Lifecycle)

| Provider | Chức năng |
|----------|-----------|
| `LogisticsServiceProvider.php` | Register interfaces → repos, load assets, thêm dashboard menu, seed Vietnamese states từ ViettelPost API, register events, add shipping fee filter hook |
| `EventServiceProvider.php` | Bind event → listener, register widget rendering |

---

## 3. Các Pattern Design được sử dụng

### 3.1. Repository Pattern
- Interface định nghĩa contract
- Eloquent implementation thực hiện truy xuất DB
- Cache decorator wrap bên ngoài

### 3.2. Driver / Strategy Pattern
- `ShippingServiceInterface` là strategy contract
- `GHNDriver`, `ViettelPostDriver` là concrete strategies
- `ShippingFactory` là factory tạo strategy

### 3.3. Factory Pattern
- `ShippingFactory::make($provider)` → trả về driver phù hợp
- Tách logic khởi tạo khỏi business logic

### 3.4. Usecase / Application Service Pattern
- Logic phức tạp được tách riêng trong `Usecase/`
- Controller chỉ nhận request, gọi usecase, trả response
- Dễ test, dễ maintain

### 3.5. Event-Driven Pattern
- `ShippingOrderStatusUpdated` event được fire khi status thay đổi
- `UpdateOrderStatusListener` xử lý side effect (sync sang e-commerce)
- Loose coupling giữa các plugin

### 3.6. DTO Pattern
- Tách data transfer khỏi Model
- Immutability, type safety
- Dùng cho API request/response, internal data passing

---

## 4. Luồng hoạt động chính

### 4.1. Tạo đơn ship (Create Shipping Order)

```
User click "Tạo đơn vận chuyển" (từ e-commerce order)
    ↓
CreateOrderShippingController@create
    ↓
OrderShippingUsecase → lấy data (order, products, addresses, providers)
    ↓
Trả về form.blade.php (Vue component: CreateOrderComponent.vue)
    ↓
User điền thông tin → submit
    ↓
CreateOrderShippingController@store
    ↓
CreateOrderShippingRequest (validate)
    ↓
CreateShippingUsecase
    ├── Check duplicate order
    ├── Map địa chỉ nội bộ → mã provider (ProvinceMapper, DistrictMapper)
    ├── ShippingFactory::make($provider)
    ├── Gọi $driver->ShippingCreate($dto)
    ├── Lưu shippingOrder + shippingOrderInformation (Repositories)
    └── Dispatch ShippingOrderStatusUpdated event
    ↓
UpdateOrderStatusListener → sync status sang E-commerce Shipment
    ↓
Webhook sau đó cập nhật status khi trạng thái thay đổi
```

### 4.2. Webhook xử lý cập nhật trạng thái

```
Provider (ViettelPost/GHN) gửi webhook
    ↓
WebhookController@handle
    ↓
WebhookUsecase
    ├── Parse provider-specific payload
    ├── Map provider status → internal ShippingStatus enum
    ├── ShippingOrderRepository → update status
    └── Dispatch ShippingOrderStatusUpdated
    ↓
UpdateOrderStatusListener → sync sang E-commerce Shipment
    ↓
E-commerce plugin nhận notification
```

### 4.3. Tính phí ship tại Checkout

```
User xem checkout page
    ↓
ShippingFeeController (hoặc Frontend AJAX)
    ↓
ShippingFeeUsecase
    ├── Lấy sender address (từ config/admin address)
    ├── Lấy receiver address (từ order)
    ├── ShippingFactory::make($provider)
    ├── Gọi $driver->calculateFee($shippingData)
    └── Trả về phí ship
```

### 4.4. Tính phí ship tại Product Page

```
User chọn sản phẩm + địa chỉ
    ↓
shipping-fee.js (public/js/)
    ↓
AJAX → /ajax/shipping-fee
    ↓
ShippingFeeController
    ↓
GHNDriver/ViettelPostDriver.calculateFee()
    ↓
Trả về JSON response
```

---

## 5. Database — 6 Migrations

| Migration | Bảng | Purpose |
|-----------|------|---------|
| `logistics_create_logistics_table` | `logistics` | Settings đơn giản |
| `create_shipping_providers_table` | `shipping_providers` | Provider configs (GHN, ViettelPost) |
| `create_shipping_province_mappings_table` | `shipping_province_mappings` | Map mã tỉnh nội bộ ↔ provider |
| `create_shipping_district_mappings_table` | `shipping_district_mappings` | Map mã quận nội bộ ↔ provider |
| `create_shipping_order_table` | `shipping_orders` | Đơn ship — FK order_id, provider, status, code, fee |
| `create_shipping_order_information_table` | `shipping_order_information` | Chi tiết sender/receiver, package, COD |

**Seeder:** `ShippingProviderSeeder.php` — tạo 2 dòng mặc định cho GHN + ViettelPost với credentials placeholder.

---

## 6. Routes chính

```php
// Admin routes (middleware: web)
GET/POST   /logistics                           → LogisticsController (resource)
GET        /logistics/dashboard                → DashboardController
GET/POST   /logistics/dashboard/widget-config  → Widget config
GET/POST   /logistics/shipping-providers        → ShippingProviderController (resource)
GET        /logistics/shipping-address/provincen/{code}  → Province lookup
GET        /logistics/shipping-address/district/{code}   → District lookup
POST       /logistics/shipping-address/address-admin    → Save admin address
GET/POST   /logistics/shipping-create           → CreateOrderShippingController (resource)
GET        /logistics/shipping-create/factories/{id}     → Get factories

// Public AJAX routes
GET        /ajax/districts                      → GetAddressController
GET        /ajax/ward                           → GetAddressController
GET        /ajax/shipping-fee                   → ShippingFeeController (product page)
POST       /ajax/shipping-fee-checkout           → ShippingFeeController (checkout)

// Webhook
POST       /webhook/shipping/{provider}         → WebhookController
```

---

## 7. Frontend Assets

```
public/js/
├── location.js              # Cascading selects tỉnh/quận/phường
├── shipping-fee.js          # Tính phí tại product page
├── shipping_checkout.js      # Tính phí tại checkout
├── report.js                # Reports JS
└── components/
    ├── CreateOrderComponent.vue    # Form tạo đơn ship
    ├── DiscountComponent.vue        # UI discount
    ├── EcommerceModal.vue           # Modal wrapper
    ├── RevenueChart.vue             # Chart doanh thu
    ├── SalesReportsChart.vue        # Chart bán hàng
    └── partials/                    # Sub-components (product modal, address...)
```

---

## 8. Kết luận

Plugin Logistics follow **flat structure** (không có `Domains/`), tổ chức theo **architectural layers**:
- **Http Layer:** Controllers + Requests
- **Data Layer:** Models + Repositories
- **Business Layer:** Services (Drivers, Mappers) + Usecases
- **Presentation Layer:** Tables + Forms + Widgets + Views
- **Cross-cutting:** Events, Listeners, Helpers, Exceptions

**Pattern nổi bật:** Repository (Interface + Eloquent + Cache), Driver/Strategy, Factory, Usecase, Event-Driven, DTO.

**Điểm cần cải thiện:**
1. `Supports/LogisticHelper.php` chứa ~2000 dòng duplicate từ EcommerceHelper
2. Cache decorators (`StoreCacheDecorator`) là stub chưa dùng
3. GHNDriver có credentials hardcoded — không an toàn cho production
