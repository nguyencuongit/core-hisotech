<?php

namespace Botble\Inventory;

use Illuminate\Support\Facades\Schema;
use Botble\PluginManagement\Abstracts\PluginOperationAbstract;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::dropIfExists('Inventories');
        Schema::dropIfExists('Inventories_translations');
    }
}
