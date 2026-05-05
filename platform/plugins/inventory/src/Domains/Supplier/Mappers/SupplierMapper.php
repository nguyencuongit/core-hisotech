<?php

namespace Botble\Inventory\Domains\Supplier\Mappers;

use Botble\Inventory\Domains\Supplier\Entities\SupplierAddressEntity;
use Botble\Inventory\Domains\Supplier\Entities\SupplierApprovalEntity;
use Botble\Inventory\Domains\Supplier\Entities\SupplierBankEntity;
use Botble\Inventory\Domains\Supplier\Entities\SupplierContactEntity;
use Botble\Inventory\Domains\Supplier\Entities\SupplierEntity;
use Botble\Inventory\Domains\Supplier\Entities\SupplierProductEntity;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DateTimeInterface;

final class SupplierMapper
{
    private const SUPPLIER_TYPE_ENUM_CLASSES = [
        'Botble\\Inventory\\Enums\\SupplierTypeEnum',
        'Botble\\Inventory\\Domains\\Supplier\\Enums\\SupplierTypeEnum',
    ];

    private const SUPPLIER_STATUS_ENUM_CLASSES = [
        'Botble\\Inventory\\Enums\\SupplierStatusEnum',
        'Botble\\Inventory\\Domains\\Supplier\\Enums\\SupplierStatusEnum',
    ];

    private const SUPPLIER_ADDRESS_TYPE_ENUM_CLASSES = [
        'Botble\\Inventory\\Enums\\SupplierAddressTypeEnum',
        'Botble\\Inventory\\Domains\\Supplier\\Enums\\SupplierAddressTypeEnum',
    ];

    public static function toEntity(array $supplier): SupplierEntity
    {
        return new SupplierEntity(
            id: self::get($supplier, 'id'),
            code: self::get($supplier, 'code'),
            name: self::get($supplier, 'name'),
            type: self::enumValue(self::get($supplier, 'type'), self::SUPPLIER_TYPE_ENUM_CLASSES),
            taxCode: self::get($supplier, 'tax_code'),
            website: self::get($supplier, 'website'),
            note: self::get($supplier, 'note'),
            status: self::enumValue(self::get($supplier, 'status'), self::SUPPLIER_STATUS_ENUM_CLASSES),
            metadata: self::arrayValue(self::get($supplier, 'metadata')),
            createdBy: self::get($supplier, 'created_by'),
            creatorName: self::nested($supplier, 'creator', 'name'),
            submittedBy: self::get($supplier, 'submitted_by'),
            submitterName: self::nested($supplier, 'submitter', 'name'),
            submittedAt: self::date(self::get($supplier, 'submitted_at')),
            approvedBy: self::get($supplier, 'approved_by'),
            approverName: self::nested($supplier, 'approver', 'name'),
            approvedAt: self::date(self::get($supplier, 'approved_at')),
            approvalNote: self::get($supplier, 'approval_note'),
            requiresReapproval: (bool) self::get($supplier, 'requires_reapproval', false),
            createdAt: self::date(self::get($supplier, 'created_at')),
            updatedAt: self::date(self::get($supplier, 'updated_at')),

            // Relations: chỉ map nếu Repository đã eager load và toArray() có key tương ứng.
            contacts: self::toContactEntities(self::relation($supplier, 'contacts')),
            addresses: self::toAddressEntities(self::relation($supplier, 'addresses')),
            banks: self::toBankEntities(self::relation($supplier, 'banks')),
            products: self::toProductEntities(self::relation($supplier, 'supplier_products', 'supplierProducts')),
            approvals: self::toApprovalEntities(self::relation($supplier, 'approvals')),
        );
    }

    public static function toEntities(iterable $suppliers): array
    {
        $entities = [];

        foreach ($suppliers as $supplier) {
            $entities[] = self::toEntity((array) $supplier);
        }

        return $entities;
    }

    public static function toContactEntity(array $contact): SupplierContactEntity
    {
        return new SupplierContactEntity(
            id: self::get($contact, 'id'),
            supplierId: self::get($contact, 'supplier_id'),
            isPrimary: (bool) self::get($contact, 'is_primary', false),
            name: self::get($contact, 'name'),
            position: self::get($contact, 'position'),
            phone: self::get($contact, 'phone'),
            email: self::get($contact, 'email'),
            identityNumber: self::get($contact, 'identity_number'),
            socialContact: self::arrayValue(self::get($contact, 'social_contact')),
        );
    }

    public static function toAddressEntity(array $address): SupplierAddressEntity
    {
        return new SupplierAddressEntity(
            id: self::get($address, 'id'),
            supplierId: self::get($address, 'supplier_id'),
            type: self::enumValue(self::get($address, 'type'), self::SUPPLIER_ADDRESS_TYPE_ENUM_CLASSES),
            isDefault: (bool) self::get($address, 'is_default', false),
            address: self::get($address, 'address'),
            wardId: self::get($address, 'ward_id'),
            districtId: self::get($address, 'district_id'),
            provinceId: self::get($address, 'province_id'),
            countryId: self::get($address, 'country_id'),
        );
    }

    public static function toBankEntity(array $bank): SupplierBankEntity
    {
        return new SupplierBankEntity(
            id: self::get($bank, 'id'),
            supplierId: self::get($bank, 'supplier_id'),
            isDefault: (bool) self::get($bank, 'is_default', false),
            bankName: self::get($bank, 'bank_name'),
            branch: self::get($bank, 'branch'),
            accountNumber: self::get($bank, 'account_number'),
            accountName: self::get($bank, 'account_name'),
        );
    }

    public static function toProductEntity(array $supplierProduct): SupplierProductEntity
    {
        $product = self::arrayValue(self::get($supplierProduct, 'product'));

        return new SupplierProductEntity(
            id: self::get($supplierProduct, 'id'),
            supplierId: self::get($supplierProduct, 'supplier_id'),
            productId: self::get($supplierProduct, 'product_id'),
            supplierSku: self::get($supplierProduct, 'supplier_sku'),
            purchasePrice: self::nullableFloat(self::get($supplierProduct, 'purchase_price')),
            moq: self::nullableInt(self::get($supplierProduct, 'moq')),
            leadTimeDays: self::nullableInt(self::get($supplierProduct, 'lead_time_days')),
            productName: self::get($product, 'name'),
            productSku: self::get($product, 'sku'),
            createdAt: self::date(self::get($supplierProduct, 'created_at')),
            updatedAt: self::date(self::get($supplierProduct, 'updated_at')),
        );
    }

    public static function toApprovalEntity(array $approval): SupplierApprovalEntity
    {
        return new SupplierApprovalEntity(
            id: self::get($approval, 'id'),
            supplierId: self::get($approval, 'supplier_id'),
            action: self::get($approval, 'action'),
            fromStatus: self::get($approval, 'from_status'),
            toStatus: self::get($approval, 'to_status'),
            note: self::get($approval, 'note'),
            actedBy: self::get($approval, 'acted_by'),
            actorName: self::nested($approval, 'actor', 'name'),
            actedAt: self::date(self::get($approval, 'acted_at')),
            meta: self::arrayValue(self::get($approval, 'meta')),
        );
    }

    protected static function toContactEntities(iterable $contacts): array
    {
        $entities = [];

        foreach ($contacts as $contact) {
            if (is_array($contact)) {
                $entities[] = self::toContactEntity($contact);
            }
        }

        return $entities;
    }

    protected static function toAddressEntities(iterable $addresses): array
    {
        $entities = [];

        foreach ($addresses as $address) {
            if (is_array($address)) {
                $entities[] = self::toAddressEntity($address);
            }
        }

        return $entities;
    }

    protected static function toBankEntities(iterable $banks): array
    {
        $entities = [];

        foreach ($banks as $bank) {
            if (is_array($bank)) {
                $entities[] = self::toBankEntity($bank);
            }
        }

        return $entities;
    }

    protected static function toProductEntities(iterable $products): array
    {
        $entities = [];

        foreach ($products as $product) {
            if (is_array($product)) {
                $entities[] = self::toProductEntity($product);
            }
        }

        return $entities;
    }

    protected static function toApprovalEntities(iterable $approvals): array
    {
        $entities = [];

        foreach ($approvals as $approval) {
            if (is_array($approval)) {
                $entities[] = self::toApprovalEntity($approval);
            }
        }

        return $entities;
    }

    protected static function relation(array $data, string $snakeKey, ?string $camelKey = null): array
    {
        $value = self::first($data, array_filter([$snakeKey, $camelKey]));

        return is_array($value) ? $value : [];
    }

    protected static function nested(array $data, string $relation, string $key): mixed
    {
        $value = self::get($data, $relation);

        if (! is_array($value)) {
            return null;
        }

        return self::get($value, $key);
    }

    protected static function get(array $data, string $key, mixed $default = null): mixed
    {
        return array_key_exists($key, $data) ? $data[$key] : $default;
    }

    protected static function first(array $data, array $keys, mixed $default = null): mixed
    {
        foreach ($keys as $key) {
            if ($key !== null && array_key_exists($key, $data)) {
                return $data[$key];
            }
        }

        return $default;
    }

    protected static function arrayValue(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && trim($value) !== '') {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    protected static function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    protected static function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    protected static function enumValue(mixed $value, array $enumClasses): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        foreach ($enumClasses as $enumClass) {
            if (! class_exists($enumClass) && ! enum_exists($enumClass)) {
                continue;
            }

            if ($value instanceof $enumClass) {
                return $value;
            }

            if (enum_exists($enumClass) && method_exists($enumClass, 'tryFrom')) {
                $enum = $enumClass::tryFrom((string) $value);

                if ($enum !== null) {
                    return $enum;
                }
            }

            if (method_exists($enumClass, 'make')) {
                return $enumClass::make($value);
            }
        }

        return $value;
    }

    protected static function date(mixed $value): ?CarbonImmutable
    {
        if (! $value) {
            return null;
        }

        if ($value instanceof CarbonImmutable) {
            return $value;
        }

        if ($value instanceof CarbonInterface) {
            return CarbonImmutable::instance($value->toDateTime());
        }

        if ($value instanceof DateTimeInterface) {
            return CarbonImmutable::instance($value);
        }

        return CarbonImmutable::parse($value);
    }
}