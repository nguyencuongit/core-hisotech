<?php

namespace Botble\Inventory\Domains\Supplier\Entities;

use Botble\Inventory\Enums\SupplierStatusEnum;
use Botble\Inventory\Enums\SupplierTypeEnum;
use Carbon\CarbonImmutable;

final class SupplierEntity
{
    /**
     * @param list<SupplierContactEntity> $contacts
     * @param list<SupplierAddressEntity> $addresses
     * @param list<SupplierBankEntity> $banks
     * @param list<SupplierProductEntity> $products
     * @param list<SupplierApprovalEntity> $approvals
     */
    public function __construct(
        public readonly int|string $id,
        public readonly ?string $code,
        public readonly ?string $name,
        public readonly ?SupplierTypeEnum $type,
        public readonly ?string $taxCode,
        public readonly ?string $website,
        public readonly ?string $note,
        public readonly ?SupplierStatusEnum $status,
        public readonly array $metadata,
        public readonly int|string|null $createdBy,
        public readonly ?string $creatorName,
        public readonly int|string|null $submittedBy,
        public readonly ?string $submitterName,
        public readonly ?CarbonImmutable $submittedAt,
        public readonly int|string|null $approvedBy,
        public readonly ?string $approverName,
        public readonly ?CarbonImmutable $approvedAt,
        public readonly ?string $approvalNote,
        public readonly bool $requiresReapproval,
        public readonly ?CarbonImmutable $createdAt,
        public readonly ?CarbonImmutable $updatedAt,
        public readonly array $contacts = [],
        public readonly array $addresses = [],
        public readonly array $banks = [],
        public readonly array $products = [],
        public readonly array $approvals = [],
    ) {
    }

    public function getKey(): int|string
    {
        return $this->id;
    }

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

    public function defaultAddress(): ?SupplierAddressEntity
    {
        foreach ($this->addresses as $address) {
            if ($address->isDefault) {
                return $address;
            }
        }

        return $this->addresses[0] ?? null;
    }

    public function defaultBank(): ?SupplierBankEntity
    {
        foreach ($this->banks as $bank) {
            if ($bank->isDefault) {
                return $bank;
            }
        }

        return $this->banks[0] ?? null;
    }
}
