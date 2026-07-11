<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class AwardCategory extends Model
{
    protected static string $table = 'award_categories';

    public static function active(): array
    {
        return Database::all(
            'SELECT * FROM award_categories WHERE event_id = ? AND is_active = 1 ORDER BY sort ASC, id ASC',
            [self::eventId()]
        );
    }

    public static function activeById(int $id): ?array
    {
        return Database::first(
            'SELECT * FROM award_categories WHERE id = ? AND event_id = ? AND is_active = 1 LIMIT 1',
            [$id, self::eventId()]
        );
    }

    /* ---- Admin ---- */

    public static function allForAdmin(): array
    {
        return Database::all(
            'SELECT c.*,
                    (SELECT COUNT(*) FROM nominations n WHERE n.category_id = c.id) AS nominations,
                    (SELECT COUNT(*) FROM nominations n WHERE n.category_id = c.id AND n.status = \'shortlisted\') AS shortlisted
             FROM award_categories c WHERE c.event_id = ? ORDER BY c.sort ASC, c.id ASC',
            [self::eventId()]
        );
    }

    public static function create(array $d): int
    {
        return Database::insert('award_categories', [
            'event_id'    => self::eventId(),
            'title'       => $d['title'] ?? '',
            'description' => $d['description'] ?? '',
            'is_active'   => !empty($d['is_active']) ? 1 : 0,
            'sort'        => (int) ($d['sort'] ?? 0),
        ]);
    }

    public static function toggle(int $id): void
    {
        Database::run(
            'UPDATE award_categories SET is_active = 1 - is_active WHERE id = ? AND event_id = ?',
            [$id, self::eventId()]
        );
    }

    public static function findInEvent(int $id): ?array
    {
        return Database::first('SELECT * FROM award_categories WHERE id = ? AND event_id = ? LIMIT 1', [$id, self::eventId()]);
    }

    public static function update(int $id, array $d): void
    {
        Database::run(
            'UPDATE award_categories SET title = ?, description = ?, sort = ? WHERE id = ? AND event_id = ?',
            [$d['title'] ?? '', $d['description'] ?? '', (int) ($d['sort'] ?? 0), $id, self::eventId()]
        );
    }

    public static function delete(int $id): void
    {
        Database::run('DELETE FROM award_categories WHERE id = ? AND event_id = ?', [$id, self::eventId()]);
    }

    public static function isReferenced(int $id): bool
    {
        return (int) Database::scalar('SELECT COUNT(*) FROM nominations WHERE category_id = ?', [$id]) > 0;
    }
}
