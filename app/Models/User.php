<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class User extends Model
{
    protected static string $table = 'users';

    public static function findByEmail(string $email): ?array
    {
        return Database::first('SELECT * FROM users WHERE email = ? LIMIT 1', [$email]);
    }

    public static function touchLogin(int $id): void
    {
        Database::run('UPDATE users SET last_login = ? WHERE id = ?', [date('Y-m-d H:i:s'), $id]);
    }

    public static function updatePassword(int $id, string $plain): void
    {
        Database::run(
            'UPDATE users SET password_hash = ? WHERE id = ?',
            [password_hash($plain, PASSWORD_DEFAULT), $id]
        );
    }

    public static function all(): array
    {
        return Database::all('SELECT id, name, email, role, is_active, last_login FROM users ORDER BY id ASC');
    }
}
