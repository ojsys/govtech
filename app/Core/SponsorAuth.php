<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\SponsorAccount;

/**
 * Authentication for the sponsor/exhibitor portal. Separate session namespace
 * from the admin Auth so the two never collide.
 */
final class SponsorAuth
{
    private const SK = '_sponsor';

    public static function attempt(string $email, string $password): bool
    {
        $acc = SponsorAccount::findByEmail($email);
        if (!$acc || (int) $acc['is_active'] !== 1) {
            password_verify($password, '$2y$10$usesomesillystringforsalt0000000000000000000000000000');
            return false;
        }
        if (!password_verify($password, $acc['password_hash'])) {
            return false;
        }
        if (password_needs_rehash($acc['password_hash'], PASSWORD_DEFAULT)) {
            SponsorAccount::updatePassword((int) $acc['id'], $password);
        }
        session_regenerate_id(true);
        $_SESSION[self::SK] = [
            'id'    => (int) $acc['id'],
            'email' => $acc['email'],
            'application_id' => (int) $acc['application_id'],
        ];
        return true;
    }

    public static function check(): bool
    {
        return isset($_SESSION[self::SK]['id']);
    }

    public static function id(): ?int
    {
        return isset($_SESSION[self::SK]['id']) ? (int) $_SESSION[self::SK]['id'] : null;
    }

    public static function user(): ?array
    {
        return $_SESSION[self::SK] ?? null;
    }

    public static function logout(): void
    {
        unset($_SESSION[self::SK]);
        session_regenerate_id(true);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            $_SESSION['_portal_intended'] = $_SERVER['REQUEST_URI'] ?? '/portal';
            redirect('/portal/login');
        }
    }
}
