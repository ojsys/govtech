<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class AgendaSession extends Model
{
    protected static string $table = 'agenda_sessions';

    /**
     * Published sessions grouped by day for the public /agenda page.
     * @return array<string,array<int,array>>  day_label => sessions
     */
    public static function publishedByDay(): array
    {
        $rows = Database::all(
            'SELECT * FROM agenda_sessions
             WHERE event_id = ? AND is_published = 1
             ORDER BY sort ASC, start_time ASC, id ASC',
            [self::eventId()]
        );
        $days = [];
        foreach ($rows as $row) {
            $day = ($row['day_label'] ?? '') !== '' ? $row['day_label'] : 'Programme';
            $days[$day][] = $row;
        }
        return $days;
    }

    /* ---- Admin CRUD ---- */

    public static function allForAdmin(): array
    {
        return Database::all(
            'SELECT * FROM agenda_sessions WHERE event_id = ? ORDER BY sort ASC, start_time ASC, id ASC',
            [self::eventId()]
        );
    }

    /** Distinct day labels already used, for the admin datalist. */
    public static function dayLabels(): array
    {
        $rows = Database::all(
            "SELECT DISTINCT day_label FROM agenda_sessions
             WHERE event_id = ? AND day_label <> '' ORDER BY day_label ASC",
            [self::eventId()]
        );
        return array_values(array_filter(array_column($rows, 'day_label')));
    }

    public static function create(array $d): int
    {
        return Database::insert('agenda_sessions', self::columns($d) + ['event_id' => self::eventId()]);
    }

    public static function update(int $id, array $d): void
    {
        $c = self::columns($d);
        Database::run(
            'UPDATE agenda_sessions SET day_label=?, start_time=?, end_time=?, title=?, description=?,
             speaker=?, location=?, track=?, is_break=?, is_published=?, sort=?
             WHERE id=? AND event_id=?',
            [
                $c['day_label'], $c['start_time'], $c['end_time'], $c['title'], $c['description'],
                $c['speaker'], $c['location'], $c['track'], $c['is_break'], $c['is_published'], $c['sort'],
                $id, self::eventId(),
            ]
        );
    }

    public static function delete(int $id): void
    {
        Database::run('DELETE FROM agenda_sessions WHERE id=? AND event_id=?', [$id, self::eventId()]);
    }

    /** Normalise posted data to storable columns. */
    private static function columns(array $d): array
    {
        return [
            'day_label'    => trim((string) ($d['day_label'] ?? '')),
            'start_time'   => trim((string) ($d['start_time'] ?? '')),
            'end_time'     => trim((string) ($d['end_time'] ?? '')),
            'title'        => trim((string) ($d['title'] ?? '')),
            'description'  => trim((string) ($d['description'] ?? '')),
            'speaker'      => trim((string) ($d['speaker'] ?? '')),
            'location'     => trim((string) ($d['location'] ?? '')),
            'track'        => trim((string) ($d['track'] ?? '')),
            'is_break'     => !empty($d['is_break']) ? 1 : 0,
            'is_published' => !empty($d['is_published']) ? 1 : 0,
            'sort'         => (int) ($d['sort'] ?? 0),
        ];
    }
}
