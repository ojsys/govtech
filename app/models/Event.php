<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Event extends Model
{
    protected static string $table = 'events';

    /** The active edition (config event_id), falling back to the latest live event. */
    public static function current(): ?array
    {
        $id = self::eventId();
        $row = Database::first('SELECT * FROM events WHERE id = ? LIMIT 1', [$id]);
        if ($row) {
            return $row;
        }
        return Database::first("SELECT * FROM events WHERE status = 'live' ORDER BY start_date DESC LIMIT 1");
    }

    /** Update the active event's details from the admin. */
    public static function updateCurrent(array $d): void
    {
        Database::run(
            'UPDATE events SET name = ?, edition = ?, theme = ?, start_date = ?, end_date = ?, venue = ? WHERE id = ?',
            [
                $d['name'] ?? '', $d['edition'] ?? '', $d['theme'] ?? '',
                $d['start_date'] ?: null, $d['end_date'] ?: null, $d['venue'] ?? '',
                self::eventId(),
            ]
        );
    }
}
