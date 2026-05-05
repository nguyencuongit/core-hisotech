Vai trò từng folder trong 1 domain (theo SKILL.md mới)
src/Domains/<Domain>/
├── Actions/              (mandatory)
├── DTO/                  (mandatory)
├── Enums/                (optional)
├── Forms/                (mandatory)
├── Http/
│   ├── Controllers/      (mandatory)
│   └── Requests/         (mandatory)
├── Models/               (mandatory)
├── Permissions/          (mandatory) ← MỚI
├── Providers/            (mandatory)
├── Repositories/
│   ├── Eloquent/         (mandatory)
│   └── Interfaces/       (mandatory)
├── Services/             (mandatory)
├── Support/              (optional)
├── Tables/               (mandatory)
└── UseCases/             (mandatory)
Bảng vai trò chi tiết
Folder	File ví dụ	Trách nhiệm	Được phép gọi gì
Actions/	CreatePackingAction.php	1 thao tác đơn lẻ (1 lý do để thay đổi). Wrap DB::transaction nếu cần.	Service, Repository, Usecase
DTO/	PackingDTO.php	Value object readonly. Normalize input từ Request. Không có logic DB.	— (chỉ data)
Enums/	DocumentStatusEnum.php	PHP enum định nghĩa hằng + label.	—
Forms/	PackingForm.php	Render Botble admin form. Không có business logic. Set setValidatorClass().	Repository (cho dropdown), Permissions (cho show/hide button)
Http/Controllers/	PackingController.php	THIN. Nhận request → DTO → gọi Usecase/Action → response.	Usecase, Action, Service, Permissions
Http/Requests/	PackingRequest.php	FormRequest validation: required/unique/exists/array rules.	— (declarative rules only)
Models/	PackingList.php, Package.php, PackingListItem.php	Eloquent: table, fillable, casts, relations. KHÔNG có orchestration.	— (passive)
Permissions/	PackingPermissions.php	1 file duy nhất chứa public const cho mọi permission của domain. Route/Table/Form/Provider import const.	— (constants only)
Providers/	PackingProvider.php	Bind Interface → Eloquent, đăng ký dashboard menu, register events nếu có.	Repository binding, Permissions cho menu
Repositories/Interfaces/	PackingInterface.php	Contract cho data access.	—
Repositories/Eloquent/	PackingRepository.php	CHỖ DUY NHẤT được gọi Model::query(), Model::create(), find(), where().	Models trực tiếp
Services/	PackingService.php	Core business logic, transactional, multi-step domain operations.	Repository (qua Interface), khác Service trong cùng domain
Support/	PalletLocationRules.php	Helper, value object, rule class không thuộc layer chính.	—
Tables/	PackingTable.php	Botble admin list. query() được dùng getModel()->query() (exception đặc biệt). Add scope inventory_warehouse_ids() ở đây.	Permissions cho EditAction/DeleteAction
UseCases/	PackingUsecase.php	Orchestrate nhiều Service/Repository cho 1 user operation. Class suffix giữ Usecase.	Service, Repository, Action
🎯 Quy tắc gọi giữa các layer (sau patch SKILL)
HTTP request
   │
   ▼
Controller ──► Request (validate)
   │              │
   │              ▼
   │           DTO::fromRequest()
   │              │
   ▼              ▼
Usecase ─────► Action
   │              │
   ▼              ▼
Service ◄───────┘
   │
   ▼
Repository (Interface)
   │
   ▼
Repository (Eloquent) ──► Model
                           ▲
                           │
                    CHỈ Ở ĐÂY!
Cấm tuyệt đối:

Controller gọi Model::query() ❌
Service gọi Model::find() ❌
Form gọi Model::pluck() (trừ trường hợp đơn giản) ⚠️
Hard-code permission string 'packing.create' ở bất cứ đâu ❌
Bắt buộc:

Mọi chỗ check permission → PackingPermissions::EDIT
Mọi chỗ truy vấn model → đi qua Repository
Mỗi domain có đúng 1 file <Domain>Permissions.php
📋 Checklist cho agent đọc SKILL mới rồi code
Trước khi code	Trong khi code	Trước khi finish
Đọc folder domain bị touch	DTO normalize input	Lint PHP files đã sửa
Đọc <Domain>Permissions.php	Service gọi qua XxxInterface	Grep ::query() ngoài Repositories/Eloquent/
Đọc Repository interface	Permission từ const	Grep hard-coded '<domain>.xxx' strings
Đọc routes/web.php section liên quan	Transaction wrap multi-table	route:list xem permission đúng