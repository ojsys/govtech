<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Plain-PHP template renderer. Views are PHP files under app/views.
 * A view may set a layout via $this->layout('layouts/app').
 */
final class View
{
    private ?string $layout = null;
    private array $sections = [];
    private array $shared = [];

    public function share(array $data): void
    {
        $this->shared = array_merge($this->shared, $data);
    }

    /** Render a view (relative to views/, no extension) with data; returns HTML. */
    public function render(string $template, array $data = []): string
    {
        $content = $this->renderPartial($template, $data);

        if ($this->layout !== null) {
            $layout = $this->layout;
            $this->layout = null;
            $this->sections['content'] = $content;
            return $this->renderPartial($layout, $data);
        }
        return $content;
    }

    /** Render a view without applying a layout (partials, emails). */
    public function renderPartial(string $template, array $data = []): string
    {
        $file = VIEW_PATH . '/' . ltrim($template, '/') . '.php';
        if (!is_file($file)) {
            throw new RuntimeException("View not found: {$template}");
        }
        extract($this->shared, EXTR_SKIP);
        extract($data, EXTR_SKIP);
        ob_start();
        include $file;
        return (string) ob_get_clean();
    }

    /* ---- methods available inside templates via $this ---- */

    public function layout(string $name): void
    {
        $this->layout = $name;
    }

    public function section(string $name): string
    {
        return $this->sections[$name] ?? '';
    }

    public function setSection(string $name, string $value): void
    {
        $this->sections[$name] = $value;
    }
}
