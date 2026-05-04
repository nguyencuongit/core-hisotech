---
name: inventory-plugin
description: Work on the Botble Inventory plugin at platform/plugins/inventory. Use when editing Inventory domain modules such as WarehouseStaff, Warehouse, Supplier, GoodsReceipt, WarehouseMap, Pallet, Stock, routes, providers, permissions, forms, tables, requests, migrations, services, repositories, usecases, actions, DTOs, or admin UI behavior. Follow the mandatory domain-first structure, register domain providers from InventoryServiceProvider, keep controllers thin, and validate PHP/Laravel behavior before finishing.
---

# Inventory Plugin Skill

## 1. Purpose

Use this skill when working inside the Botble Inventory plugin:

```txt
platform/plugins/inventory
```

This skill is about:

- coding structure
- file placement
- domain architecture
- Botble conventions
- validation
- routes
- permissions
- providers
- migrations
- final checks

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
   - related Forms/Tables/Requests/Services/Repositories/UseCases/Actions/DTOs
4. Keep feature code inside:

```txt
src/Domains/<Domain>
```

5. Keep root `src/` folders for shared plugin infrastructure only.
6. Do not move domain-owned code back to root folders.
7. Do not use an older domain folder as proof that mandatory layers can be skipped.
8. Run focused PHP lint and route/provider checks before finishing.
9. Report clearly:
   - files changed
   - routes changed
   - permissions changed
   - providers changed
   - migrations changed
   - checks run
   - anything left unfinished

---

## 3. Mandatory Domain Structure

Every domain must follow this structure.

Do not treat `Services/`, `Repositories/`, `UseCases/`, `Actions/`, or `DTO/` as optional.

They are mandatory domain layers and must be used consistently.

```txt
src/Domains/<Domain>/
  Actions/
  DTO/
  Entities/             (one file per business entity — see section 4.3)
  Enums/                (optional)
  Forms/
  Http/
    Controllers/
    Requests/
  Mappers/              (Model ↔ Entity conversion — see section 4.3)
  Models/
  Permissions/          (one file per domain — see section 4.2)
  Providers/
  Repositories/
    Eloquent/
    Interfaces/
  Services/
  Support/              (optional, domain helpers/value objects)
  Tables/
  UseCases/
```

### Folder name standard: `UseCases/` (capital C, plural)

The current standard is `UseCases/` — capital `C`, with trailing `s`.

Reality matrix (as of last audit):

| Domain | Folder name |
|---|---|
| Packing | `UseCases/` ✅ |
| Supplier | `UseCases/` ✅ |
| WarehouseProduct | `UseCases/` ✅ |
| Transfer | `Usecase/` (legacy) |
| Transactions | `Usecase/` (legacy) |
| Warehouse | `Usecase/` (legacy) |
| WarehouseStaff | `Usecase/` (legacy) |
| Report | `Usecase/` (legacy) |
| Return | `Usecase/` (legacy) |

Rules:

- For NEW domains: use `UseCases/` only.
- For EXISTING legacy `Usecase/`: when a task substantially touches that domain, migrate the folder to `UseCases/` and update namespaces, imports, route handlers, and provider bindings in the same task.
- Class file name keeps the suffix `Usecase` (capital `U`, lowercase `c`, no plural). Example: `UseCases/PackingUsecase.php` defines class `PackingUsecase`.
- Never create a new domain that uses `Usecase/`.

### Empty mandatory layer folders

Even if a domain does not yet need concrete classes in every layer, the mandatory layer folders must still exist.

Required folders:

```txt
Actions/
DTO/
Entities/
Mappers/
Permissions/
Repositories/Eloquent/
Repositories/Interfaces/
Services/
UseCases/
```

If a folder would otherwise be empty, add a `.gitkeep` file so the structure is committed.

Do not use an older domain folder as proof that `Actions/`, `DTO/`, `Entities/`, `Mappers/`, `Services/`, `Permissions/`, or `UseCases/` can be skipped.

Optional folders (`Enums/`, `Support/`) only need to exist when the domain has actual files to put in them.

---

## 4. Layer Responsibilities

### `Forms/`

Botble admin form definitions.

Use for:

- create forms
- edit forms
- field definitions
- form buttons
- admin form layout
- binding the correct Request validator class

Do not put complex business logic here.

If a Botble Form has a matching Request class, set it explicitly:

```php
->setValidatorClass(WarehouseStaffRequest::class)
```

Do not import or use generic plugin requests such as `InventoryRequest` for domain forms unless the task explicitly requires it.

Avoid direct `DB::table()` in Forms when a model/service/repository exists.

---

### `Tables/`

Botble admin table definitions.

Use for:

- listing data
- table columns
- table filters
- table actions
- bulk actions
- scoped admin queries

Do not write complex mutation logic here.

Table actions must use the exact same permission flags as routes, menus, and `config/permissions.php`.

Correct:

```php
EditAction::make()->permission('warehouse-positions.edit');
DeleteBulkAction::make()->permission('warehouse-staff.destroy');
```

Wrong:

```php
EditAction::make()->permission('warehouse-positions.create');
DeleteBulkAction::make()->permission('inventory.warehouse-staff.destroy');
```

---

### `Http/Controllers/`

Controllers must stay thin.

Controllers should:

- receive request
- call Form when using Botble form flow
- call Request validation
- create DTO from validated request data when needed
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

If the operation writes multiple tables, wrap it in `DB::transaction()` in the Service, Usecase, or Action layer.

Controllers may start a transaction only when the existing domain flow already expects it, but new orchestration should be delegated.

---

### `Http/Requests/`

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

For update rules, verify the route parameter name before using it in `Rule::unique()->ignore(...)`.

---

### `Models/`

Eloquent models live here.

Models should define:

- table name
- fillable fields
- casts
- relationships
- basic scopes only

Do not put orchestration or multi-table workflow logic in Models.

Do not use Models as service classes.

---

### `Providers/`

Domain providers live here.

A domain provider owns:

- domain menu registration
- domain repository bindings
- domain events/listeners if needed
- domain-specific bootstrapping

Do not place domain-specific menus or bindings in the root `InventoryServiceProvider`.

---

### `Repositories/Interfaces/`

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

### `Repositories/Eloquent/`

Eloquent repository implementations live here.

Use repositories for:

- reusable domain queries
- scope-aware queries
- complex data access
- find/update helpers

Do not put controller-specific UI logic here.

Repository methods must return query builders, collections, models, arrays, or scalar values correctly.

Do not call `toArray()` directly on an Eloquent builder.

Wrong:

```php
return $this->model->where('staff_id', $id)->toArray();
```

Correct:

```php
return $this->model->where('staff_id', $id)->get()->toArray();
```

Prefer returning a query builder when the caller needs to continue filtering:

```php
return $this->model->newQuery()->where('staff_id', $id);
```

---

### `Services/`

Services contain core domain logic.

Use services for:

- reusable business operations
- multi-step domain logic
- transactional data changes
- calculations
- state changes
- persistence coordination within one domain

Services may call repositories and models.

Services should not render admin UI.

---

### `UseCases/`

Usecases orchestrate a complete user/system operation.

Use when a workflow coordinates multiple services/repositories/models.

Examples (folder = `UseCases/`, class file suffix = `Usecase`):

```txt
UseCases/AssignmentsUsecase.php       (class AssignmentsUsecase)
UseCases/CreateGoodsReceiptUsecase.php
UseCases/UpdateWarehouseMapUsecase.php
UseCases/PackingUsecase.php
```

Folder naming rules:

- Folder MUST be `UseCases/` for any new file or new domain.
- Class names keep `Usecase` suffix (capital `U`, lowercase `c`, no plural).
- If touching a legacy `Usecase/` folder for substantial work, rename it to `UseCases/` in the same task and update imports/namespaces.
- Do not introduce a third spelling (`UseCase/`, `Usecases/`).

---

### `Actions/`

Actions represent focused single-purpose operations.

Use for small, reusable commands such as:

```txt
CreatePalletAction
MovePalletAction
GenerateLocationTreeAction
SyncWarehouseStaffAssignmentsAction
```

Actions should be easy to call from controllers, usecases, jobs, or services.

Actions should have one clear reason to change.

---

### `DTO/`

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

Prefer:

```php
$dto = WarehouseStaffDTO::fromRequest($request);
```

over passing raw request arrays across multiple layers.

---

## 4.1. Model Access Discipline (CRITICAL)

The single biggest source of bugs in this plugin is models being queried from too many places. Fix by enforcing one rule:

> **Only `Repositories/Eloquent/` files may call `Model::query()`, `new Model()`, `Model::create()`, `Model::find()`, `Model::where()`, or any direct Eloquent builder.**

Every other layer goes through the repository.

### What counts as "direct model access" (and what does NOT)

Common confusion: agents see `use ...\Models\Supplier;` in a Service file and think it violates the rule. **It does not.** Distinguish:

**❌ Direct model access (forbidden outside `Repositories/Eloquent/`):**
- `Supplier::query()`, `Supplier::find($id)`, `Supplier::where(...)`, `Supplier::create([...])`, `Supplier::all()`
- `new Supplier($data)` followed by `->save()` / `->push()` (using model as data-access entry point)
- Any static call on the model class that hits the database

**✅ Allowed everywhere (NOT considered "calling the model"):**
- `use Botble\...\Models\Supplier;` — importing for type hints
- `function create(): Supplier` — return type hint
- `function update(Supplier $supplier)` — parameter type hint
- `$supplier instanceof Supplier` — type check
- `$supplier->name`, `$supplier->banks`, `$supplier->status?->value` — property/relation access on an instance that was loaded *through the repository*
- `$supplier->getKey()`, `$supplier->save()` on an instance returned by repo (instance methods on a hydrated entity are fine)
- `new Supplier()` passed into a Repository constructor (DI binding in a Provider) — this is wiring, not data access

Rule of thumb: if the line could appear inside a DTO (no DB round-trip), it is allowed. If the line *fetches or persists* via the model class statically, it is forbidden outside the repository.

### Allowed vs forbidden by layer

| Layer | May call Model directly? | What it MUST use instead |
|---|---|---|
| `Repositories/Eloquent/` | ✅ Yes — this is its job | — |
| `Services/` | ❌ No | Inject `XxxInterface`, call `$this->repo->method()` |
| `UseCases/` | ❌ No | Inject `XxxInterface` + service |
| `Actions/` | ❌ No | Same as Service |
| `Http/Controllers/` | ❌ No | Inject Usecase or Service |
| `Forms/` | ⚠️ Limited | May call repository for dropdown choices. Avoid `Model::pluck` directly. |
| `Tables/` | ⚠️ Limited | Botble's `TableAbstract::query()` is allowed to use `$this->getModel()->query()` because the framework expects an Eloquent builder return. Add scoping (warehouse_ids) inline here. |
| `Http/Requests/` | ⚠️ Read-only | `exists:` validation rule may reference table names; avoid `Model::find` inside `authorize()` — call a repository method. |

### Why this matters

A bug seen in code review: `validatePayload` in service queries `Export::query()->find()` directly. When the warehouse-scoping rule changes, you have to hunt `Model::query()` through 30 files. With repository discipline, scoping change touches 1 file.

### Example — wrong

```php
// In PackingService.php
$export = Export::query()->find($exportId);              // ❌
$activePacking = PackingList::query()->where(...)->first(); // ❌
$exportItems = ExportItem::query()->whereIn(...)->get();    // ❌
```

### Example — correct

```php
// In PackingService.php (constructor injection)
public function __construct(
    protected PackingInterface $packings,
    protected ExportRepositoryInterface $exports,
) {}

// usage
$export = $this->exports->findOrFail($exportId);
$activePacking = $this->packings->findActiveByExportId($exportId, $excludeId);
$exportItems = $this->exports->itemsByIds($exportItemIds);
```

### Repository method naming

Method names must read as business intent, not SQL:

```txt
findActiveByExportId         (not: query()->where(...)->first())
itemsByIds                   (not: whereIn(...)->get())
packedQuantitiesForList      (not: selectRaw->groupBy)
```

If a method exists only to wrap one Eloquent call, that is still correct. Centralization wins over brevity.

### Exceptions (must comment why)

Two narrow exceptions are allowed without going through repository:

1. `lockForUpdate()` inside a `DB::transaction()` in a Service — when the lock semantics matter and pulling through a repository would lose them. Add a `// LOCK:` comment above the call.
2. `Model::factory()` in tests/seeders.

Any other direct model access in non-repository code is a violation.

### Enforcement checklist before finishing a task

```powershell
# Find direct model access outside repositories (false positives possible — review manually)
Get-ChildItem -Path 'platform/plugins/inventory/src/Domains' -Recurse -Filter '*.php' |
  Where-Object { $_.FullName -notmatch 'Repositories\\Eloquent' } |
  Select-String -Pattern '::query\(\)|->newQuery\(\)' |
  Select-Object Path, LineNumber, Line
```

If the result includes a file outside `Repositories/Eloquent/`, either move the call into a repository method or document the exception.

---

## 4.2. Domain Permissions Centralization (CRITICAL)

Hard-coding permission strings like `'packing.create'` across routes/tables/forms/menus causes silent breakage when the slug changes. Fix by defining permissions ONCE per domain.

### One file per domain

Each domain has exactly one Permissions class:

```txt
src/Domains/<Domain>/Permissions/<Domain>Permissions.php
```

The class holds public string constants only. No methods, no logic.

### Example

```php
<?php

namespace Botble\Inventory\Domains\Packing\Permissions;

final class PackingPermissions
{
    public const INDEX   = 'packing.index';
    public const CREATE  = 'packing.create';
    public const EDIT    = 'packing.edit';
    public const DESTROY = 'packing.destroy';

    /**
     * Used by config/permissions.php auto-registration.
     */
    public static function all(): array
    {
        return [
            self::INDEX,
            self::CREATE,
            self::EDIT,
            self::DESTROY,
        ];
    }
}
```

### Where to reference these constants

Every place that checks or declares a permission for this domain MUST import the constant. Hard-coded strings are forbidden.

#### Routes (`routes/web.php`)

```php
use Botble\Inventory\Domains\Packing\Permissions\PackingPermissions;

Route::group(['prefix' => 'packing', 'as' => 'packing.'], function () {
    Route::get('/', [
        'uses' => PackingController::class . '@index',
        'as' => 'index',
        'permission' => PackingPermissions::INDEX,   // ✅
    ]);

    Route::post('/create', [
        'uses' => PackingController::class . '@store',
        'as' => 'store',
        'permission' => PackingPermissions::CREATE,  // ✅
    ]);
});
```

Wrong:

```php
'permission' => 'packing.create',  // ❌ hard-coded
```

#### Tables (`Tables/<Domain>Table.php`)

```php
use Botble\Inventory\Domains\Packing\Permissions\PackingPermissions;

EditAction::make()->permission(PackingPermissions::EDIT);          // ✅
DeleteBulkAction::make()->permission(PackingPermissions::DESTROY); // ✅
```

#### Forms (`Forms/<Domain>Form.php`)

When a form needs to show/hide a button by permission:

```php
if (auth()->user()?->hasPermission(PackingPermissions::EDIT)) { ... }
```

#### Controllers — ad-hoc gates

```php
abort_unless(
    auth()->user()?->hasPermission(PackingPermissions::CREATE)
    || auth()->user()?->hasPermission(PackingPermissions::EDIT),
    403
);
```

#### Provider — menu registration

```php
DashboardMenu::make()
    ->registerItem([
        'permissions' => [PackingPermissions::INDEX],
        // ...
    ]);
```

#### `config/permissions.php`

Either reference the constants directly, or add a comment pinning the source:

```php
// Source of truth: Domains/Packing/Permissions/PackingPermissions.php
[
    'name'  => 'Quản lý phiếu đóng gói',
    'flag'  => 'packing.index',  // matches PackingPermissions::INDEX
    'parent_flag' => 'inventory.index',
],
```

### Rules

- Constants are `public const`, all UPPER_SNAKE_CASE.
- Constant value is the bare permission flag (no `inventory.` prefix unless the whole plugin uses that style — see section 19).
- Never mix `EDIT` and `UPDATE`, or `DESTROY` and `DELETE`. Pick one verb per CRUD slot per the convention in section 19.
- When adding a new permission to a domain:
  1. Add the constant to `<Domain>Permissions.php`
  2. Reference it from routes
  3. Reference it from table/menu/form
  4. Add the matching entry to `config/permissions.php`
- When deleting a permission, delete from all 4 places in the same task.

### Why this works

When an agent edits anything in a domain, it sees `<Domain>Permissions.php` first (it is in the standard mandatory layer list) and copies the constant. It does not invent a new string. Renaming a permission becomes a 1-file change with a stable IDE rename refactor — instead of grepping the whole plugin.

---

## 4.3. Domain Entity Layer (CRITICAL)

Even with section 4.1 enforced, business logic still leaks Eloquent details (relations, accessors, framework events) up into Services, Usecases, and Actions. Fix by introducing a **Domain Entity** layer: immutable PHP objects that represent the business entity *without any Eloquent inheritance or framework coupling*.

> **Services, Usecases, Actions, and DTOs MUST consume `XxxEntity`, not `XxxModel`. The Eloquent Model is allowed only in `Repositories/Eloquent/`, `Mappers/`, and a documented framework-boundary list (Forms, Tables, Provider DI).**

### Folder structure (mandatory when this rule applies)

```txt
src/Domains/<Domain>/
├── Entities/                    ← NEW — pure PHP, no Eloquent
│   ├── <Domain>Entity.php       ← root aggregate
│   └── <Domain><Child>Entity.php
├── Mappers/                     ← NEW — single conversion point
│   └── <Domain>Mapper.php
├── Models/                      ← Eloquent (existing)
├── Repositories/
│   ├── Eloquent/                ← imports Model + Mapper, returns Entity
│   └── Interfaces/              ← signatures use Entity
├── Services/                    ← consumes Entity ONLY
├── UseCases/                    ← consumes Entity ONLY
├── Actions/                     ← consumes Entity ONLY
└── …
```

### Entity rules

- **Immutable**: all properties `public readonly`. No setters.
- **Plain PHP**: no `extends Model`, no `use HasFactory`, no Eloquent traits.
- **Self-contained**: holds the data the domain needs — primitives, enums, child entities, plain arrays. NEVER holds an Eloquent `Collection` or another Eloquent Model.
- **Pure behavior allowed**: methods that compute from own state (`isApproved()`, `requiresReapproval()`, `primaryContact()`) are encouraged.
- **No DB calls**: an Entity method MUST NOT touch the database, queue events, or call repositories.
- **Constructed via `__construct` or named `from*` factories** — never auto-hydrated by a framework.

### Example — entity

```php
<?php

namespace Botble\Inventory\Domains\Supplier\Entities;

use Botble\Inventory\Enums\SupplierStatusEnum;
use Botble\Inventory\Enums\SupplierTypeEnum;
use Carbon\CarbonImmutable;

final class SupplierEntity
{
    /**
     * @param  list<SupplierContactEntity>  $contacts
     * @param  list<SupplierAddressEntity>  $addresses
     * @param  list<SupplierBankEntity>     $banks
     * @param  list<SupplierProductEntity>  $products
     * @param  list<SupplierApprovalEntity> $approvals
     */
    public function __construct(
        public readonly int|string $id,
        public readonly string $code,
        public readonly string $name,
        public readonly ?SupplierTypeEnum $type,
        public readonly ?string $taxCode,
        public readonly ?string $website,
        public readonly ?string $note,
        public readonly ?SupplierStatusEnum $status,
        public readonly array $metadata,
        public readonly ?int $createdBy,
        public readonly ?int $submittedBy,
        public readonly ?CarbonImmutable $submittedAt,
        public readonly ?int $approvedBy,
        public readonly ?CarbonImmutable $approvedAt,
        public readonly ?string $approvalNote,
        public readonly bool $requiresReapproval,
        public readonly array $contacts = [],
        public readonly array $addresses = [],
        public readonly array $banks = [],
        public readonly array $products = [],
        public readonly array $approvals = [],
    ) {}

    public function isApproved(): bool
    {
        return $this->status?->isApproved() ?? false;
    }

    public function primaryContact(): ?SupplierContactEntity
    {
        foreach ($this->contacts as $contact) {
            if ($contact->isPrimary) {
                return $contact;
            }
        }
        return $this->contacts[0] ?? null;
    }
}
```

### Mapper rules

- One file per domain: `Mappers/<Domain>Mapper.php`.
- Static methods only (`toEntity`, `toEntities`, `toContactEntity`, …).
- The **only** place in the domain that imports both `Models\X` AND `Entities\X`.
- No DB calls in the mapper. It accepts an *already-loaded* model (with its relations) and returns an entity.

### Repository rules under this pattern

- **Read methods** return Entity (or `?Entity`, `list<Entity>`).
- **Write methods** accept primitives or DTO, return Entity.
- Repository is the only file (besides Mapper) that uses both worlds: it queries Eloquent internally, then calls the Mapper before returning.
- Lock-and-update flows: lock with Eloquent inside repo, update, then map to Entity for the return value.

### Allowed vs forbidden by layer (with Entity rule applied)

| Layer | Imports Model? | Imports Entity? |
|---|---|---|
| `Models/` | ✅ self | ❌ |
| `Entities/` | ❌ | ✅ self |
| `Mappers/` | ✅ | ✅ |
| `Repositories/Eloquent/` | ✅ | ✅ (return type) |
| `Repositories/Interfaces/` | ❌ | ✅ |
| `Services/` | ❌ | ✅ |
| `UseCases/` | ❌ | ✅ |
| `Actions/` | ❌ | ✅ |
| `DTO/` | ❌ | ❌ (DTOs are pure data, no entity inside) |
| `Http/Requests/` | ❌ | ❌ |
| `Http/Controllers/` | ⚠️ route binding only — convert immediately to Entity via repo | ✅ |
| `Forms/` | ⚠️ Botble framework boundary | ⚠️ Use Entity for read; Model required by `setModel()` |
| `Tables/` | ⚠️ Botble framework boundary — `model(Model::class)` required | ❌ |
| `Providers/` | ⚠️ DI binding only (`new Model()` for repo constructor) | ❌ |

### Documented framework-boundary exceptions

These exceptions exist because Botble's `FormAbstract` / `TableAbstract` / repository binding require an Eloquent class. Each MUST carry the comment `// FRAMEWORK-BOUNDARY: Botble requires Eloquent Model here.` directly above the offending line:

```php
class SupplierTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            // FRAMEWORK-BOUNDARY: Botble requires Eloquent Model here.
            ->model(Supplier::class)
            // …
    }
}
```

```php
class SupplierProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SupplierInterface::class, function () {
            // FRAMEWORK-BOUNDARY: Botble requires Eloquent Model here.
            return new SupplierRepository(new Supplier());
        });
    }
}
```

A reviewer who sees Model imported anywhere else without this comment must reject the change.

### Controller boundary

Controllers may receive a `Model` from Laravel's route model binding. Convert to Entity at the first line of the action — do not pass Model deeper:

```php
// ✅
public function show(Supplier $supplier, SupplierUsecase $usecase)
{
    $entity = $usecase->loadForShow($supplier->getKey());  // returns SupplierEntity
    return view('plugins/inventory::suppliers.show', ['supplier' => $entity]);
}

// ❌
public function show(Supplier $supplier, SupplierUsecase $usecase)
{
    $supplier = $usecase->loadForShow($supplier);   // Usecase signature took Model — leak.
    return view('plugins/inventory::suppliers.show', compact('supplier'));
}
```

### Service rule restated

A Service file MUST satisfy ALL of:

1. Zero `use ...\Models\` import (except in test files).
2. All injected dependencies are interfaces (`XxxInterface`) or other services.
3. Every method that returns an entity declares `XxxEntity` as return type.
4. Every method that accepts an entity declares `XxxEntity` as parameter type.

If a Service needs `$entity->save()`-like semantics, it calls `$this->repo->update($entity, $changes)` — repo handles persistence, returns the updated Entity.

### Enforcement checklist before finishing a task

```powershell
# Find Model imports in business-logic layer (Services, Usecases, Actions)
Get-ChildItem -Path 'platform/plugins/inventory/src/Domains' -Recurse -Filter '*.php' |
  Where-Object { $_.FullName -match '\\(Services|UseCases|Actions)\\' } |
  Select-String -Pattern 'use Botble\\Inventory\\Domains\\\w+\\Models\\' |
  Select-Object Path, LineNumber, Line
```

If the result is non-empty, either remove the Model import (use Entity) or — only if the file is `Mappers/` or `Repositories/Eloquent/` — confirm it is in the allowed list above.

### Why this matters

When the storage layer changes (column rename, ORM swap, computed field migration), only `Mappers/` and `Repositories/Eloquent/` are affected. Service/Usecase/Action remain untouched because they speak Entity, not Model. This is the same pattern as Hexagonal / Clean Architecture's "domain over infrastructure" rule — applied to a Botble plugin.

### Migration plan when adopting this rule on an existing domain

1. Create `Entities/` and `Mappers/` folders.
2. Define Entity classes mirroring the Model's persisted state (NOT every accessor — only what the domain reads).
3. Write Mapper static methods.
4. Add return-type-only changes to `Repositories/Interfaces/` (Entity instead of Model).
5. Update `Repositories/Eloquent/` to call Mapper before returning.
6. Update Service one method at a time; run tests after each.
7. Update Usecase + Actions.
8. Update Controllers (last — route model binding still gives Model; convert via Usecase).
9. Run the enforcement grep above; resolve every hit.

---

## 5. Domain File Placement Rules

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

The root plugin folders may contain shared infrastructure such as:

```txt
src/Providers/InventoryServiceProvider.php
src/Http/Middleware/
src/Support/
src/Helpers/
```

Do not add domain-specific CRUD code to root folders.

---

## 6. Naming Rules

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

### Existing legacy names — migration targets

The following legacy names exist in code and should be migrated when their domain is touched substantially:

```txt
src/Domains/WarehouseStaff/Usecase/AssignmentsUsercase.php
                          └─ folder ─┘ └── class typo "Usercase" ──┘
```

Target after migration:

```txt
src/Domains/WarehouseStaff/UseCases/AssignmentsUsecase.php
```

When migrating, update in one atomic task:

- folder rename `Usecase/` → `UseCases/`
- class typo fix `AssignmentsUsercase` → `AssignmentsUsecase`
- namespace
- all imports
- controller usage
- provider bindings if any
- lint and route checks
- composer dump-autoload

Do not perform half-migrations. Do not rename folder without fixing class typo at the same time.

---

## 7. WarehouseStaff Standard

`WarehouseStaff` owns:

- staff
- positions
- staff-to-warehouse assignments

Keep these files under:

```txt
src/Domains/WarehouseStaff
```

Required files/folders:

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
UseCases/AssignmentsUsecase.php
```

If `Actions/`, `DTO/`, or `Services/` has no concrete class yet, keep the folder with `.gitkeep`.

### WarehouseStaff behavior rules

- `WarehouseStaffProvider` registers the staff/position admin menu.
- `WarehouseStaffProvider` binds `WarehouseStaffAssignmentInterface` to `WarehouseStaffAssignmentRepository`.
- `InventoryServiceProvider` registers `WarehouseStaffProvider::class`.
- Do not move staff menus back to the root provider.
- `WarehouseStaffController` saves the Botble form, then syncs warehouse assignments through `AssignmentsUsercase` inside `DB::transaction()` if this is the existing current flow.
- `AssignmentsUsercase` is the current assignment orchestration point.
- Keep repository details out of controllers.
- `WarehouseStaffAssignments` uses `inv_warehouse_staff_assignments`.
- `inv_warehouse_staff_assignments` should keep unique `staff_id + warehouse_id`.
- Treat staff-to-warehouse as many-to-many through `assignments()`.
- Do not rely on `WarehouseStaff::warehouse()` for access checks or staff lists.
- `WarehouseStaffTable` must eager-load `assignments.warehouse`.
- Non-super-admin staff queries must respect `inventory_warehouse_ids()`.
- `InventoryContextMiddleware` maps the logged-in user to `WarehouseStaff`, then to assigned warehouse IDs.
- `super_user === 1` is unscoped.
- `UserWarehouse` and `inv_user_warehouses` exist, but current context flow uses `WarehouseStaffAssignments`.
- Do not switch staff context flow without an explicit task.

### Warehouse scope safety

For non-super-admin users:

- If `inventory_warehouse_ids()` returns IDs, scope queries with `whereIn`.
- If `inventory_warehouse_ids()` is empty, return no records.
- Never fall back to unscoped queries for non-super-admin users.

Correct:

```php
$query = $model->newQuery();

if (! inventory_is_super_admin()) {
    $warehouseIds = inventory_warehouse_ids();

    if (empty($warehouseIds)) {
        return $query->whereRaw('1 = 0');
    }

    return $query->whereIn('warehouse_id', $warehouseIds);
}

return $query;
```

Wrong:

```php
if (! $isAdmin && ! empty($warehouseIds)) {
    return $this->model->whereIn('warehouse_id', $warehouseIds);
}

return $this->model;
```

The wrong example leaks all records when the user is not admin and has no assigned warehouses.

---

## 8. WarehouseStaff Route And Permission Rules

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
- Do not mix `inventory.warehouse-staff.*` permission flags unless the full permission set is intentionally migrated.
- When changing permission names, update:
  - routes
  - tables
  - menus
  - config/permissions.php
  - tests/checks if any

---

## 9. WarehouseStaff Validation Rules

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

Recommended Laravel rules:

```php
return [
    'full_name' => ['required', 'string', 'max:255'],
    'staff_code' => [
        'required',
        'string',
        'max:100',
        Rule::unique('inv_warehouse_staff', 'staff_code')->ignore($this->route('warehouse_staff')),
    ],
    'phone' => ['required', 'string', 'max:50'],
    'email' => ['required', 'email', 'max:255'],
    'warehouse_id' => ['required', 'array'],
    'warehouse_id.*' => ['integer', 'exists:inv_warehouses,id'],
    'position' => ['nullable', 'integer', 'exists:inv_warehouse_positions,id'],
    'status' => ['nullable', 'string'],
    'user_id' => ['nullable', 'integer', 'exists:users,id'],
];
```

Verify route parameter names before using the example as-is.

### Position

`WarehousePositionRequest` must validate:

```txt
name required
code required and unique in inv_warehouse_positions, ignoring current model on update
level integer min 0 max 100
is_active boolean/status valid
```

Recommended Laravel rules:

```php
return [
    'name' => ['required', 'string', 'max:255'],
    'code' => [
        'required',
        'string',
        'max:100',
        Rule::unique('inv_warehouse_positions', 'code')->ignore($this->route('warehouse_position')),
    ],
    'level' => ['nullable', 'integer', 'min:0', 'max:100'],
    'is_active' => ['nullable', 'boolean'],
];
```

Verify route parameter names before using the example as-is.

### Label rule

If editing Vietnamese labels:

- write valid UTF-8
- or use translation keys
- do not preserve mojibake labels

Correct:

```txt
Mã chức vụ
Mã nhân viên
Tên nhân viên
```

Wrong:

```txt
MÃ nhÃ¢n viÃªn
```

For `WarehousePositionForm`, the code field label should be:

```txt
Mã chức vụ
```

not:

```txt
Mã nhân viên
```

---

## 10. Warehouse Domain Rules

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
Permissions/
Providers/
Repositories/Eloquent/
Repositories/Interfaces/
Services/
Tables/
UseCases/
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
- Controllers must call Services/Usecase/Actions for multi-step logic.
- Keep warehouse-scoped queries safe for non-super-admin users.

---

## 11. GoodsReceipt Domain Rules

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
Permissions/
Providers/
Repositories/Eloquent/
Repositories/Interfaces/
Services/
Tables/
UseCases/
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
- Multi-table writes belong in Service/Usecase/Action and must use `DB::transaction()`.
- Do not post stock transactions, stock balances, or ecommerce product quantity unless the task explicitly defines posting rules.
- Do not mix goods receipt draft/save logic with stock posting logic unless explicitly requested.

---

## 12. Supplier Domain Rules

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
Permissions/
Providers/
Repositories/Eloquent/
Repositories/Interfaces/
Services/
Tables/
UseCases/
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
- Multi-table writes belong in `SupplierService` or a Usecase/Action and must use `DB::transaction()`.
- Render supplier enum statuses with labels/badges, not raw values.
- Keep supplier product relations under the Supplier domain unless a task explicitly moves shared logic elsewhere.

---

## 13. WarehouseMap Domain Rules

WarehouseMap-owned code belongs under:

```txt
src/Domains/WarehouseMap
```

Required structure:

```txt
Actions/
DTO/
Forms/
Http/Controllers/
Http/Requests/
Models/
Permissions/
Providers/
Repositories/Eloquent/
Repositories/Interfaces/
Services/
Tables/
UseCases/
```

Rules:

- Map is a visual layer only.
- Location is the real storage address.
- Do not make map cells the source of truth for stock.
- Map interactions that affect real storage must resolve to valid warehouse locations.
- Keep map rendering/UI logic separate from stock movement logic.

---

## 14. Pallet Domain Rules

Pallet-owned code belongs under:

```txt
src/Domains/Pallet
```

Required structure:

```txt
Actions/
DTO/
Forms/
Http/Controllers/
Http/Requests/
Models/
Permissions/
Providers/
Repositories/Eloquent/
Repositories/Interfaces/
Services/
Tables/
UseCases/
```

Rules:

- Pallet is a real container for goods.
- Pallet status changes should be handled by Services/Actions.
- Pallet movement should be transactional.
- Do not directly change stock balance in pallet UI controllers.
- If pallet movement affects stock, delegate to the correct stock/ledger service or usecase.

---

## 15. Stock Domain Rules

Stock-owned code belongs under:

```txt
src/Domains/Stock
```

Required structure:

```txt
Actions/
DTO/
Forms/
Http/Controllers/
Http/Requests/
Models/
Permissions/
Providers/
Repositories/Eloquent/
Repositories/Interfaces/
Services/
Tables/
UseCases/
```

Rules:

- Stock balance is the source of truth for inventory quantity.
- Stock ledger records quantity movement history.
- Do not silently update stock balance without ledger rules if posting is part of the task.
- Do not post stock from draft documents unless explicitly requested.
- Do not mix receipt creation with receipt posting unless the task explicitly defines that behavior.

---

## 16. Provider Rules

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
- If a provider import is added, run lint on `InventoryServiceProvider.php`.

---

## 17. Route Rules

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
- Do not add duplicate route names.
- Do not change route parameter names without checking Requests and controllers.

---

## 18. Permission Rules

Permissions are defined in:

```txt
config/permissions.php
```

Rules:

- Use one naming style per domain.
- Prefer bare permission flags for Inventory domain actions.
- Keep permissions aligned across:
  - routes
  - tables
  - menus
  - config/permissions.php
- Do not use `.delete` in one place and `.destroy` in another.
- Do not prefix permissions with `inventory.` unless the whole domain intentionally uses that style.
- When adding a route action, add the matching permission if it should be protected.

Recommended CRUD permission pattern:

```txt
<domain>.index
<domain>.create
<domain>.edit
<domain>.destroy
```

Example:

```txt
warehouse-staff.index
warehouse-staff.create
warehouse-staff.edit
warehouse-staff.destroy
```

---

## 19. Migration Rules

Migrations live in the plugin migration folder.

Rules:

- Use clear table names with `inv_` prefix.
- Use short explicit index/foreign key names when MySQL identifier length can be an issue.
- Do not create duplicate tables for the same concept.
- When adding nullable columns involved in unique business dimensions, ensure service logic handles nullable uniqueness correctly.
- Do not run destructive migrations unless explicitly requested.
- If a field is legacy but still used, do not drop it casually.
- If dropping/renaming a field, explain data impact and provide safe migration strategy.
- For assignment pivot tables, enforce unique business keys where required.
- For `inv_warehouse_staff_assignments`, keep unique `staff_id + warehouse_id`.

---

## 20. Cleanup Rules

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

Do not mix cleanup refactors into unrelated fixes unless required to make the task work.

---

## 21. Code Style Rules

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
- Keep namespaces aligned with folder paths.
- Prefer explicit imports over fully-qualified class names scattered through methods.
- Do not leave unused imports after editing.
- Do not leave debug code such as `dd()`, `dump()`, or `ray()`.

---

## 22. Focused Validation Checklist

Run focused checks after changes.

### WarehouseStaff checks

```powershell
php -l platform/plugins/inventory/src/Domains/WarehouseStaff/Providers/WarehouseStaffProvider.php
php -l platform/plugins/inventory/src/Domains/WarehouseStaff/Http/Controllers/WarehouseStaffController.php
php -l platform/plugins/inventory/src/Domains/WarehouseStaff/Http/Requests/WarehouseStaffRequest.php
php -l platform/plugins/inventory/src/Domains/WarehouseStaff/Http/Requests/WarehousePositionRequest.php
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

### Composer/autoload check if classes moved or renamed

```powershell
composer dump-autoload
```

### Cache clear when routes/providers/config changed

```powershell
php artisan optimize:clear
```

Only claim a check passed if it was actually run.

---

## 23. Finish Criteria

Before final response, confirm:

- Domain files are in the correct folder.
- Mandatory domain folders were respected:
  - `Actions/`
  - `DTO/`
  - `Forms/`
  - `Http/Controllers/`
  - `Http/Requests/`
  - `Models/`
  - `Permissions/`
  - `Providers/`
  - `Repositories/Eloquent/`
  - `Repositories/Interfaces/`
  - `Services/`
  - `Tables/`
  - `UseCases/` (capital `C`, plural)
- No file outside `Repositories/Eloquent/` calls `Model::query()`, `new Model()`, or `Model::create()` directly.
- All permission strings come from `<Domain>Permissions` constants — no hardcoded `'packing.create'` strings in routes/tables/forms/menus.
- Empty mandatory folders have `.gitkeep`.
- Routes point to domain controllers.
- Provider registration and domain provider bindings are correct.
- Permission flags are aligned between routes, tables, menus, and config.
- Warehouse-scoped queries respect `inventory.context` when needed.
- Non-super-admin empty warehouse scope does not leak all records.
- PHP lint passes for touched PHP files.
- Route list or Laravel boot checks pass when routes/providers changed.
- Migrations are listed/checked when migrations changed.
- Any skipped checks are reported with a reason.

---

## 24. Final Response Format

When finished, respond in Vietnamese with:

```txt
1. Đã làm gì
2. File đã sửa / tạo mới
3. Route / permission / provider có thay đổi không
4. Migration có thay đổi không
5. Đã chạy check/lint gì
6. Còn gì chưa làm hoặc cần review thủ công
```

Do not claim checks passed if they were not run.

---

## 25. Agent Safety Rules

When acting as a coding agent:

- Do not guess file contents when the file exists.
- Read before editing.
- Do not overwrite unrelated user changes.
- Do not run destructive commands unless explicitly requested.
- Do not delete migrations or data files without explicit approval.
- Prefer small, targeted patches.
- Explain skipped checks honestly.
- If the current code conflicts with this skill, follow this skill unless the user explicitly says to preserve legacy behavior.
