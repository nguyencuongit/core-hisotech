---
name: inventory-skill-creator
description: Create or update agent-facing skill documentation for the Inventory plugin. Use when capturing finalized conventions for the inventory domain structure, naming, permissions, forms, tables, requests, services, repositories, usecases, UUID models, enums, breadcrumbs, DashboardMenu, and especially the cross-plugin rule that foreign plugin data must be queried directly from tables instead of importing foreign Eloquent models.
---

# Inventory Skill Creator

Use this skill when the user asks to create, update, or standardize the agent guidance for the Botble Inventory plugin.

## Goal

Document the conventions that new Inventory work must follow so other agents can implement features consistently without re-learning the same rules.

## What to capture

### 1. Domain structure
Inventory uses a DDD-lite domain layout under `src/Domains/<Domain>/`.
Document the expected layers:

- `Actions/`
- `DTO/`
- `Entities/`
- `Forms/`
- `Http/Controllers/`
- `Http/Requests/`
- `Mappers/`
- `Models/`
- `Permissions/`
- `Providers/`
- `Repositories/Eloquent/`
- `Repositories/Interfaces/`
- `Services/`
- `Tables/`
- `UseCases/`

Prefer `UseCases/` for new work.

### 2. Naming and model conventions
Document these conventions clearly:

- table prefix uses `inv_*`
- model tables use snake_case table names
- domain models extend `BaseModel`
- UUID is the default identifier style when the domain defines it
- keep enum casts on model fields
- keep `SoftDeletes` where the domain already uses it
- keep `SafeContent` or equivalent cast only where the field actually needs it
- keep breadcrumbs, permissions, and dashboard menu entries aligned with the domain slug

### 3. Controller and validation conventions
Document that:

- controllers stay thin
- requests own validation
- DTOs carry validated data into usecases/services
- multi-table writes belong in service/usecase/action layers, wrapped in `DB::transaction()`

### 4. Repository and service conventions
Document that:

- repositories own persistence
- services/usecases orchestrate business flows
- repository methods should return entities when the domain uses entity mapping
- relation loading should be deliberate and limited to same-domain data

### 5. Cross-plugin rule
This is the most important rule.

If Inventory needs data from another plugin, do **not** import that plugin's Eloquent model into the Inventory domain.
Instead:

- query the foreign table directly with `DB::table('...')`
- select only the needed columns
- map the result into the domain entity/DTO in the Inventory layer
- keep the domain isolated from foreign model classes

Examples of foreign tables that must be queried directly when needed:

- `ec_products`
- `users`

### 6. What not to do
Document these anti-patterns:

- importing `Botble\Ecommerce\Models\Product` inside Inventory domain models
- importing `Botble\ACL\Models\User` inside Inventory domain models
- eager loading foreign relations across plugin boundaries
- placing business logic in controllers
- skipping `FormRequest` validation
- mixing dashboard/menu/permission strings across domains

## Suggested skill output format
When generating a new skill document, include:

1. a short purpose section
2. the mandatory structure
3. the cross-plugin data access rule
4. the naming and permission conventions
5. the final checks to run before finishing

## Final checks
Before closing the task, ensure the skill text is consistent with current Inventory code conventions and does not describe patterns that the plugin does not actually use.
