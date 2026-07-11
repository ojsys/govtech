<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Auth;
use App\Core\Database;

final class Audit extends Model
{
    protected static string $table = 'audit_log';

    /** Record an admin mutation. Call after every state change in admin. */
    public static function log(string $action, string $entity, ?int $entityId = null, array $meta = []): void
    {
        Database::insert('audit_log', [
            'user_id'   => Auth::id() ?? 0,
            'action'    => $action,
            'entity'    => $entity,
            'entity_id' => $entityId ?? 0,
            'meta'      => $meta ? json_encode($meta, JSON_UNESCAPED_SLASHES) : null,
            'ip'        => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);
    }

    public static function recent(int $limit = 50): array
    {
        $limit = max(1, min(500, $limit));
        return Database::all(
            "SELECT a.*, u.name AS user_name FROM audit_log a
             LEFT JOIN users u ON u.id = a.user_id
             ORDER BY a.id DESC LIMIT {$limit}"
        );
    }
}
