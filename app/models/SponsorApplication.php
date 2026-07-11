<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class SponsorApplication extends Model
{
    protected static string $table = 'sponsor_applications';

    public const STATUSES = ['new', 'contacted', 'invoiced', 'confirmed', 'paid'];

    public static function create(array $d): int
    {
        return Database::insert('sponsor_applications', [
            'package_id'   => (int) $d['package_id'],
            'company_name' => $d['company_name'] ?? '',
            'contact_name' => $d['contact_name'] ?? '',
            'email'        => mb_strtolower(trim($d['email'] ?? '')),
            'phone'        => $d['phone'] ?? '',
            'logo_path'    => $d['logo_path'] ?? '',
            'message'      => $d['message'] ?? '',
            'status'       => 'new',
        ]);
    }

    public static function withPackage(int $id): ?array
    {
        return Database::first(
            'SELECT a.*, p.name AS package_name, p.type AS package_type, p.price_kobo, p.comp_passes
             FROM sponsor_applications a
             JOIN sponsorship_packages p ON p.id = a.package_id
             WHERE a.id = ? LIMIT 1',
            [$id]
        );
    }

    public static function setStatus(int $id, string $status): bool
    {
        if (!in_array($status, self::STATUSES, true)) {
            return false;
        }
        Database::run('UPDATE sponsor_applications SET status = ? WHERE id = ?', [$status, $id]);
        return true;
    }

    /* ---- Admin ---- */

    public static function forAdmin(string $status = ''): array
    {
        $where = '';
        $params = [];
        if (in_array($status, self::STATUSES, true)) {
            $where = 'WHERE a.status = ?';
            $params[] = $status;
        }
        return Database::all(
            "SELECT a.*, p.name AS package_name, p.type AS package_type, p.comp_passes,
                    (SELECT COUNT(*) FROM sponsor_accounts sa WHERE sa.application_id = a.id) AS has_account
             FROM sponsor_applications a
             JOIN sponsorship_packages p ON p.id = a.package_id
             {$where} ORDER BY a.id DESC",
            $params
        );
    }

    public static function stats(): array
    {
        return [
            'total'     => (int) Database::scalar('SELECT COUNT(*) FROM sponsor_applications'),
            'new'       => (int) Database::scalar("SELECT COUNT(*) FROM sponsor_applications WHERE status = 'new'"),
            'confirmed' => (int) Database::scalar("SELECT COUNT(*) FROM sponsor_applications WHERE status IN ('confirmed','paid')"),
        ];
    }
}
