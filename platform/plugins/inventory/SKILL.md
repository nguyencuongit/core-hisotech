---
name: inventory-plugin
description: Work on the Botble Inventory plugin at platform/plugins/inventory. Use when editing inventory domains such as Supplier, GoodsReceipt, Warehouse, WarehouseStaff, routes, providers, forms, tables, requests, models, services, migrations, permissions, or admin UI behavior. Follow the domain-first structure, register domain providers from InventoryServiceProvider, keep Supplier code under Domains/Supplier, and validate Laravel/PHP behavior before finishing.
---

# Inventory Plugin

## Core Workflow

1. Read the touched domain files before editing.
2. Keep feature code inside its domain folder.
3. Keep root `src/` folders only for shared plugin infrastructure.
4. Register each domain provider from `src/Providers/InventoryServiceProvider.php`.
5. Run targeted PHP lint and route/boot checks before finishing.

Read [DEVELOPMENT_GUIDE.md](DEVELOPMENT_GUIDE.md) when the task needs broader architecture context, migration guidance, or a checklist for future maintainers.

## Domain Layout

Use this structure for domain-owned features:

```txt
platform/plugins/inventory/src/Domains/<Domain>/
  Forms/
  Http/Controllers/
  Http/Requests/
  Models/
  Providers/
  Services/
  Tables/
```

Add `Repositories/`, `Actions/`, `DTO/`, or `UseCases/` only when the domain actually needs them.

## Goods Receipt Rules

Goods receipt is a domain module for warehouse receiving. Keep its code here:

```txt
src/Domains/GoodsReceipt/
  Http/Controllers/GoodsReceiptController.php
  Http/Requests/GoodsReceiptRequest.php
  Models/GoodsReceipt*.php
  Providers/GoodsReceiptProvider.php
  Services/GoodsReceiptService.php
  Tables/GoodsReceiptTable.php
```

Use `inv_goods_receipts` as the document header table and `inv_goods_receipt_items` as line items. The create/edit flow should let admins select supplier, warehouse, receipt date, status, reference code, note, discounts/tax, and product rows.

For product rows:

- Product search must be scoped by selected `warehouse_id` and only return active rows configured in `inv_warehouse_products`.
- Supplier suggestions read from `inv_supplier_products` and can prefill product, supplier price, MOQ, and lead time note.
- Multi-row saves belong in `GoodsReceiptService` and must use `DB::transaction()`.
- Do not update stock balances or ecommerce product quantity unless the task explicitly defines the completion/posting rules.

## Warehouse Product Rules

Warehouse products belong inside `Domains/Warehouse` because this is warehouse configuration, not receiving.

Use:

```txt
src/Domains/Warehouse/
  Models/WarehouseProduct.php
  Http/Controllers/WarehouseProductController.php
  Http/Requests/WarehouseProductRequest.php
  Services/WarehouseProductService.php
```

`inv_warehouse_products` is the required bridge table between warehouses and ecommerce products. It defines which products are allowed to appear in warehouse workflows.

Backend validation must check:

- warehouse exists, usually via route model binding
- product exists
- optional product variation exists and belongs to the selected product
- optional default location belongs to the selected warehouse
- optional supplier product matches supplier and product
- no duplicate warehouse/product/variation combination, including the nullable variation case

When removing a warehouse product, delete it only if it has no stock/receipt history. If it has been used, set `is_active = false` instead.

## Supplier Rules

Supplier is a domain module. Keep Supplier-specific code here:

```txt
src/Domains/Supplier/
  Forms/SupplierForm.php
  Http/Controllers/SupplierController.php
  Http/Requests/SupplierRequest.php
  Models/Supplier*.php
  Providers/SupplierProvider.php
  Services/SupplierService.php
  Tables/SupplierTable.php
```

Do not recreate old root Supplier files under:

```txt
src/Forms/SupplierForm.php
src/Http/Controllers/SupplierController.php
src/Http/Requests/SupplierRequest.php
src/Models/Supplier*.php
src/Services/SupplierService.php
src/Tables/SupplierTable.php
src/Repositories/*Supplier*
```

Current Supplier persistence uses Eloquent directly inside `Domains/Supplier/Services/SupplierService.php`. Do not add root Supplier repositories/interfaces unless a new shared cross-domain requirement justifies it.

## Provider Rules

`InventoryServiceProvider` owns shared plugin bootstrapping only:

- namespace/config/translations/routes/views/migrations
- root inventory menu item
- language module registration
- domain provider registration

Register domain providers like this:

```php
$this->app->register(SupplierProvider::class);
$this->app->register(GoodsReceiptProvider::class);
$this->app->register(WarehouseStaffProvider::class);
$this->app->register(WarehouseProvider::class);
```

Domain-specific menu items belong in their own provider, e.g. `Domains/Supplier/Providers/SupplierProvider.php`.

## Route Rules

Routes live in `routes/web.php` and must point to domain controller namespaces.

Use grouped routes with explicit `prefix`, `as`, `uses`, and `permission` keys:

```php
Route::group([
    'prefix' => 'suppliers',
    'as' => 'suppliers.',
], function () {
    Route::match(['GET', 'POST'], '/', [
        'uses' => SupplierController::class . '@index',
        'as' => 'index',
        'permission' => 'inventory.suppliers.index',
    ]);
});
```

Put literal/specific routes before parameter routes. Example: define `products/search` before `/{supplier}`.

## Supplier Implementation Rules

- Keep controllers thin; put write orchestration in `SupplierService`.
- Wrap multi-table writes in `DB::transaction()`.
- Validate supplier input in `SupplierRequest`.
- Cast supplier `status` and `type` to `SupplierStatusEnum` and `SupplierTypeEnum`.
- Render table status with `toHtml()` or a formatted column, not raw enum values.
- Use eager loading and `withCount()` for Supplier table columns to avoid N+1 queries.
- Mark computed table columns `orderable(false)` and `searchable(false)` when they do not map to real SQL columns.
- Keep approval actions in the service: submit, approve, reject, approval log creation, and notification dispatch.

## Current Supplier Table Standard

The supplier list should show enough operational context:

- supplier code
- supplier name
- supplier type
- tax code
- primary contact
- supplied product count
- status badge
- created date

Use `FormattedColumn` for computed values and badges.

## Cleanup Rules

- Delete old root Supplier classes after moving them into `Domains/Supplier`.
- Do not keep duplicate Supplier implementations in root and domain folders.
- `backup.php` is not runtime code. Remove it or keep it outside lint/commit paths before final delivery.
- Keep unrelated dirty worktree changes untouched.

## Validation Checklist

Run focused checks after changes:

```powershell
php -l platform/plugins/inventory/src/Domains/Supplier/Tables/SupplierTable.php
php -l platform/plugins/inventory/src/Providers/InventoryServiceProvider.php
php artisan route:list --name=inventory.suppliers
```

For broader changes:

```powershell
Get-ChildItem -Path 'platform/plugins/inventory/src' -Recurse -Filter '*.php' | ForEach-Object { php -l $_.FullName }
php artisan migrate:status --path=platform/plugins/inventory/database/migrations
```

When boot/autoload matters, bootstrap Laravel and check the class:

```powershell
php -r "require 'vendor/autoload.php'; `$app = require 'bootstrap/app.php'; `$kernel = `$app->make('Illuminate\\Contracts\\Console\\Kernel'); `$kernel->bootstrap(); echo class_exists('Botble\\Inventory\\Domains\\Supplier\\Providers\\SupplierProvider') ? 'ok' : 'missing';"
```

## Finish Criteria

Before final response, confirm:

- Domain files are in the correct folder.
- Routes point to domain controllers.
- Provider registration is correct.
- No old root Supplier namespace references remain.
- PHP lint passes for touched files.
- Route list or Laravel boot checks pass when routes/providers changed.
