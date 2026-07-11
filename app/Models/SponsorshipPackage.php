<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class SponsorshipPackage extends Model
{
    protected static string $table = 'sponsorship_packages';

    /** @return array{sponsor:array,exhibition:array} */
    public static function grouped(): array
    {
        $rows = Database::all(
            'SELECT * FROM sponsorship_packages WHERE event_id = ? AND is_active = 1 ORDER BY sort ASC, id ASC',
            [self::eventId()]
        );
        $out = ['sponsor' => [], 'exhibition' => []];
        foreach ($rows as $row) {
            $out[$row['type'] === 'exhibition' ? 'exhibition' : 'sponsor'][] = $row;
        }
        return $out;
    }

    public static function findActiveById(int $id): ?array
    {
        return Database::first(
            'SELECT * FROM sponsorship_packages WHERE id = ? AND event_id = ? AND is_active = 1 LIMIT 1',
            [$id, self::eventId()]
        );
    }

    /* ---- Admin CRUD ---- */

    public static function allForAdmin(): array
    {
        return Database::all(
            'SELECT * FROM sponsorship_packages WHERE event_id = ? ORDER BY type ASC, sort ASC, id ASC',
            [self::eventId()]
        );
    }

    public static function create(array $d): int
    {
        return Database::insert('sponsorship_packages', [
            'event_id'    => self::eventId(),
            'type'        => $d['type'] === 'exhibition' ? 'exhibition' : 'sponsor',
            'name'        => $d['name'],
            'price_kobo'  => (int) $d['price_kobo'],
            'booth_size'  => $d['booth_size'] ?: null,
            'perks_json'  => $d['perks_json'] ?? '[]',
            'comp_passes' => max(0, (int) ($d['comp_passes'] ?? 0)),
            'is_active'   => !empty($d['is_active']) ? 1 : 0,
            'sort'        => (int) ($d['sort'] ?? 0),
        ]);
    }

    public static function update(int $id, array $d): void
    {
        Database::run(
            'UPDATE sponsorship_packages SET type=?, name=?, price_kobo=?, booth_size=?, perks_json=?,
             comp_passes=?, is_active=?, sort=? WHERE id=? AND event_id=?',
            [
                $d['type'] === 'exhibition' ? 'exhibition' : 'sponsor', $d['name'], (int) $d['price_kobo'],
                $d['booth_size'] ?: null, $d['perks_json'] ?? '[]', max(0, (int) ($d['comp_passes'] ?? 0)),
                !empty($d['is_active']) ? 1 : 0, (int) ($d['sort'] ?? 0), $id, self::eventId(),
            ]
        );
    }

    public static function delete(int $id): void
    {
        Database::run('DELETE FROM sponsorship_packages WHERE id = ? AND event_id = ?', [$id, self::eventId()]);
    }

    public static function isReferenced(int $id): bool
    {
        return (int) Database::scalar('SELECT COUNT(*) FROM sponsor_applications WHERE package_id = ?', [$id]) > 0;
    }
}
