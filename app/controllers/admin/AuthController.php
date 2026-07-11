<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\RateLimit;
use App\Core\Request;
use App\Core\View;
use App\Models\Audit;

/**
 * Admin login / logout. NOT extending AdminController (no auth required to log in).
 */
final class AuthController
{
    public function showLogin(Request $request, array $args = []): void
    {
        if (Auth::check()) {
            redirect('/admin');
        }
        $view = new View();
        echo $view->render('admin/login', [
            'csrf'  => Csrf::token(),
            'error' => flash('error'),
        ]);
    }

    public function login(Request $request, array $args = []): void
    {
        Csrf::verify($request);

        if (!RateLimit::allow('admin-login:' . $request->ip(), 6, 300)) {
            flash('error', 'Too many attempts. Please wait a few minutes and try again.');
            redirect('/admin/login');
        }

        $email = $request->str('email');
        $password = (string) $request->input('password', '');

        if (Auth::attempt($email, $password)) {
            Audit::log('login', 'user', Auth::id());
            $intended = $_SESSION['_intended'] ?? '/admin';
            unset($_SESSION['_intended']);
            redirect($intended);
        }

        flash('error', 'Invalid email or password.');
        redirect('/admin/login');
    }

    public function logout(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        Auth::logout();
        redirect('/admin/login');
    }
}
