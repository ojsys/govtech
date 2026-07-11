<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\View;

/**
 * Base for all authenticated admin controllers. Enforces login + (optionally)
 * a role, and renders through the admin layout with shared chrome data.
 */
abstract class AdminController
{
    protected View $view;

    /** Subclasses may narrow this; superadmin always passes. */
    protected array $allowedRoles = ['editor', 'finance', 'checkin'];

    public function __construct()
    {
        Auth::requireRole($this->allowedRoles);

        $this->view = new View();
        $this->view->share([
            'csrf'     => Csrf::token(),
            'authUser' => Auth::user(),
            'authRole' => Auth::role(),
        ]);
        // Flash messages are read + cleared on demand via the flash() helper.
    }

    protected function render(string $template, array $data = []): void
    {
        echo $this->view->render($template, $data);
    }

    protected function json(mixed $data, int $status = 200): never
    {
        json_response($data, $status);
    }
}
