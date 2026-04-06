<?php

namespace Fireworks\Providers;

use Botble\Base\Supports\ServiceProvider;

class FireworksServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // 1. Gắn class body (bật toàn site để test)
        add_filter('theme_front_body_attributes', function ($attrs) {
            return trim($attrs . ' fireworks-on');
        });

        // 2. Inject CSS trực tiếp
        add_filter('theme_front_header_content', function (?string $html) {
            return $html
                . '<link rel="stylesheet" href="'
                . asset('vendor/core/fireworks/css/fireworks.css')
                . '">';
        }, 999);

        // 3. Inject JS trực tiếp (QUAN TRỌNG)
        add_filter('theme_front_footer_content', function (?string $html) {
            return $html
                . '<canvas id="fireworks"></canvas>'
                . '<script src="'
                . asset('vendor/core/fireworks/js/fireworks.js')
                . '"></script>';
        }, 999);
    }
}
