<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Gallery extends Model
{
    protected static string $table = 'gallery';

    public static function forEvent(): array
    {
        return Database::all(
            'SELECT * FROM gallery WHERE event_id = ? ORDER BY sort ASC, id ASC',
            [self::eventId()]
        );
    }

    /* ---- Admin ---- */

    public static function allForAdmin(): array
    {
        return self::forEvent();
    }

    public static function create(string $image, string $caption, int $sort): int
    {
        return Database::insert('gallery', [
            'event_id' => self::eventId(),
            'image'    => $image,
            'caption'  => $caption,
            'sort'     => $sort,
        ]);
    }

    public static function updateMeta(int $id, string $caption, int $sort): void
    {
        Database::run(
            'UPDATE gallery SET caption = ?, sort = ? WHERE id = ? AND event_id = ?',
            [$caption, $sort, $id, self::eventId()]
        );
    }

    public static function delete(int $id): void
    {
        Database::run('DELETE FROM gallery WHERE id = ? AND event_id = ?', [$id, self::eventId()]);
    }

    /** Next sort value (max + 1) so new images append to the end. */
    public static function nextSort(): int
    {
        return (int) Database::scalar('SELECT COALESCE(MAX(sort),0) + 1 FROM gallery WHERE event_id = ?', [self::eventId()]);
    }
}
