# Inventory Plugin Development Guide

This guide is for developers maintaining `platform/plugins/inventory`.

## Architecture Principle

Inventory is organized by domain. Put feature-specific code inside `src/Domains/<Domain>`. Keep root `src/` folders for shared plugin infrastructure only.

Current domains:

- `GoodsReceipt`
- `Supplier`
- `Warehouse`
- `WarehouseStaff`

Root plugin code should stay small:

- `src/Providers/InventoryServiceProvider.php`
- shared enums in `src/Enums`
- shared root inventory model/table/form/controller where still used
- shared helpers/support code when it is truly cross-domain

## Supplier Domain Structure

Supplier code belongs here:

```txt
src/Domains/Supplier/
  Forms/SupplierForm.php
  Http/Controllers/SupplierController.php
  Http/Requests/SupplierRequest.php
  Models/Supplier.php
  Models/SupplierAddress.php
  Models/SupplierApproval.php
  Models/SupplierBank.php
  Models/SupplierContact.php
  Models/SupplierProduct.php
  Providers/SupplierProvider.php
  Services/SupplierService.php
  Tables/SupplierTable.php
```

Do not add these old root files back:

```txt
src/Forms/SupplierForm.php
src/Http/Controllers/SupplierController.php
src/Http/Requests/SupplierRequest.php
src/Models/Supplier.php
src/Models/SupplierAddress.php
src/Models/SupplierApproval.php
src/Models/SupplierBank.php
src/Models/SupplierContact.php
src/Models/SupplierProduct.php
src/Services/SupplierService.php
src/Tables/SupplierTable.php
src/Repositories/Eloquent/Supplier*.php
src/Repositories/Interfaces/Supplier*.php
```

The repository classes above were part of the old root layout. The current Supplier service uses Eloquent models directly.

## Providers

`InventoryServiceProvider` should load shared plugin resources and register domain providers:

```php
use Botble\Inventory\Domains\Supplier\Providers\SupplierProvider;
use Botble\Inventory\Domains\GoodsReceipt\Providers\GoodsReceiptProvider;
use Botble\Inventory\Domains\Warehouse\Providers\WarehouseProvider;
use Botble\Inventory\Domains\WarehouseStaff\Providers\WarehouseStaffProvider;

$this->app->register(SupplierProvider::class);
$this->app->register(GoodsReceiptProvider::class);
$this->app->register(WarehouseStaffProvider::class);
$this->app->register(WarehouseProvider::class);
```

Domain menus should live in domain providers:

- supplier menu: `Domains/Supplier/Providers/SupplierProvider.php`
- goods receipt menu: `Domains/GoodsReceipt/Providers/GoodsReceiptProvider.php`
- warehouse menu: `Domains/Warehouse/Providers/WarehouseProvider.php`
- warehouse staff menu: `Domains/WarehouseStaff/Providers/WarehouseStaffProvider.php`

Keep the root inventory parent menu in `InventoryServiceProvider`.

## Routes

Routes stay in `routes/web.php`.

Use domain controller imports:

```php
use Botble\Inventory\Domains\Supplier\Http\Controllers\SupplierController;
```

Supplier routes use the `inventory.suppliers.*` names and explicit permission keys. Put specific routes such as `products/search` before dynamic routes such as `/{supplier}`.

The edit form uses method spoofing with `PUT`, so the update route must allow `POST|PUT`:

```php
Route::match(['POST', 'PUT'], '/edit/{supplier}', [
    'uses' => SupplierController::class . '@update',
    'as' => 'update',
    'permission' => 'inventory.suppliers.edit',
]);
```

## Supplier Write Flow

Controller methods should stay thin:

- permission check
- page title / response
- delegate business work to `SupplierService`

`SupplierService` owns:

- creating suppliers
- updating suppliers
- syncing contacts, addresses, banks, and supplied products
- submit for approval
- approve/reject
- approval log creation
- pending approval notifications

Use `DB::transaction()` for write flows that touch more than one table.

## Goods Receipt Write Flow

Goods receipt code belongs under `src/Domains/GoodsReceipt`.

The domain currently owns:

- `GoodsReceipt` header model for `inv_goods_receipts`
- `GoodsReceiptItem` line model for `inv_goods_receipt_items`
- batch/stock models for the schema created by the receipt/stock migration
- `GoodsReceiptService` for creating/updating headers and item rows
- product search and supplier product suggestion endpoints

Create/update must remain transactional because one receipt header has many item rows. The service computes subtotal, discount, tax, and total from line rows. Supplier product suggestions should read from `inv_supplier_products` and prefill product, supplier price, MOQ, and lead time note.

Goods receipt item selection is scoped by warehouse. Products must be configured as active rows in `inv_warehouse_products` before they can be added to a receipt for that warehouse.

Do not silently post inventory movements when a receipt is marked `completed` unless a task defines the posting rules. Posting should update `inv_stock_transactions`, `inv_stock_balances`, and possibly `ec_products.quantity` together.

## Warehouse Product Configuration

Warehouse product configuration belongs under `src/Domains/Warehouse`.

The required bridge table is `inv_warehouse_products`. It answers: which products or variations are allowed to be managed in which warehouse.

Important files:

- `Models/WarehouseProduct.php`
- `Http/Controllers/WarehouseProductController.php`
- `Http/Requests/WarehouseProductRequest.php`
- `Services/WarehouseProductService.php`
- `resources/views/warehouse/show.blade.php`

Validation rules:

- route warehouse must exist
- product must exist
- variation must belong to the selected product when provided
- default location must belong to the route warehouse
- supplier product must match supplier and product
- duplicate warehouse/product/variation combinations are blocked in service because MySQL unique indexes allow repeated nullable values

Removal rule:

- if there is no goods receipt, stock transaction, or stock balance history, the row can be deleted
- if history exists, update `is_active` to false instead of deleting so reports remain stable

## Supplier Models

`Supplier` uses UUID IDs and casts:

- `status` -> `SupplierStatusEnum`
- `type` -> `SupplierTypeEnum`
- `metadata` -> array
- audit timestamps -> datetime
- `requires_reapproval` -> bool

Important relationships:

- `contacts()`
- `addresses()`
- `banks()`
- `supplierProducts()`
- `products()`
- `approvals()`
- `creator()`
- `submitter()`
- `approver()`

When adding list/detail UI, eager-load relationships instead of relying on lazy loading.

## Supplier Table

The supplier list should show operational fields, not only name/status/date:

- supplier code
- supplier name
- supplier type
- tax code
- primary contact
- supplied product count
- status badge
- created date

Use `FormattedColumn` for computed values and badge rendering. Use `withCount('supplierProducts')` and eager-load primary contacts in the query.

Computed columns that do not map to SQL columns should be:

```php
->orderable(false)
->searchable(false)
```

Render native enum statuses with `toHtml()` so users see labels/badges instead of raw values like `pending_approval`.

## Forms And Requests

`SupplierForm` should use Botble form fields and field options:

- `TextFieldOption`
- `SelectFieldOption`
- `TextareaFieldOption`
- `NameFieldOption`

Use `defaultValue()` for create defaults. Avoid hard-coded `selected` values that override existing model values on edit.

`SupplierRequest` should validate:

- unique supplier code, ignoring the current supplier on update
- supplier type enum
- supplier status enum
- contacts
- addresses
- banks
- supplied products

Non-super-users should not freely choose arbitrary supplier status.

## Approval Flow

Valid supplier statuses are defined in `SupplierStatusEnum`:

- `draft`
- `pending_approval`
- `active`
- `inactive`
- `blacklisted`
- `rejected`

There is no `approved` enum case. Approval should set status to `active`.

Approval actions should create records in `inv_supplier_approvals`.

## Migrations

Supplier migrations are under `database/migrations` and create:

- `inv_suppliers`
- `inv_supplier_approvals`
- `inv_supplier_contacts`
- `inv_supplier_addresses`
- `inv_supplier_banks`
- `inv_supplier_products`

Before debugging runtime supplier errors, check migration status:

```powershell
php artisan migrate:status --path=platform/plugins/inventory/database/migrations
```

## Cleanup

`backup.php` in the plugin root is not runtime code. It currently contains diff-style backup content and can fail PHP lint. Do not include it in automated PHP lint/CI. Prefer removing it before commit or moving it outside the plugin.

When moving a feature into `Domains/<Domain>`, remove old root copies and update all namespaces/routes/provider imports.

## Verification Commands

Run touched-file lint:

```powershell
php -l platform/plugins/inventory/src/Domains/Supplier/Http/Controllers/SupplierController.php
php -l platform/plugins/inventory/src/Domains/Supplier/Services/SupplierService.php
php -l platform/plugins/inventory/src/Domains/Supplier/Tables/SupplierTable.php
php -l platform/plugins/inventory/src/Providers/InventoryServiceProvider.php
```

Run full plugin source lint:

```powershell
Get-ChildItem -Path 'platform/plugins/inventory/src' -Recurse -Filter '*.php' | ForEach-Object { php -l $_.FullName }
```

Check routes:

```powershell
php artisan route:list --name=inventory.suppliers
```

Check Laravel boot/autoload for a class:

```powershell
php -r "require 'vendor/autoload.php'; `$app = require 'bootstrap/app.php'; `$kernel = `$app->make('Illuminate\\Contracts\\Console\\Kernel'); `$kernel->bootstrap(); echo class_exists('Botble\\Inventory\\Domains\\Supplier\\Providers\\SupplierProvider') ? 'ok' : 'missing';"
```

## Review Checklist

Before finishing a Supplier change:

- Supplier code is only in `Domains/Supplier`.
- Old root Supplier classes are not referenced.
- `SupplierProvider` is registered from `InventoryServiceProvider`.
- Supplier routes point to `Domains\Supplier\Http\Controllers\SupplierController`.
- Permissions match `config/permissions.php`.
- Multi-table writes are transactional.
- Supplier table computed columns are not accidentally SQL-searchable/orderable.
- Status display uses labels/badges.
- PHP lint and route checks pass.
