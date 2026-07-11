<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Gallery extends Model
{
    protected static string $table = 'gallery';

    /**
     * Gallery images across ALL editions, newest edition first. The gallery is
     * intentionally not event-scoped: photos are categorised by their `edition`
     * label so visitors can browse every past edition and filter between them.
     */
    public static function all(): array
    {
        return Database::all(
            "SELECT * FROM gallery ORDER BY (edition IS NULL OR edition = ''), edition DESC, sort ASC, id ASC"
        );
    }

    /** Distinct, non-empty edition labels present in the gallery (newest first). */
    public static function editions(): array
    {
        $rows = Database::all(
            "SELECT DISTINCT edition FROM gallery
             WHERE edition IS NOT NULL AND edition <> '' ORDER BY edition DESC"
        );
        return array_map(static fn($r) => (string) $r['edition'], $rows);
    }

    /* ---- Admin ---- */

    public static function allForAdmin(): array
    {
        return self::all();
    }

    public static function create(string $image, string $caption, int $sort, string $edition = ''): int
    {
        return Database::insert('gallery', [
            'event_id' => self::eventId(),
            'image'    => $image,
            'caption'  => $caption,
            'edition'  => $edition !== '' ? $edition : null,
            'sort'     => $sort,
        ]);
    }

    public static function updateMeta(int $id, string $caption, int $sort, string $edition = ''): void
    {
        Database::run(
            'UPDATE gallery SET caption = ?, edition = ?, sort = ? WHERE id = ?',
            [$caption, $edition !== '' ? $edition : null, $sort, $id]
        );
    }

    public static function delete(int $id): void
    {
        Database::run('DELETE FROM gallery WHERE id = ?', [$id]);
    }

    /** Next sort value (max + 1) so new images append to the end. */
    public static function nextSort(): int
    {
        return (int) Database::scalar('SELECT COALESCE(MAX(sort),0) + 1 FROM gallery');
    }
}
