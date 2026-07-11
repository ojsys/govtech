<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Testimonial extends Model
{
    protected static string $table = 'testimonials';

    public static function forEvent(): array
    {
        return Database::all(
            'SELECT * FROM testimonials WHERE event_id = ? ORDER BY sort ASC, id ASC',
            [self::eventId()]
        );
    }

    public static function allForAdmin(): array
    {
        return self::forEvent();
    }

    public static function create(array $d): int
    {
        return Database::insert('testimonials', [
            'event_id' => self::eventId(),
            'name'     => $d['name'] ?? '',
            'role'     => $d['role'] ?? '',
            'quote'    => $d['quote'] ?? '',
            'sort'     => (int) ($d['sort'] ?? 0),
        ]);
    }

    public static function update(int $id, array $d): void
    {
        Database::run(
            'UPDATE testimonials SET name = ?, role = ?, quote = ?, sort = ? WHERE id = ? AND event_id = ?',
            [$d['name'] ?? '', $d['role'] ?? '', $d['quote'] ?? '', (int) ($d['sort'] ?? 0), $id, self::eventId()]
        );
    }

    public static function delete(int $id): void
    {
        Database::run('DELETE FROM testimonials WHERE id = ? AND event_id = ?', [$id, self::eventId()]);
    }
}
