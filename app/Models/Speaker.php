<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Speaker extends Model
{
    protected static string $table = 'speakers';

    public static function forEvent(): array
    {
        return Database::all(
            'SELECT * FROM speakers WHERE event_id = ? ORDER BY featured DESC, sort ASC, id ASC',
            [self::eventId()]
        );
    }

    /** Resolve a speaker photo to a URL: absolute, local upload, or legacy WP media. */
    public static function photoUrl(?string $photo): string
    {
        $photo = trim((string) $photo);
        if ($photo === '') {
            return '';
        }
        if (preg_match('#^https?://#', $photo)) {
            return $photo;
        }
        // Local uploaded file?
        if (is_file(BASE_PATH . '/public_html/uploads/' . $photo)) {
            return rtrim((string) \Config::get('app.uploads_url', '/uploads'), '/') . '/' . ltrim($photo, '/');
        }
        // Otherwise treat as a path under the legacy WP media base.
        return rtrim((string) \Config::get('app.legacy_media_base', ''), '/') . '/' . ltrim($photo, '/');
    }

    /* ---- Admin CRUD ---- */

    public static function allForAdmin(): array
    {
        return Database::all(
            'SELECT * FROM speakers WHERE event_id = ? ORDER BY sort ASC, id ASC',
            [self::eventId()]
        );
    }

    public static function create(array $d): int
    {
        return Database::insert('speakers', [
            'event_id'     => self::eventId(),
            'name'         => $d['name'] ?? '',
            'role'         => $d['role'] ?? '',
            'organization' => $d['organization'] ?? '',
            'photo'        => $d['photo'] ?? '',
            'bio'          => $d['bio'] ?? '',
            'featured'     => !empty($d['featured']) ? 1 : 0,
            'sort'         => (int) ($d['sort'] ?? 0),
        ]);
    }

    public static function update(int $id, array $d): void
    {
        Database::run(
            'UPDATE speakers SET name=?, role=?, organization=?, photo=?, bio=?, featured=?, sort=?
             WHERE id=? AND event_id=?',
            [
                $d['name'] ?? '', $d['role'] ?? '', $d['organization'] ?? '',
                $d['photo'] ?? '', $d['bio'] ?? '', !empty($d['featured']) ? 1 : 0,
                (int) ($d['sort'] ?? 0), $id, self::eventId(),
            ]
        );
    }

    public static function delete(int $id): void
    {
        Database::run('DELETE FROM speakers WHERE id=? AND event_id=?', [$id, self::eventId()]);
    }

    public static function initials(string $name): string
    {
        $clean = preg_replace('/^(Sen\.|Dr\.|Prof\.|Engr\.|Mr\.|Mrs\.|Ms\.)\s*/', '', $name) ?? $name;
        $parts = preg_split('/\s+/', trim($clean)) ?: [];
        $letters = array_map(static fn($w) => mb_substr($w, 0, 1), array_slice($parts, 0, 2));
        return mb_strtoupper(implode('', $letters));
    }
}
