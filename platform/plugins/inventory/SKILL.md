---
name: inventory-plugin
description: Work on the Botble Inventory plugin at platform/plugins/inventory. Use when editing Inventory domain modules such as WarehouseStaff, Warehouse, Supplier, GoodsReceipt, routes, providers, permissions, forms, tables, requests, migrations, or admin UI behavior. Follow the domain-first structure, register domain providers from InventoryServiceProvider, and validate PHP/Laravel behavior before finishing.
---

# Inventory Plugin

## Start Every Task

1. Read the touched domain folder before editing.
2. Read related `routes/web.php`, `config/permissions.php`, the domain provider, and `src/Providers/InventoryServiceProvider.php` when routes, menus, permissions, middleware, or bindings can be affected.
3. Keep feature code inside `src/Domains/<Domain>`.
4. Keep root `src/` folders for shared plugin infrastructure only.
5. Run focused PHP lint and route/boot checks before finishing.

Read `DEVELOPMENT_GUIDE.md` for broader architecture notes, WarehouseStaff flow details, migration guidance, and review checklists.

## Domain Layout

Use this shape for domain-owned code:

```txt
src/Domains/<Domain>/
  Forms/
  Http/Controllers/
  Http/Requests/
  Models/
  Providers/
  Tables/
```

Add `Services/`, `Repositories/`, `Usecase/`, `Actions/`, or `DTO/` only when the domain actually uses that pattern. Keep the existing folder spelling when extending a domain; `WarehouseStaff` currently uses `Usecase/AssignmentsUsercase.php`.

## WarehouseStaff Standard

`WarehouseStaff` owns staff, positions, and staff-to-warehouse assignments.

Keep these files under `src/Domains/WarehouseStaff`:

```txt
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

WarehouseStaff behavior rules:

- `WarehouseStaffProvider` registers the staff/position admin menu and binds `WarehouseStaffAssignmentInterface` to `WarehouseStaffAssignmentRepository`.
- `InventoryServiceProvider` registers `WarehouseStaffProvider::class`; do not move staff menus back to the root provider.
- `WarehouseStaffController` saves the Botble form, then syncs warehouse assignments through `AssignmentsUsercase` inside `DB::transaction()`.
- `AssignmentsUsercase` is the current assignment orchestration point. Keep repository details out of controllers when adding assignment behavior.
- `WarehouseStaffAssignments` uses `inv_warehouse_staff_assignments` with unique `staff_id + warehouse_id`.
- Treat staff-to-warehouse as many-to-many through `assignments()`. Do not rely on `WarehouseStaff::warehouse()` for access checks or lists.
- `WarehouseStaffTable` must eager-load `assignments.warehouse` and scope non-super-admin users with `inventory_warehouse_ids()`.
- `InventoryContextMiddleware` maps the logged-in user to `WarehouseStaff`, then to assigned warehouse IDs. `super_user === 1` is unscoped.
- `UserWarehouse` and `inv_user_warehouses` exist, but the current context flow uses `WarehouseStaffAssignments`. Do not switch flows without an explicit task.

Route and permission rules:

- Route names are `inventory.warehouse-staff.*` and `inventory.warehouse-positions.*`.
- Permission flags for this domain should be consistent and bare: `warehouse-staff.index/create/edit/destroy` and `warehouse-positions.index/create/edit/destroy`.
- Keep route permissions, table action permissions, menu permissions, and `config/permissions.php` aligned.
- Do not mix `warehouse-positions.delete` with `warehouse-positions.destroy`.
- Do not mix `inventory.warehouse-staff.*` permission flags into WarehouseStaff unless the full permission set is migrated.

Validation rules:

- `staff_code` must be unique in `inv_warehouse_staff`, ignoring the current route model on update.
- `warehouse_id` is required and must be an array.
- When improving staff validation, verify every warehouse exists and `position` exists in `inv_warehouse_positions`.
- `code` must be unique in `inv_warehouse_positions`, ignoring the current route model on update.

Cleanup rules:

- Do not place WarehouseStaff repositories or assignment usecases under `Domains/Warehouse`.
- If a file under `Domains/Warehouse` declares a `Botble\Inventory\Domains\WarehouseStaff` namespace, treat it as a cleanup target in a dedicated task.
- If editing Vietnamese labels, write valid UTF-8 or use translation keys. Do not preserve mojibake labels.

## Warehouse Rules

Warehouse-owned code belongs under `src/Domains/Warehouse`.

Warehouse product configuration uses `inv_warehouse_products` and defines which ecommerce products or variations are allowed in a warehouse. Keep this code in Warehouse:

```txt
Models/WarehouseProduct.php
Http/Controllers/WarehouseProductController.php
Http/Requests/WarehouseProductRequest.php
Services/WarehouseProductService.php
```

Validate warehouse products by checking product existence, variation ownership, default location ownership, supplier product consistency, and duplicate warehouse/product/variation combinations including the nullable variation case.

If a warehouse product has stock, receipt, or balance history, deactivate it instead of deleting it.

## GoodsReceipt Rules

GoodsReceipt owns warehouse receiving:

```txt
src/Domains/GoodsReceipt/
  Http/Controllers/GoodsReceiptController.php
  Http/Requests/GoodsReceiptRequest.php
  Models/GoodsReceipt*.php
  Providers/GoodsReceiptProvider.php
  Services/GoodsReceiptService.php
  Tables/GoodsReceiptTable.php
```

Create and update flows must be transactional because one receipt header has many item rows. Product search must be scoped by selected `warehouse_id` and active `inv_warehouse_products`. Supplier suggestions read from `inv_supplier_products`.

Do not post stock transactions, stock balances, or ecommerce product quantity unless the task explicitly defines posting rules.

## Supplier Rules

Supplier code belongs under `src/Domains/Supplier`:

```txt
Forms/SupplierForm.php
Http/Controllers/SupplierController.php
Http/Requests/SupplierRequest.php
Models/Supplier*.php
Providers/SupplierProvider.php
Services/SupplierService.php
Tables/SupplierTable.php
```

Do not recreate old root Supplier classes under `src/Forms`, `src/Http`, `src/Models`, `src/Services`, `src/Tables`, or `src/Repositories`.

Controllers stay thin. Multi-table writes belong in `SupplierService` and must use `DB::transaction()`. Render supplier enum statuses with labels/badges, not raw values.

## Provider Rules

`InventoryServiceProvider` owns shared bootstrapping only:

- helpers, config, translations, routes, views, migrations
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

Domain-specific menus and bindings belong in the domain provider.

## Route Rules

Routes live in `routes/web.php` and point to domain controller namespaces.

Use grouped routes with explicit `prefix`, `as`, `uses`, and `permission` keys. Put literal routes like `products/search` before parameter routes like `/{supplier}` or `/{warehouse}`.

The inventory route group uses the `inventory.context` middleware. Keep that middleware when adding admin inventory routes that depend on warehouse scope.

## Validation Checklist

Run focused checks after changes:

```powershell
php -l platform/plugins/inventory/src/Domains/WarehouseStaff/Providers/WarehouseStaffProvider.php
php -l platform/plugins/inventory/src/Domains/WarehouseStaff/Http/Controllers/WarehouseStaffController.php
php artisan route:list --name=inventory.warehouse-staff
php artisan route:list --name=inventory.warehouse-positions
```

For broader changes:

```powershell
Get-ChildItem -Path 'platform/plugins/inventory/src' -Recurse -Filter '*.php' | ForEach-Object { php -l $_.FullName }
php artisan migrate:status --path=platform/plugins/inventory/database/migrations
```

Do not include `platform/plugins/inventory/backup.php` in source lint; it is not runtime code.

## Finish Criteria

Before final response, confirm:

- Domain files are in the correct folder.
- Routes point to domain controllers.
- Provider registration and domain provider bindings are correct.
- Permission flags are aligned between routes, tables, menus, and config.
- Warehouse-scoped queries respect `inventory.context` when needed.
- PHP lint passes for touched PHP files.
- Route list or Laravel boot checks pass when routes/providers changed.
