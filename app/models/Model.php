<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Minimal active-record-ish base. Subclasses set $table.
 * All queries go through PDO prepared statements in Database.
 */
abstract class Model
{
    protected static string $table = '';

    public static function find(int $id): ?array
    {
        return Database::first('SELECT * FROM ' . static::$table . ' WHERE id = ? LIMIT 1', [$id]);
    }

    /** Current event id from config. */
    protected static function eventId(): int
    {
        return (int) \Config::get('app.event_id', 1);
    }
}
