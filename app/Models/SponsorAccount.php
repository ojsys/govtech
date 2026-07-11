<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class SponsorAccount extends Model
{
    protected static string $table = 'sponsor_accounts';

    public static function findByEmail(string $email): ?array
    {
        return Database::first(
            'SELECT * FROM sponsor_accounts WHERE email = ? LIMIT 1',
            [mb_strtolower(trim($email))]
        );
    }

    public static function findByApplication(int $applicationId): ?array
    {
        return Database::first(
            'SELECT * FROM sponsor_accounts WHERE application_id = ? LIMIT 1',
            [$applicationId]
        );
    }

    /** Create a portal login for a confirmed sponsor. Returns the new id. */
    public static function create(int $applicationId, string $email, string $plainPassword): int
    {
        return Database::insert('sponsor_accounts', [
            'application_id' => $applicationId,
            'email'          => mb_strtolower(trim($email)),
            'password_hash'  => password_hash($plainPassword, PASSWORD_DEFAULT),
            'is_active'      => 1,
        ]);
    }

    public static function updatePassword(int $id, string $plain): void
    {
        Database::run(
            'UPDATE sponsor_accounts SET password_hash = ? WHERE id = ?',
            [password_hash($plain, PASSWORD_DEFAULT), $id]
        );
    }

    /** The application + package behind this account. */
    public static function context(int $accountId): ?array
    {
        return Database::first(
            'SELECT acc.id AS account_id, acc.email AS account_email,
                    a.*, p.name AS package_name, p.type AS package_type, p.comp_passes, p.price_kobo
             FROM sponsor_accounts acc
             JOIN sponsor_applications a ON a.id = acc.application_id
             JOIN sponsorship_packages p ON p.id = a.package_id
             WHERE acc.id = ? LIMIT 1',
            [$accountId]
        );
    }
}
