<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class TicketType extends Model
{
    protected static string $table = 'ticket_types';

    public static function active(): array
    {
        return Database::all(
            'SELECT * FROM ticket_types WHERE event_id = ? AND is_active = 1 ORDER BY sort ASC, id ASC',
            [self::eventId()]
        );
    }

    public static function findActiveById(int $id): ?array
    {
        return Database::first(
            'SELECT * FROM ticket_types WHERE id = ? AND event_id = ? AND is_active = 1 LIMIT 1',
            [$id, self::eventId()]
        );
    }

    /* ---- Admin CRUD ---- */

    public static function allForAdmin(): array
    {
        return Database::all(
            'SELECT * FROM ticket_types WHERE event_id = ? ORDER BY sort ASC, id ASC',
            [self::eventId()]
        );
    }

    public static function create(array $d): int
    {
        return Database::insert('ticket_types', [
            'event_id'    => self::eventId(),
            'name'        => $d['name'],
            'slug'        => $d['slug'],
            'price_kobo'  => (int) $d['price_kobo'],
            'description' => $d['description'] ?? '',
            'perks_json'  => $d['perks_json'] ?? '[]',
            'group_size'  => max(1, (int) ($d['group_size'] ?? 1)),
            'quota'       => $d['quota'] !== '' && $d['quota'] !== null ? (int) $d['quota'] : null,
            'featured'    => !empty($d['featured']) ? 1 : 0,
            'is_active'   => !empty($d['is_active']) ? 1 : 0,
            'sort'        => (int) ($d['sort'] ?? 0),
        ]);
    }

    public static function update(int $id, array $d): void
    {
        Database::run(
            'UPDATE ticket_types SET name=?, slug=?, price_kobo=?, description=?, perks_json=?,
             group_size=?, quota=?, featured=?, is_active=?, sort=? WHERE id=? AND event_id=?',
            [
                $d['name'], $d['slug'], (int) $d['price_kobo'], $d['description'] ?? '',
                $d['perks_json'] ?? '[]', max(1, (int) ($d['group_size'] ?? 1)),
                ($d['quota'] !== '' && $d['quota'] !== null ? (int) $d['quota'] : null),
                !empty($d['featured']) ? 1 : 0, !empty($d['is_active']) ? 1 : 0,
                (int) ($d['sort'] ?? 0), $id, self::eventId(),
            ]
        );
    }

    public static function delete(int $id): void
    {
        Database::run('DELETE FROM ticket_types WHERE id = ? AND event_id = ?', [$id, self::eventId()]);
    }

    /** True if any order references this pass (so we must not hard-delete it). */
    public static function isReferenced(int $id): bool
    {
        return (int) Database::scalar('SELECT COUNT(*) FROM order_items WHERE ticket_type_id = ?', [$id]) > 0;
    }

    /** A unique slug within the event; appends -2, -3… on collision. */
    public static function uniqueSlug(string $base, int $ignoreId = 0): string
    {
        $base = $base !== '' ? $base : 'pass';
        $slug = $base;
        $n = 1;
        while (true) {
            $row = Database::first('SELECT id FROM ticket_types WHERE slug = ? LIMIT 1', [$slug]);
            if (!$row || (int) $row['id'] === $ignoreId) {
                return $slug;
            }
            $slug = $base . '-' . (++$n);
        }
    }

    /** The pass type used for complimentary sponsor delegate passes. */
    public static function compType(): ?array
    {
        // Prefer the "standard" delegate; otherwise the cheapest active in-person pass.
        return Database::first(
            "SELECT * FROM ticket_types
             WHERE event_id = ? AND is_active = 1 AND slug <> 'virtual'
             ORDER BY (slug = 'standard') DESC, price_kobo ASC, sort ASC
             LIMIT 1",
            [self::eventId()]
        );
    }
}
