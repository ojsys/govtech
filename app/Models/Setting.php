<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Setting extends Model
{
    protected static string $table = 'site_settings';

    private static ?array $cache = null;

    /** All settings as key => value. */
    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }
        $rows = Database::all('SELECT setting_key, setting_value FROM site_settings');
        $out = [];
        foreach ($rows as $row) {
            $out[$row['setting_key']] = $row['setting_value'];
        }
        return self::$cache = $out;
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        return self::all()[$key] ?? $default;
    }

    public static function set(string $key, string $value): void
    {
        $exists = (int) Database::scalar('SELECT COUNT(*) FROM site_settings WHERE setting_key = ?', [$key]);
        if ($exists) {
            Database::run('UPDATE site_settings SET setting_value = ? WHERE setting_key = ?', [$value, $key]);
        } else {
            Database::insert('site_settings', ['setting_key' => $key, 'setting_value' => $value]);
        }
        self::$cache = null;
    }
}
