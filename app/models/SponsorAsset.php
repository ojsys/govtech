<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class SponsorAsset extends Model
{
    protected static string $table = 'sponsor_assets';

    public const TYPES = ['logo', 'brochure_ad', 'screen_ad'];

    public static function create(int $accountId, string $type, string $filePath): int
    {
        return Database::insert('sponsor_assets', [
            'account_id' => $accountId,
            'type'       => in_array($type, self::TYPES, true) ? $type : 'logo',
            'file_path'  => $filePath,
            'status'     => 'pending',
        ]);
    }

    public static function forAccount(int $accountId): array
    {
        return Database::all(
            'SELECT * FROM sponsor_assets WHERE account_id = ? ORDER BY id DESC',
            [$accountId]
        );
    }

    public static function find(int $id): ?array
    {
        return Database::first('SELECT * FROM sponsor_assets WHERE id = ? LIMIT 1', [$id]);
    }

    public static function setStatus(int $id, string $status, string $notes = ''): bool
    {
        if (!in_array($status, ['pending', 'approved', 'rejected'], true)) {
            return false;
        }
        Database::run('UPDATE sponsor_assets SET status = ?, notes = ? WHERE id = ?', [$status, $notes, $id]);
        return true;
    }

    /* ---- Admin ---- */

    public static function pendingForAdmin(): array
    {
        return Database::all(
            "SELECT s.*, a.company_name FROM sponsor_assets s
             JOIN sponsor_accounts acc ON acc.id = s.account_id
             JOIN sponsor_applications a ON a.id = acc.application_id
             ORDER BY (s.status = 'pending') DESC, s.id DESC"
        );
    }
}
