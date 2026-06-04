<?php
declare(strict_types=1);

namespace App\Core;

class View
{
    /**
     * Render a view file wrapped in the layout.
     *
     * @param string               $view Path relative to views/ (e.g. 'entries/index')
     * @param array<string, mixed> $data Variables extracted into the view scope
     */
    public static function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        $viewFile   = dirname(__DIR__, 2) . '/views/' . $view . '.php';
        $layoutFile = dirname(__DIR__, 2) . '/views/layout.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        // Capture the inner view into $content, then render inside layout.
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require $layoutFile;
    }
}
