<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Base controller. Provides view rendering + JSON + shared view data.
 */
abstract class Controller
{
    protected View $view;

    public function __construct()
    {
        $this->view = new View();
        $this->view->share([
            'csrf'    => Csrf::token(),
            'appName' => 'Nigeria GovTech Conference & Awards',
        ]);
        // Flash messages are read + cleared on demand in views via the flash() helper.
    }

    /** Render a page through the main layout and echo it. */
    protected function render(string $template, array $data = []): void
    {
        echo $this->view->render($template, $data);
    }

    protected function json(mixed $data, int $status = 200): never
    {
        json_response($data, $status);
    }
}
