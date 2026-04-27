---
name: inventory-plugin
description: Work on the Botble Inventory plugin at platform/plugins/inventory. Use when editing Inventory domain modules such as WarehouseStaff, Warehouse, Supplier, GoodsReceipt, routes, providers, permissions, forms, tables, requests, migrations, services, repositories, usecases, actions, DTOs, or admin UI behavior. Follow the mandatory domain-first structure, register domain providers from InventoryServiceProvider, and validate PHP/Laravel behavior before finishing.
---

# Inventory Plugin Skill

## 1. Purpose

Use this skill when working inside the Botble Inventory plugin:

```txt
platform/plugins/inventory
```

This skill is about **coding structure, file placement, domain architecture, Botble conventions, validation, routes, permissions, providers, and final checks**.

This skill is **not** a warehouse business-logic specification.  
Do not add warehouse workflow rules here unless the user explicitly asks for business logic.

---

## 2. Start Every Task

Before editing any code:

1. Identify the touched domain.
2. Read the full touched domain folder before editing.
3. Read related files if the task can affect them:
   - `routes/web.php`
   - `config/permissions.php`
   - the domain provider
   - `src/Providers/InventoryServiceProvider.php`
   - related migrations
   - related Forms/Tables/Requests/Services/Repositories/Usecases/Actions/DTOs
4. Keep feature code inside:

```txt
src/Domains/<Domain>
```

5. Keep root `src/` folders for shared plugin infrastructure only.
6. Do not move domain-owned code back to root folders.
7. Run focused PHP lint and route/provider checks before finishing.
8. Report clearly:
   - files changed
   - routes changed
   - permissions changed
   - migrations changed
   - checks run
   - anything left unfinished

---

## 3. Mandatory Domain Structure

Every domain must follow this structure.

Do not treat `Services/`, `Repositories/`, `Usecase/`, `Actions/`, or `DTO/` as optional.  
They are mandatory domain layers and should be used consistently.

```txt
src/Domains/<Domain>/
  Actions/
  DTO/
  Forms/
  Http/
    Controllers/
    Requests/
  Models/
  Providers/
  Repositories/
    Eloquent/
    Interfaces/
  Services/
  Tables/
  Usecase/
```

### Layer responsibilities

#### `Forms/`

Botble admin form definitions.

Use for:

- create forms
- edit forms
- field definitions
- form buttons
- admin form layout

Do not put complex business logic here.

---

#### `Tables/`

Botble admin table definitions.

Use for:

- listing data
- table columns
- table filters
- table actions
- bulk actions
- scoped admin queries

Do not write complex mutation logic here.

---

#### `Http/Controllers/`

Controllers must stay thin.

Controllers should:

- receive request
- call Form when using Botble form flow
- call Usecase/Action/Service
- redirect with success/error message
- never contain large business logic
- never directly perform multi-table business writes unless delegated

Preferred flow:

```txt
Controller
→ Request
→ DTO
→ Action or Usecase
→ Service
→ Repository
→ Model
```

---

#### `Http/Requests/`

Request validation lives here.

Requests must validate:

- required fields
- unique rules
- exists rules
- enum/status rules
- array inputs
- nested array items
- update rules that ignore current route model

Do not trust controller input without Request validation.

---

#### `Models/`

Eloquent models live here.

Models should define:

- table name
- fillable fields
- casts
- relationships
- basic scopes only

Do not put orchestration or multi-table workflow logic in Models.

---

#### `Providers/`

Domain providers live here.

A domain provider owns:

- domain menu registration
- domain repository bindings
- domain events/listeners if needed
- domain-specific bootstrapping

Do not place domain-specific menus or bindings in the root `InventoryServiceProvider`.

---

#### `Repositories/Interfaces/`

Repository interfaces live here.

Use interfaces for data access contracts.

Example:

```php
interface WarehouseStaffAssignmentInterface
{
    public function findByStaff(int $staffId);
}
```

---

#### `Repositories/Eloquent/`

Eloquent repository implementations live here.

Use repositories for:

- reusable domain queries
- scope-aware queries
- complex data access
- find/update helpers

Do not put controller-specific UI logic here.

---

#### `Services/`

Services contain core domain logic.

Use services for:

- reusable business operations
- multi-step domain logic
- transactional data changes
- calculations
- state changes
- persistence coordination within one domain

Services may call repositories and models.

---

#### `Usecase/`

Usecases orchestrate a complete user/system operation.

Use when a workflow coordinates multiple services/repositories/models.

Examples:

```txt
AssignmentsUsercase
CreateGoodsReceiptUsecase
UpdateWarehouseMapUsecase
```

Keep existing folder spelling as `Usecase/` unless the user asks for a naming migration.

---

#### `Actions/`

Actions represent focused single-purpose operations.

Use for small, reusable commands such as:

```txt
CreatePalletAction
MovePalletAction
GenerateLocationTreeAction
SyncWarehouseAssignmentAction
```

Actions should be easy to call from controllers, usecases, jobs, or services.

---

#### `DTO/`

DTOs normalize input data between Request/Controller and business layers.

Use DTOs for:

- validated request data
- typed payloads
- avoiding raw `$request->all()` spread across services
- consistent input shape

Example flow:

```txt
Request validated data
→ DTO::fromRequest()
→ Usecase/Action/Service
```

---

## 4. Domain File Placement Rules

### Always keep domain files inside the domain folder

Correct:

```txt
src/Domains/WarehouseStaff/Services/WarehouseStaffService.php
src/Domains/WarehouseStaff/Repositories/Eloquent/WarehouseStaffAssignmentRepository.php
src/Domains/WarehouseStaff/Actions/SyncWarehouseStaffAssignmentsAction.php
```

Wrong:

```txt
src/Services/WarehouseStaffService.php
src/Repositories/WarehouseStaffAssignmentRepository.php
src/Http/Controllers/WarehouseStaffController.php
```

### Root folders are shared infrastructure only

The root plugin folders may contain:

```txt
src/Providers/InventoryServiceProvider.php
src/Http/Middleware/
src/Support/
src/Helpers/
```

Do not add domain-specific CRUD code to root folders.

---

## 5. Naming Rules

Use clear domain names.

Examples:

```txt
WarehouseStaff
Warehouse
Supplier
GoodsReceipt
WarehouseMap
Pallet
StockLedger
```

Class names should reflect responsibility:

```txt
WarehouseStaffController
WarehouseStaffRequest
WarehouseStaffForm
WarehouseStaffTable
WarehouseStaffService
WarehouseStaffRepository
WarehouseStaffDTO
CreateWarehouseStaffAction
SyncWarehouseStaffAssignmentsAction
WarehouseStaffUsecase
```

### Existing legacy names

If a domain already has an existing spelling, preserve it unless the task explicitly asks for cleanup.

Current known legacy name:

```txt
src/Domains/WarehouseStaff/Usecase/AssignmentsUsercase.php
```

Do not rename this file casually because existing code may depend on it.

If renaming is requested, update:

- class name
- namespace
- imports
- controller usage
- provider bindings if any
- lint and route checks

---

## 6. WarehouseStaff Standard

`WarehouseStaff` owns:

- staff
- positions
- staff-to-warehouse assignments

Keep these files under:

```txt
src/Domains/WarehouseStaff
```

Required files:

```txt
Actions/
DTO/
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
Services/
Tables/WarehouseStaffTable.php
Tables/WarehousePositionTable.php
Usecase/AssignmentsUsercase.php
```

### WarehouseStaff behavior rules

- `WarehouseStaffProvider` registers the staff/position admin menu.
- `WarehouseStaffProvider` binds `WarehouseStaffAssignmentInterface` to `WarehouseStaffAssignmentRepository`.
- `InventoryServiceProvider` registers `WarehouseStaffProvider::class`.
- Do not move staff menus back to the root provider.
- `WarehouseStaffController` saves the Botble form, then syncs warehouse assignments through `AssignmentsUsercase` inside `DB::transaction()`.
- `AssignmentsUsercase` is the current assignment orchestration point.
- Keep repository details out of controllers.
- `WarehouseStaffAssignments` uses `inv_warehouse_staff_assignments`.
- `inv_warehouse_staff_assignments` should keep unique `staff_id + warehouse_id`.
- Treat staff-to-warehouse as many-to-many through `assignments()`.
- Do not rely on `WarehouseStaff::warehouse()` for access checks or lists.
- `WarehouseStaffTable` must eager-load `assignments.warehouse`.
- Non-super-admin staff queries must respect `inventory_warehouse_ids()`.
- `InventoryContextMiddleware` maps the logged-in user to `WarehouseStaff`, then to assigned warehouse IDs.
- `super_user === 1` is unscoped.
- `UserWarehouse` and `inv_user_warehouses` exist, but current context flow uses `WarehouseStaffAssignments`.
- Do not switch staff context flow without an explicit task.

---

## 7. WarehouseStaff Route And Permission Rules

Route names:

```txt
inventory.warehouse-staff.*
inventory.warehouse-positions.*
```

Permission flags for this domain should be consistent and bare:

```txt
warehouse-staff.index
warehouse-staff.create
warehouse-staff.edit
warehouse-staff.destroy

warehouse-positions.index
warehouse-positions.create
warehouse-positions.edit
warehouse-positions.destroy
```

Rules:

- Keep route permissions, table action permissions, menu permissions, and `config/permissions.php` aligned.
- Do not mix `warehouse-positions.delete` with `warehouse-positions.destroy`.
- Do not mix `inventory.warehouse-staff.*` permission flags unless the full permission set is migrated.
- When changing permission names, update:
  - routes
  - tables
  - menus
  - config/permissions.php
  - tests/checks if any

---

## 8. WarehouseStaff Validation Rules

### Staff

`WarehouseStaffRequest` must validate:

```txt
full_name required
staff_code required and unique in inv_warehouse_staff, ignoring current model on update
phone required
email required and valid email
warehouse_id required array
warehouse_id.* exists in inv_warehouses
position exists in inv_warehouse_positions
status valid
user_id nullable and exists in users if provided
```

### Position

`WarehousePositionRequest` must validate:

```txt
name required
code required and unique in inv_warehouse_positions, ignoring current model on update
level integer min 0 max 100
is_active boolean/status valid
```

### Label rule

If editing Vietnamese labels:

- write valid UTF-8
- or use translation keys
- do not preserve mojibake labels

Example:

```txt
Mã chức vụ
```

not:

```txt
MÃ nhÃ¢n viÃªn
```

---

## 9. Warehouse Domain Rules

Warehouse-owned code belongs under:

```txt
src/Domains/Warehouse
```

Required structure:

```txt
Actions/
DTO/
Forms/
Http/Controllers/
Http/Requests/
Models/
Providers/
Repositories/Eloquent/
Repositories/Interfaces/
Services/
Tables/
Usecase/
```

Warehouse product configuration uses:

```txt
inv_warehouse_products
```

It defines which ecommerce products or variations are allowed in a warehouse.

Known files:

```txt
Models/WarehouseProduct.php
Http/Controllers/WarehouseProductController.php
Http/Requests/WarehouseProductRequest.php
Services/WarehouseProductService.php
```

Rules:

- Validate product existence.
- Validate variation ownership.
- Validate supplier product consistency when supplier product is used.
- Validate duplicate warehouse/product/variation combinations, including nullable variation cases.
- If a warehouse product has stock, receipt, or balance history, deactivate it instead of deleting it.
- Controllers must call Services/Usecases/Actions for multi-step logic.

---

## 10. GoodsReceipt Domain Rules

GoodsReceipt-owned code belongs under:

```txt
src/Domains/GoodsReceipt
```

Required structure:

```txt
Actions/
DTO/
Forms/
Http/Controllers/
Http/Requests/
Models/
Providers/
Repositories/Eloquent/
Repositories/Interfaces/
Services/
Tables/
Usecase/
```

Known files:

```txt
Http/Controllers/GoodsReceiptController.php
Http/Requests/GoodsReceiptRequest.php
Models/GoodsReceipt*.php
Providers/GoodsReceiptProvider.php
Services/GoodsReceiptService.php
Tables/GoodsReceiptTable.php
```

Rules:

- Create and update flows must be transactional.
- One receipt header has many item rows.
- Product search must be scoped by selected `warehouse_id`.
- Product search must use active `inv_warehouse_products`.
- Supplier suggestions read from `inv_supplier_products`.
- Controllers stay thin.
- Multi-table writes belong in service/usecase/action and must use `DB::transaction()`.
- Do not post stock transactions, stock balances, or ecommerce product quantity unless the task explicitly defines posting rules.

---

## 11. Supplier Domain Rules

Supplier-owned code belongs under:

```txt
src/Domains/Supplier
```

Required structure:

```txt
Actions/
DTO/
Forms/
Http/Controllers/
Http/Requests/
Models/
Providers/
Repositories/Eloquent/
Repositories/Interfaces/
Services/
Tables/
Usecase/
```

Known files:

```txt
Forms/SupplierForm.php
Http/Controllers/SupplierController.php
Http/Requests/SupplierRequest.php
Models/Supplier*.php
Providers/SupplierProvider.php
Services/SupplierService.php
Tables/SupplierTable.php
```

Rules:

- Do not recreate old root Supplier classes under:
  - `src/Forms`
  - `src/Http`
  - `src/Models`
  - `src/Services`
  - `src/Tables`
  - `src/Repositories`
- Controllers stay thin.
- Multi-table writes belong in `SupplierService` or a usecase/action and must use `DB::transaction()`.
- Render supplier enum statuses with labels/badges, not raw values.

---

## 12. Provider Rules

`InventoryServiceProvider` owns shared bootstrapping only:

- helpers
- config
- translations
- routes
- views
- migrations
- root inventory parent menu
- middleware alias registration
- domain provider registration

Register domain providers like:

```php
$this->app->register(SupplierProvider::class);
$this->app->register(GoodsReceiptProvider::class);
$this->app->register(WarehouseStaffProvider::class);
$this->app->register(WarehouseProvider::class);
```

Rules:

- Domain-specific menus belong in the domain provider.
- Domain-specific repository bindings belong in the domain provider.
- Do not put domain-specific CRUD menu registration in `InventoryServiceProvider`.
- If a new domain is added, add its provider registration to `InventoryServiceProvider`.

---

## 13. Route Rules

Routes live in:

```txt
routes/web.php
```

Routes must point to domain controller namespaces.

Use grouped routes with explicit:

```txt
prefix
as
uses
permission
```

Rules:

- Put literal routes before parameter routes.
- Example: put `products/search` before `/{warehouse}`.
- The inventory route group uses `inventory.context` middleware.
- Keep `inventory.context` middleware when adding admin inventory routes that depend on warehouse scope.
- Keep route names aligned with table/menu/config permissions.

---

## 14. Migration Rules

Migrations live in the plugin migration folder.

Rules:

- Use clear table names with `inv_` prefix.
- Use short explicit index/foreign key names when MySQL identifier length can be an issue.
- Do not create duplicate tables for the same concept.
- When adding nullable columns involved in unique business dimensions, ensure service logic handles nullable uniqueness correctly.
- Do not run destructive migrations unless explicitly requested.
- If a field is legacy but still used, do not drop it casually.
- If dropping/renaming a field, explain data impact and provide safe migration strategy.

---

## 15. Cleanup Rules

Do not place domain files in the wrong domain.

Examples:

- Do not place WarehouseStaff repositories under `Domains/Warehouse`.
- Do not place Supplier services under root `src/Services`.
- Do not place GoodsReceipt controllers under root `src/Http/Controllers`.

If a file under one domain declares another domain namespace, treat it as a cleanup target in a dedicated task.

Example:

```txt
A file under Domains/Warehouse declares namespace Botble\Inventory\Domains\WarehouseStaff
```

This is a cleanup issue.

---

## 16. Code Style Rules

- Keep controllers thin.
- Keep service/usecase/action responsibilities clear.
- Do not duplicate business logic across controllers and services.
- Use `DB::transaction()` for multi-table writes.
- Use repositories for reusable queries.
- Use DTOs to normalize request payloads.
- Use requests for validation.
- Use forms for Botble admin form rendering.
- Use tables for Botble admin table rendering.
- Use providers for domain menu/bindings.
- Avoid direct `DB::table()` in Forms when a model/service/repository exists.
- Do not introduce unrelated refactors while fixing a specific task.
- Preserve existing working behavior unless the task asks to change it.

---

## 17. Focused Validation Checklist

Run focused checks after changes.

### WarehouseStaff checks

```powershell
php -l platform/plugins/inventory/src/Domains/WarehouseStaff/Providers/WarehouseStaffProvider.php
php -l platform/plugins/inventory/src/Domains/WarehouseStaff/Http/Controllers/WarehouseStaffController.php
php artisan route:list --name=inventory.warehouse-staff
php artisan route:list --name=inventory.warehouse-positions
```

### If routes/providers changed

```powershell
php artisan route:list --name=inventory
```

### If migrations changed

```powershell
php artisan migrate:status --path=platform/plugins/inventory/database/migrations
```

### Broader PHP lint

```powershell
Get-ChildItem -Path 'platform/plugins/inventory/src' -Recurse -Filter '*.php' | ForEach-Object { php -l $_.FullName }
```

Do not include:

```txt
platform/plugins/inventory/backup.php
```

in source lint because it is not runtime code.

---

## 18. Finish Criteria

Before final response, confirm:

- Domain files are in the correct folder.
- Mandatory domain folders were respected:
  - `Actions/`
  - `DTO/`
  - `Forms/`
  - `Http/Controllers/`
  - `Http/Requests/`
  - `Models/`
  - `Providers/`
  - `Repositories/Eloquent/`
  - `Repositories/Interfaces/`
  - `Services/`
  - `Tables/`
  - `Usecase/`
- Routes point to domain controllers.
- Provider registration and domain provider bindings are correct.
- Permission flags are aligned between routes, tables, menus, and config.
- Warehouse-scoped queries respect `inventory.context` when needed.
- PHP lint passes for touched PHP files.
- Route list or Laravel boot checks pass when routes/providers changed.
- Migrations are listed/checked when migrations changed.
- Any skipped checks are reported with a reason.

---

## 19. Final Response Format

When finished, respond with:

```txt
1. Đã làm gì
2. File đã sửa / tạo mới
3. Route / permission / provider có thay đổi không
4. Migration có thay đổi không
5. Đã chạy check/lint gì
6. Còn gì chưa làm hoặc cần review thủ công
```

Do not claim checks passed if they were not run.
