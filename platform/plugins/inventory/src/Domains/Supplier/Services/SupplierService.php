<?php

namespace Botble\Inventory\Domains\Supplier\Services;

use Botble\Base\Events\AdminNotificationEvent;
use Botble\Base\Supports\AdminNotificationItem;
use Botble\Inventory\Domains\Supplier\Models\Supplier;
use Botble\Inventory\Domains\Supplier\Repositories\Interfaces\SupplierInterface;
use Botble\Inventory\Enums\SupplierStatusEnum;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SupplierService
{
    public function __construct(
        protected SupplierInterface $suppliers,
    ) {
    }

    public function create(array $data): Supplier
    {
        return DB::transaction(function () use ($data) {
            $supplierAttributes = $this->prepareSupplierData($data);
            $toStatus = Arr::get($supplierAttributes, 'status');
            $supplier = $this->suppliers->createSupplier($supplierAttributes);

            $this->syncChildren($supplier, $data);
            $supplier = $this->suppliers->reload($supplier);

            $this->logApprovalAction(
                $supplier,
                'create',
                null,
                $supplier->status?->value,
                Arr::get($data, 'approval_note')
            );

            if ($toStatus === SupplierStatusEnum::PENDING_APPROVAL->value) {
                $this->logApprovalAction(
                    $supplier,
                    'submit',
                    SupplierStatusEnum::DRAFT->value,
                    SupplierStatusEnum::PENDING_APPROVAL->value,
                    Arr::get($data, 'approval_note')
                );

                $this->notifySuperAdminForApproval($supplier);
            }

            if ($toStatus === SupplierStatusEnum::ACTIVE->value) {
                $this->logApprovalAction(
                    $supplier,
                    'approve',
                    SupplierStatusEnum::PENDING_APPROVAL->value,
                    SupplierStatusEnum::ACTIVE->value,
                    Arr::get($data, 'approval_note')
                );
            }

            return $this->suppliers->reload($supplier, ['contacts', 'addresses', 'banks', 'supplierProducts.product', 'approvals']);
        });
    }

    public function update(Supplier $supplier, array $data): Supplier
    {
        return DB::transaction(function () use ($supplier, $data) {
            $supplier = $this->suppliers->reload($supplier, ['banks', 'addresses']);
            $oldSensitive = $this->sensitiveSnapshot($supplier);

            $this->suppliers->updateSupplier($supplier, $this->prepareSupplierData($data, $supplier));
            $this->syncChildren($supplier, $data, true);

            $freshSupplier = $this->suppliers->reload($supplier, ['banks', 'addresses']);
            $newSensitive = $this->sensitiveSnapshot($freshSupplier);

            if ($oldSensitive !== $newSensitive && $freshSupplier->status?->isApproved()) {
                $this->suppliers->updateSupplier($freshSupplier, [
                    'requires_reapproval' => true,
                    'status' => SupplierStatusEnum::PENDING_APPROVAL->value,
                ]);

                $this->logApprovalAction(
                    $freshSupplier,
                    'resubmit_for_reapproval',
                    SupplierStatusEnum::ACTIVE->value,
                    SupplierStatusEnum::PENDING_APPROVAL->value,
                    'Sensitive supplier data changed'
                );
                $this->notifySuperAdminForApproval($freshSupplier);
            }

            return $this->suppliers->reload($supplier, ['contacts', 'addresses', 'banks', 'supplierProducts.product', 'approvals']);
        });
    }

    public function submitForApproval(Supplier $supplier, ?string $note = null): Supplier
    {
        return DB::transaction(function () use ($supplier, $note) {
            $fromStatus = $supplier->status?->value;

            $this->suppliers->updateSupplier($supplier, [
                'status' => SupplierStatusEnum::PENDING_APPROVAL->value,
                'submitted_by' => auth()->id(),
                'submitted_at' => now(),
                'approval_note' => $note,
            ]);

            $this->logApprovalAction($supplier, 'submit', $fromStatus, SupplierStatusEnum::PENDING_APPROVAL->value, $note);
            $this->notifySuperAdminForApproval($this->suppliers->reload($supplier));

            return $this->suppliers->reload($supplier);
        });
    }

    public function approve(Supplier $supplier, ?string $note = null): Supplier
    {
        return DB::transaction(function () use ($supplier, $note) {
            $fromStatus = $supplier->status?->value;

            $this->suppliers->updateSupplier($supplier, [
                'status' => SupplierStatusEnum::ACTIVE->value,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'approval_note' => $note,
                'requires_reapproval' => false,
            ]);

            $this->logApprovalAction($supplier, 'approve', $fromStatus, SupplierStatusEnum::ACTIVE->value, $note);

            return $this->suppliers->reload($supplier);
        });
    }

    public function reject(Supplier $supplier, ?string $note = null): Supplier
    {
        return DB::transaction(function () use ($supplier, $note) {
            $fromStatus = $supplier->status?->value;

            $this->suppliers->updateSupplier($supplier, [
                'status' => SupplierStatusEnum::REJECTED->value,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'approval_note' => $note,
            ]);

            $this->logApprovalAction($supplier, 'reject', $fromStatus, SupplierStatusEnum::REJECTED->value, $note);

            return $this->suppliers->reload($supplier);
        });
    }

    protected function prepareSupplierData(array $data, ?Supplier $supplier = null): array
    {
        $code = Arr::get($data, 'code');
        $status = Arr::get($data, 'status');
        $now = now();

        if (! auth()->user()?->isSuperUser()) {
            $status = $supplier?->status?->value ?? SupplierStatusEnum::PENDING_APPROVAL->value;
            $data['status'] = $status;
        }

        if (! $code) {
            $code = $supplier?->code ?: $this->generateCode();
        }

        $data = array_merge($data, ['code' => $code]);

        if (! $supplier) {
            $data['created_by'] = auth()->id();
        }

        if ($status === SupplierStatusEnum::PENDING_APPROVAL->value && (! $supplier || ! $supplier->submitted_by)) {
            $data['submitted_by'] = auth()->id();
            $data['submitted_at'] = $now;
        }

        if ($status === SupplierStatusEnum::ACTIVE->value && (! $supplier || ! $supplier->approved_by)) {
            $data['approved_by'] = auth()->id();
            $data['approved_at'] = $now;
            $data['requires_reapproval'] = false;
        }

        return Arr::only($data, [
            'code',
            'name',
            'type',
            'tax_code',
            'website',
            'note',
            'status',
            'metadata',
            'created_by',
            'submitted_by',
            'submitted_at',
            'approved_by',
            'approved_at',
            'approval_note',
            'requires_reapproval',
        ]);
    }

    protected function syncChildren(Supplier $supplier, array $data, bool $replace = false): void
    {
        if ($replace) {
            $this->suppliers->deleteChildren($supplier);
        }

        $this->syncContacts($supplier, Arr::get($data, 'contacts', []));
        $this->syncAddresses($supplier, Arr::get($data, 'addresses', []));
        $this->syncBanks($supplier, Arr::get($data, 'banks', []));
        $this->syncProducts($supplier, Arr::get($data, 'supplier_products', []));
    }

    protected function syncContacts(Supplier $supplier, array $contacts): void
    {
        $rows = array_values(array_filter($contacts, fn ($item) => Arr::get($item, 'name')));

        if (! count($rows)) {
            return;
        }

        $firstPrimarySet = false;

        foreach ($rows as $index => $contact) {
            $wantsPrimary = (bool) Arr::get($contact, 'is_primary', false);
            $isPrimary = false;

            if ($wantsPrimary && ! $firstPrimarySet) {
                $isPrimary = true;
                $firstPrimarySet = true;
            } elseif (! $firstPrimarySet && $index === 0) {
                $isPrimary = true;
                $firstPrimarySet = true;
            }

            $this->suppliers->createContact($supplier, [
                'name' => Arr::get($contact, 'name'),
                'position' => Arr::get($contact, 'position'),
                'phone' => Arr::get($contact, 'phone'),
                'email' => Arr::get($contact, 'email'),
                'identity_number' => Arr::get($contact, 'identity_number'),
                'social_contact' => Arr::get($contact, 'social_contact', []),
                'is_primary' => $isPrimary,
            ]);
        }
    }

    protected function syncAddresses(Supplier $supplier, array $addresses): void
    {
        $rows = array_values(array_filter($addresses, fn ($item) => Arr::get($item, 'address')));

        if (! count($rows)) {
            return;
        }

        $firstDefaultSet = false;

        foreach ($rows as $index => $address) {
            $wantsDefault = (bool) Arr::get($address, 'is_default', false);
            $isDefault = false;

            if ($wantsDefault && ! $firstDefaultSet) {
                $isDefault = true;
                $firstDefaultSet = true;
            } elseif (! $firstDefaultSet && $index === 0) {
                $isDefault = true;
                $firstDefaultSet = true;
            }

            $this->suppliers->createAddress($supplier, [
                'type' => Arr::get($address, 'type'),
                'address' => Arr::get($address, 'address'),
                'ward_id' => Arr::get($address, 'ward_id'),
                'district_id' => Arr::get($address, 'district_id'),
                'province_id' => Arr::get($address, 'province_id'),
                'country_id' => Arr::get($address, 'country_id'),
                'is_default' => $isDefault,
            ]);
        }
    }

    protected function syncBanks(Supplier $supplier, array $banks): void
    {
        $rows = array_values(array_filter($banks, fn ($item) => Arr::get($item, 'bank_name') && Arr::get($item, 'account_number')));

        if (! count($rows)) {
            return;
        }

        $firstDefaultSet = false;

        foreach ($rows as $index => $bank) {
            $wantsDefault = (bool) Arr::get($bank, 'is_default', false);
            $isDefault = false;

            if ($wantsDefault && ! $firstDefaultSet) {
                $isDefault = true;
                $firstDefaultSet = true;
            } elseif (! $firstDefaultSet && $index === 0) {
                $isDefault = true;
                $firstDefaultSet = true;
            }

            $this->suppliers->createBank($supplier, [
                'bank_name' => Arr::get($bank, 'bank_name'),
                'branch' => Arr::get($bank, 'branch'),
                'account_number' => Arr::get($bank, 'account_number'),
                'account_name' => Arr::get($bank, 'account_name'),
                'is_default' => $isDefault,
            ]);
        }
    }

    protected function syncProducts(Supplier $supplier, array $products): void
    {
        $seenProductIds = [];

        foreach (array_values($products) as $product) {
            $productId = (int) Arr::get($product, 'product_id');

            if (! $productId || in_array($productId, $seenProductIds, true)) {
                continue;
            }

            $seenProductIds[] = $productId;

            $this->suppliers->updateOrCreateProduct($supplier, $productId, [
                'supplier_sku' => Arr::get($product, 'supplier_sku'),
                'purchase_price' => $this->nullableNumber(Arr::get($product, 'purchase_price')),
                'moq' => $this->nullableInteger(Arr::get($product, 'moq')),
                'lead_time_days' => $this->nullableInteger(Arr::get($product, 'lead_time_days')),
            ]);
        }
    }

    protected function nullableNumber(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) str_replace(',', '', (string) $value);
    }

    protected function nullableInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    protected function logApprovalAction(Supplier $supplier, string $action, ?string $from, ?string $to, ?string $note): void
    {
        $actedAt = match ($action) {
            'create' => $supplier->created_at ?: now(),
            'submit' => $supplier->submitted_at ?: now(),
            'approve', 'reject' => $supplier->approved_at ?: now(),
            default => now(),
        };

        $actedBy = match ($action) {
            'create' => $supplier->created_by ?: auth()->id(),
            'submit' => $supplier->submitted_by ?: auth()->id(),
            'approve', 'reject' => $supplier->approved_by ?: auth()->id(),
            default => auth()->id(),
        };

        $this->suppliers->createApproval($supplier, [
            'action' => $action,
            'from_status' => $from,
            'to_status' => $to,
            'note' => $note,
            'acted_by' => $actedBy,
            'acted_at' => $actedAt,
            'meta' => [],
        ]);
    }

    protected function notifySuperAdminForApproval(Supplier $supplier): void
    {
        event(new AdminNotificationEvent(
            AdminNotificationItem::make()
                ->title(trans('plugins/inventory::inventory.supplier.notifications.pending_approval.title', [
                    'code' => $supplier->code,
                ]))
                ->description(trans('plugins/inventory::inventory.supplier.notifications.pending_approval.description', [
                    'name' => $supplier->name,
                    'user' => auth()->user()?->name ?: trans('core/base::base.panel.system'),
                ]))
                ->action(
                    trans('plugins/inventory::inventory.supplier.notifications.pending_approval.action'),
                    route('inventory.suppliers.approval', $supplier->getKey())
                )
                ->permission(defined('ACL_ROLE_SUPER_USER') ? ACL_ROLE_SUPER_USER : 'superuser')
        ));
    }

    protected function generateCode(): string
    {
        do {
            $code = 'NCC' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while ($this->suppliers->codeExists($code));

        return $code;
    }

    protected function sensitiveSnapshot(Supplier $supplier): array
    {
        return [
            'name' => $supplier->name,
            'tax_code' => $supplier->tax_code,
            'banks' => $supplier->banks->map(fn ($bank) => [
                'bank_name' => $bank->bank_name,
                'account_name' => $bank->account_name,
                'account_number' => $bank->account_number,
            ])->values()->all(),
            'addresses' => $supplier->addresses->map(fn ($address) => [
                'type' => $address->type,
                'address' => $address->address,
            ])->values()->all(),
        ];
    }
}
