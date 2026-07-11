<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Models\Audit;
use App\Models\User;

/**
 * "My account" — lets any signed-in admin change their own password.
 * No role override: the base AdminController already requires a valid login,
 * and every role should be able to rotate their own credentials.
 */
final class AccountController extends AdminController
{
    private const MIN_LENGTH = 10;

    public function edit(Request $request, array $args = []): void
    {
        $this->render('admin/account', [
            'pageTitle' => 'My account',
        ]);
    }

    public function updatePassword(Request $request, array $args = []): void
    {
        Csrf::verify($request);

        $current = (string) $request->input('current_password', '');
        $new     = (string) $request->input('new_password', '');
        $confirm = (string) $request->input('confirm_password', '');

        // Always re-load from the DB so we verify against the live hash.
        $user = User::findByEmail((string) (Auth::user()['email'] ?? ''));
        if (!$user || !password_verify($current, $user['password_hash'])) {
            flash('error', 'Your current password is incorrect.');
            redirect('/admin/account');
        }
        if (mb_strlen($new) < self::MIN_LENGTH) {
            flash('error', 'New password must be at least ' . self::MIN_LENGTH . ' characters.');
            redirect('/admin/account');
        }
        if ($new !== $confirm) {
            flash('error', 'New password and confirmation do not match.');
            redirect('/admin/account');
        }
        if (password_verify($new, $user['password_hash'])) {
            flash('error', 'New password must be different from your current one.');
            redirect('/admin/account');
        }

        User::updatePassword((int) $user['id'], $new);
        Audit::log('update', 'user', (int) $user['id'], ['field' => 'password']);
        flash('ok', 'Your password has been updated.');
        redirect('/admin/account');
    }
}
