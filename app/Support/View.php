<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;

class View
{
    public static function render(
        string $view,
        array $data = [],
        ?string $layout = 'layouts/app'
    ): void {
        $viewPath = __DIR__ . '/../../resources/views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            throw new RuntimeException(
                "View not found: {$view}"
            );
        }

        extract($data, EXTR_SKIP);

        if ($layout === null) {
            require $viewPath;
            return;
        }

        ob_start();

        require $viewPath;

        $content = ob_get_clean();

        $layoutPath = __DIR__
            . '/../../resources/views/'
            . $layout
            . '.php';

        if (!file_exists($layoutPath)) {
            throw new RuntimeException(
                "Layout not found: {$layout}"
            );
        }

        require $layoutPath;
    }
}