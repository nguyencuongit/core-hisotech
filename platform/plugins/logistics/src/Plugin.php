<?php

namespace Botble\Logistics;

use Illuminate\Support\Facades\Schema;
use Botble\PluginManagement\Abstracts\PluginOperationAbstract;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::dropIfExists('Logistics');
        Schema::dropIfExists('Logistics_translations');
    }
}
