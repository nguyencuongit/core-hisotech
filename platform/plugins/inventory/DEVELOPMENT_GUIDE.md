# Inventory Plugin Development Guide

This guide is for developers and agents maintaining `platform/plugins/inventory`.

## Architecture Principle

Inventory is organized by domain. Put feature-specific code inside `src/Domains/<Domain>`. Keep root `src/` folders for shared plugin infrastructure only.

Current domains:

- `WarehouseStaff`
- `Warehouse`
- `Supplier`
- `GoodsReceipt`

Root plugin code should stay small:

- `src/Providers/InventoryServiceProvider.php`
- `src/Http/Middleware/InventoryContextMiddleware.php`
- shared helpers in `src/Helpers`
- shared context/support code in `src/Support`
- shared enums in `src/Enums`
- legacy root inventory model/form/table/controller where still used

## Provider Standard

`InventoryServiceProvider` loads shared plugin resources and registers domain providers:

```php
use Botble\Inventory\Domains\GoodsReceipt\Providers\GoodsReceiptProvider;
use Botble\Inventory\Domains\Supplier\Providers\SupplierProvider;
use Botble\Inventory\Domains\Warehouse\Providers\WarehouseProvider;
use Botble\Inventory\Domains\WarehouseStaff\Providers\WarehouseStaffProvider;

$this->app->register(SupplierProvider::class);
$this->app->register(GoodsReceiptProvider::class);
$this->app->register(WarehouseStaffProvider::class);
$this->app->register(WarehouseProvider::class);
```

The root provider should own:

- namespace/config/translation/route/view/migration loading
- the root inventory parent menu
- `InventoryContext` singleton
- `inventory.context` middleware alias
- domain provider registration

Domain providers own domain-specific menu items, repository bindings, and domain boot logic.

## Routes And Permissions

Routes stay in `routes/web.php` inside the admin route group:

```php
Route::group([
    'prefix' => 'inventories',
    'as' => 'inventory.',
    'middleware' => ['web', 'core', 'auth', 'inventory.context'],
], function () {
    // domain route groups
});
```

Use domain controller imports, explicit route names, and explicit permission keys:

```php
use Botble\Inventory\Domains\WarehouseStaff\Http\Controllers\WarehouseStaffController;
```

Keep route permissions, table action permissions, menu permissions, and `config/permissions.php` aligned. Do not mix prefixed and unprefixed flags inside one domain unless you migrate the whole domain.

Put literal routes before parameter routes. Example: `products/search` must be defined before `/{supplier}`.

## WarehouseStaff Domain Standard

`WarehouseStaff` owns warehouse staff, warehouse positions, and staff-to-warehouse assignment behavior.

Important files:

```txt
src/Domains/WarehouseStaff/
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

Use the existing folder/class spelling when extending this domain. The current code uses `Usecase` and `AssignmentsUsercase`.

### WarehouseStaff Tables

Migrations currently create:

- `inv_warehouse_positions`
- `inv_warehouse_staff`
- `inv_warehouse_staff_assignments`
- `inv_user_warehouses`

`inv_warehouse_positions` stores warehouse roles:

- `code`
- `name`
- `level`
- `is_active`

`inv_warehouse_staff` stores staff profiles:

- `user_id`
- `staff_code`
- `full_name`
- `phone`
- `email`
- `status`

`inv_warehouse_staff_assignments` stores staff access to warehouses:

- `staff_id`
- `warehouse_id`
- `position_id`
- `is_primary`
- `status`
- `start_date`
- `end_date`
- unique `staff_id + warehouse_id`

`inv_user_warehouses` exists, but the current warehouse-scope middleware uses `inv_warehouse_staff_assignments`, not `inv_user_warehouses`.

### WarehouseStaff Provider

`WarehouseStaffProvider` should:

- bind `WarehouseStaffAssignmentInterface` to `WarehouseStaffAssignmentRepository`
- pass a `WarehouseStaffAssignments` model instance into that repository
- register the Warehouse Staff admin menu item
- register the Warehouse Positions admin menu item

Keep these menus in `Domains/WarehouseStaff/Providers/WarehouseStaffProvider.php`. Do not move them back into `InventoryServiceProvider`.

### Staff Write Flow

`WarehouseStaffController` is the current write coordinator:

1. Create or update the staff model through `WarehouseStaffForm`.
2. Normalize selected `warehouse_id[]` values from the request.
3. Call `AssignmentsUsercase::updateWarehouseId()`.
4. Wrap the staff save and assignment sync in `DB::transaction()`.

`AssignmentsUsercase` owns assignment sync:

1. Delete assignments for warehouses no longer selected.
2. `firstOrNew()` each `staff_id + warehouse_id`.
3. Set `start_date` when the assignment is new.
4. Set `position_id`.
5. Save the assignment.

Keep repository details out of controllers when adding assignment behavior.

### Position Write Flow

`WarehousePositionController` uses the standard Botble form flow:

1. Validate with `WarehousePositionRequest`.
2. Save through `WarehousePositionForm`.
3. Return Botble HTTP response with previous/next URLs.

Delete actions use `DeleteResourceAction`.

### WarehouseStaff Forms

`WarehouseStaffForm` currently loads:

- system users from `users`
- warehouses from `Domains/Warehouse/Models/Warehouse`
- positions from `WarehousePosition`
- selected warehouses through `assignments()`

The multiple warehouse select is named `warehouse_id[]`. Current controller code expects a nested array and extracts `$item[0]`. If you refactor this, normalize request input in one small helper and keep backward compatibility with the submitted form shape.

Use translation keys or valid UTF-8 labels when editing labels. Do not preserve mojibake text.

### WarehouseStaff Validation

`WarehouseStaffRequest` should validate:

- `full_name` required string max 220
- `phone` required string max 220
- `email` required string max 220
- `staff_code` unique in `inv_warehouse_staff`, ignoring the current route model on update
- `warehouse_id` required array

When improving validation, also verify:

- every selected warehouse exists
- selected `position` exists in `inv_warehouse_positions`
- optional `user_id` exists in `users`
- email has email format if the business wants real email validation

`WarehousePositionRequest` should validate:

- `name` required string max 220
- `code` unique in `inv_warehouse_positions`, ignoring the current route model on update
- `level` integer between 0 and 100

### Warehouse Scope Context

Inventory admin routes use `inventory.context`.

`InventoryContextMiddleware`:

1. Resets context warehouse IDs and super-admin flag.
2. Marks users with `super_user === 1` as super admin.
3. Finds the current user's `WarehouseStaff` record by `user_id`.
4. Loads assigned warehouse IDs from `WarehouseStaffAssignments`.
5. Stores those IDs in `InventoryContext`.

Shared helpers:

- `inventory_context()`
- `inventory_warehouse_ids()`
- `inventory_is_super_admin()`

Use those helpers for warehouse-scoped admin queries. For example, `WarehouseStaffTable` scopes non-super-admin users by `assignments.warehouse_id`.

### WarehouseStaff Tables

`WarehouseStaffTable` should:

- use `WarehouseStaff::class`
- eager-load `assignments.warehouse`
- show assigned warehouse names through a non-orderable, non-searchable formatted column
- scope non-super-admin users by `inventory_warehouse_ids()`
- use route names under `inventory.warehouse-staff.*`

`WarehousePositionTable` should:

- use `WarehousePosition::class`
- show active/inactive status consistently
- use route names under `inventory.warehouse-positions.*`

### WarehouseStaff Routes And Permission Flags

Canonical route names:

- `inventory.warehouse-staff.index`
- `inventory.warehouse-staff.create`
- `inventory.warehouse-staff.store`
- `inventory.warehouse-staff.edit`
- `inventory.warehouse-staff.update`
- `inventory.warehouse-staff.destroy`
- `inventory.warehouse-positions.index`
- `inventory.warehouse-positions.create`
- `inventory.warehouse-positions.store`
- `inventory.warehouse-positions.edit`
- `inventory.warehouse-positions.update`
- `inventory.warehouse-positions.destroy`

Canonical permission flags for this domain:

- `warehouse-staff.index`
- `warehouse-staff.create`
- `warehouse-staff.edit`
- `warehouse-staff.destroy`
- `warehouse-positions.index`
- `warehouse-positions.create`
- `warehouse-positions.edit`
- `warehouse-positions.destroy`

When touching this domain, align all of these places:

- route `permission` keys
- table header/action/bulk permissions
- provider menu permissions
- `config/permissions.php`

Avoid mismatches such as:

- `warehouse-positions.delete` in routes while tables/config use `warehouse-positions.destroy`
- `inventory.warehouse-staff.destroy` in a table bulk action while the domain uses `warehouse-staff.destroy`
- missing `warehouse-positions.*` entries in `config/permissions.php`

### WarehouseStaff Cleanup Rules

WarehouseStaff repositories belong in `Domains/WarehouseStaff/Repositories`.

If a file under `Domains/Warehouse` declares a `Botble\Inventory\Domains\WarehouseStaff` namespace, it is not a WarehouseStaff standard file. Treat it as cleanup work in a dedicated task and avoid copying from it.

Do not rely on `WarehouseStaff::warehouse()` for multi-warehouse access. The real relation is `assignments()` and each assignment belongs to a warehouse.

## Warehouse Domain

Warehouse-owned code belongs under `src/Domains/Warehouse`.

Important current files include:

- `Models/Warehouse.php`
- `Models/WarehouseLocation.php`
- `Models/WarehouseProduct.php`
- `Http/Controllers/WarehouseController.php`
- `Http/Controllers/WarehouseProductController.php`
- `Http/Requests/WarehouseProductRequest.php`
- `Services/WarehouseProductService.php`
- `Tables/WarehouseTable.php`
- `Providers/WarehouseProvider.php`

`WarehouseProvider` registers the warehouse menu and binds `WarehouseInterface` to `WarehouseRepository`.

Warehouse list queries should use `inventory_warehouse_ids()` for non-super-admin users when the screen is warehouse-scoped.

## Warehouse Product Configuration

Warehouse product configuration belongs in the Warehouse domain.

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

Goods receipt product search must only return active products configured in `inv_warehouse_products` for the selected warehouse.

## Supplier Domain

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

Do not add old root Supplier files back under:

```txt
src/Forms/SupplierForm.php
src/Http/Controllers/SupplierController.php
src/Http/Requests/SupplierRequest.php
src/Models/Supplier*.php
src/Services/SupplierService.php
src/Tables/SupplierTable.php
src/Repositories/*Supplier*
```

`SupplierService` owns multi-table supplier writes and approval actions. Use `DB::transaction()` when syncing contacts, addresses, banks, and supplied products.

Supplier table should show operational fields:

- supplier code
- supplier name
- supplier type
- tax code
- primary contact
- supplied product count
- status badge
- created date

Use eager loading and `withCount()` for supplier list data. Computed columns should be non-orderable and non-searchable.

## GoodsReceipt Domain

Goods receipt code belongs under `src/Domains/GoodsReceipt`.

The domain owns:

- `GoodsReceipt` header model for `inv_goods_receipts`
- `GoodsReceiptItem` line model for `inv_goods_receipt_items`
- batch and stock models for receipt/stock schema
- `GoodsReceiptService` for transactional create/update
- product search and supplier product suggestion endpoints
- `GoodsReceiptTable`
- `GoodsReceiptProvider`

Create/update must remain transactional because one receipt header has many item rows. The service computes subtotal, discount, tax, and total from line rows.

Supplier product suggestions should read from `inv_supplier_products` and prefill product, supplier price, MOQ, and lead time note.

Do not silently post inventory movements when a receipt is marked `completed` unless a task defines posting rules. Posting should update `inv_stock_transactions`, `inv_stock_balances`, and possibly `ec_products.quantity` together.

## Migrations

Inventory migrations live under `platform/plugins/inventory/database/migrations`.

Before debugging runtime errors, check migration status:

```powershell
php artisan migrate:status --path=platform/plugins/inventory/database/migrations
```

For schema changes:

- create additive migrations instead of editing already-run migrations
- keep table names prefixed with `inv_`
- add indexes for foreign keys and search-heavy columns
- add foreign keys only when the referenced table exists in the same installation path
- preserve UUID vs bigint ID types from existing tables

## UI And Language

Use Botble Form/Table conventions already present in the domain.

For admin labels:

- prefer translation keys in `resources/lang/en/inventory.php` and `resources/lang/vi/inventory.php`
- keep language files valid UTF-8
- avoid hard-coded mojibake labels
- use `FormattedColumn` for computed table values and badges

For warehouse/product screens, follow `Design.md` when changing layout or views.

## Cleanup

`backup.php` in the plugin root is not runtime code. It can contain diff-style backup content and fail PHP lint. Do not include it in automated source lint.

When moving a feature into `Domains/<Domain>`:

- update namespaces
- update routes
- update provider imports and registrations
- update permissions
- remove old root copies only when they are confirmed obsolete
- keep unrelated dirty worktree changes untouched

## Verification Commands

Run touched-file lint:

```powershell
php -l platform/plugins/inventory/src/Domains/WarehouseStaff/Providers/WarehouseStaffProvider.php
php -l platform/plugins/inventory/src/Domains/WarehouseStaff/Http/Controllers/WarehouseStaffController.php
php -l platform/plugins/inventory/src/Domains/WarehouseStaff/Usecase/AssignmentsUsercase.php
php -l platform/plugins/inventory/src/Providers/InventoryServiceProvider.php
```

Run full plugin source lint:

```powershell
Get-ChildItem -Path 'platform/plugins/inventory/src' -Recurse -Filter '*.php' | ForEach-Object { php -l $_.FullName }
```

Check WarehouseStaff routes:

```powershell
php artisan route:list --name=inventory.warehouse-staff
php artisan route:list --name=inventory.warehouse-positions
```

Check Supplier or GoodsReceipt routes when touched:

```powershell
php artisan route:list --name=inventory.suppliers
php artisan route:list --name=inventory.goods-receipts
```

Check Laravel boot/autoload for a class:

```powershell
php -r "require 'vendor/autoload.php'; `$app = require 'bootstrap/app.php'; `$kernel = `$app->make('Illuminate\\Contracts\\Console\\Kernel'); `$kernel->bootstrap(); echo class_exists('Botble\\Inventory\\Domains\\WarehouseStaff\\Providers\\WarehouseStaffProvider') ? 'ok' : 'missing';"
```

## Review Checklist

Before finishing an Inventory plugin change:

- Feature code is inside the right domain folder.
- Routes point to domain controllers.
- Domain provider is registered from `InventoryServiceProvider`.
- Domain menu and repository bindings live in the domain provider.
- Permissions match route keys, table actions, menus, and `config/permissions.php`.
- Warehouse-scoped screens use `inventory.context` and helpers where needed.
- Multi-table writes are transactional.
- Table computed columns are not accidentally SQL-searchable/orderable.
- Admin labels are valid UTF-8 or translation keys.
- PHP lint and route checks pass for touched areas.
