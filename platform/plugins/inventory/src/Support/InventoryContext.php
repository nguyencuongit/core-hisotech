<?php

namespace Botble\Inventory\Support;

class InventoryContext
{
    protected ?array $warehouseId = null;

    protected bool $isSuperAdmin = false;

    public function setWarehouseIds(?array $warehouseId): static
    {
        $this->warehouseId = $warehouseId;

        return $this;
    }

    public function warehouseIds(): ?array
    {
        return $this->warehouseId;
    }

    public function setSuperAdmin(bool $isSuperAdmin): static
    {
        $this->isSuperAdmin = $isSuperAdmin;

        return $this;
    }

    public function isSuperAdmin(): bool
    {
        return $this->isSuperAdmin;
    }

    public function hasWarehouse(): bool
    {
        return ! is_null($this->warehouseId);
    }
}