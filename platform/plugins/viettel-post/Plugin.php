<?php
namespace Botble\ViettelPost;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;

class Plugin extends PluginOperationAbstract
{
    public static function activated(): void
    {
    }

    public static function deactivated(): void
    {
    }

    public static function removed(): void
    {
        include __DIR__ . '/uninstall.php';
    }
}
