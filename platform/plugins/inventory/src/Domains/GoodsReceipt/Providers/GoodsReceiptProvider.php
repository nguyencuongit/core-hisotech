<?php

namespace Botble\Inventory\Domains\GoodsReceipt\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\ServiceProvider;

class GoodsReceiptProvider extends ServiceProvider
{
    public function boot(): void
    {
        DashboardMenu::default()->beforeRetrieving(function (): void {
            DashboardMenu::registerItem([
                'id' => 'cms-plugins-inventory-goods-receipts',
                'priority' => 9,
                'parent_id' => 'cms-plugins-inventory',
                'name' => 'plugins/inventory::inventory.goods_receipt.name',
                'icon' => 'ti ti-clipboard-list',
                'url' => route('inventory.goods-receipts.index'),
                'permissions' => ['inventory.goods-receipts.index'],
            ]);
        });
    }
}
