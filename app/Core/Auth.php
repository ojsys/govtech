<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\User;

/**
 * Admin authentication + role gating. Sessions regenerate id on login.
 * Roles: superadmin (all), editor (content), finance (orders/exports), checkin.
 */
final class Auth
{
    private const SK = '_admin';

    /** Attempt login. Returns true on success. */
    public static function attempt(string $email, string $password): bool
    {
        $user = User::findByEmail($email);
        if (!$user || (int) $user['is_active'] !== 1) {
            // Constant-ish time: still run a verify to blunt user enumeration.
            password_verify($password, '$2y$10$usesomesillystringforsalt0000000000000000000000000000');
            return false;
        }
        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }
        // Upgrade legacy hashes transparently.
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            User::updatePassword((int) $user['id'], $password);
        }

        session_regenerate_id(true);
        $_SESSION[self::SK] = [
            'id'    => (int) $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
        ];
        User::touchLogin((int) $user['id']);
        return true;
    }

    public static function check(): bool
    {
        return isset($_SESSION[self::SK]['id']);
    }

    public static function user(): ?array
    {
        return $_SESSION[self::SK] ?? null;
    }

    public static function id(): ?int
    {
        return isset($_SESSION[self::SK]['id']) ? (int) $_SESSION[self::SK]['id'] : null;
    }

    public static function role(): ?string
    {
        return $_SESSION[self::SK]['role'] ?? null;
    }

    public static function logout(): void
    {
        unset($_SESSION[self::SK]);
        session_regenerate_id(true);
    }

    /** superadmin passes everything; otherwise role must be in the allowed list. */
    public static function can(array $roles): bool
    {
        $role = self::role();
        return $role === 'superadmin' || in_array($role, $roles, true);
    }

    /** Redirect to login if not authenticated. Remembers intended path. */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            $_SESSION['_intended'] = $_SERVER['REQUEST_URI'] ?? '/admin';
            redirect('/admin/login');
        }
    }

    /** 403 if the current user's role isn't allowed. */
    public static function requireRole(array $roles): void
    {
        self::requireLogin();
        if (!self::can($roles)) {
            http_response_code(403);
            echo '<!doctype html><meta charset="utf-8"><title>403</title>'
               . '<body style="font:16px system-ui;background:#07140E;color:#E8EFE9;display:grid;place-items:center;height:100vh;text-align:center">'
               . '<div><h1>403 — Not allowed</h1><p>Your role doesn\'t have access to this area.</p>'
               . '<p><a style="color:#C9A227" href="' . e(url('/admin')) . '">Back to dashboard</a></p></div>';
            exit;
        }
    }
}
