<?php

namespace Botble\Inventory\Domains\Warehouse\Services;

use Botble\Inventory\Domains\Warehouse\Models\Warehouse;
use Botble\Inventory\Domains\Warehouse\Models\WarehouseLocation;
use Botble\Inventory\Domains\Warehouse\Support\WarehouseTemplateRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WarehouseTemplateService
{
    public function apply(Warehouse $warehouse, string $templateCode, string $mode = 'append'): void
    {
        $template = WarehouseTemplateRegistry::get($templateCode);

        if (! $template) {
            throw ValidationException::withMessages([
                'template_code' => 'Mẫu kho không hợp lệ.',
            ]);
        }

        if (! in_array($mode, ['append', 'overwrite'], true)) {
            throw ValidationException::withMessages([
                'mode' => 'Chế độ áp dụng không hợp lệ.',
            ]);
        }

        $templateCodes = $this->collectCodes($template['preview'] ?? []);
        $duplicateCodes = array_keys(array_filter(array_count_values($templateCodes), static fn (int $count): bool => $count > 1));

        if ($duplicateCodes !== []) {
            throw ValidationException::withMessages([
                'template_code' => 'Mẫu kho đang có mã vị trí bị trùng: ' . implode(', ', $duplicateCodes) . '.',
            ]);
        }

        if ($mode === 'append') {
            $conflictedCodes = WarehouseLocation::query()
                ->where('warehouse_id', $warehouse->getKey())
                ->whereIn('code', $templateCodes)
                ->orderBy('code')
                ->pluck('code')
                ->all();

            if ($conflictedCodes !== []) {
                throw ValidationException::withMessages([
                    'template_code' => 'Kho này đã có sẵn các mã vị trí trùng với mẫu: ' . implode(', ', $conflictedCodes) . '. Hãy dùng "Ghi đè cây hiện tại" hoặc đổi mã vị trí trước khi áp mẫu.',
                ]);
            }
        }

        DB::transaction(function () use ($warehouse, $template, $mode): void {
            if ($mode === 'overwrite') {
                $this->deleteGeneratedLocations($warehouse);
                $warehouse->maps()->delete();
            }

            foreach ($template['preview'] as $node) {
                $this->createNode($warehouse, $node, null);
            }

            $defaultMapBlueprint = $template['default_map_blueprint'] ?? null;

            if ($defaultMapBlueprint && ! $warehouse->maps()->exists()) {
                app(WarehouseMapService::class)->createFromBlueprint($warehouse, $defaultMapBlueprint);
            }
        });
    }

    protected function createNode(Warehouse $warehouse, array $node, ?int $parentId): void
    {
        $location = app(WarehouseLocationService::class)->create($warehouse, [
            'parent_id' => $parentId,
            'code' => $node['code'],
            'name' => $node['name'],
            'type' => $node['type'],
            'status' => true,
            'description' => 'Tạo tự động từ mẫu kho.',
        ]);

        foreach ($node['children'] ?? [] as $child) {
            $this->createNode($warehouse, $child, $location->getKey());
        }
    }

    protected function deleteGeneratedLocations(Warehouse $warehouse): void
    {
        $warehouse->locations()->delete();
    }

    protected function collectCodes(array $nodes): array
    {
        $codes = [];

        foreach ($nodes as $node) {
            if (! empty($node['code'])) {
                $codes[] = (string) $node['code'];
            }

            if (! empty($node['children']) && is_array($node['children'])) {
                $codes = [...$codes, ...$this->collectCodes($node['children'])];
            }
        }

        return $codes;
    }
}
