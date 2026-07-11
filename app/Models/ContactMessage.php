<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class ContactMessage extends Model
{
    protected static string $table = 'contact_messages';

    public static function create(array $d): int
    {
        return Database::insert('contact_messages', [
            'name'    => $d['name'] ?? '',
            'email'   => $d['email'] ?? '',
            'subject' => $d['subject'] ?? '',
            'message' => $d['message'] ?? '',
        ]);
    }

    public static function recent(int $limit = 100): array
    {
        $limit = max(1, min(500, $limit));
        return Database::all("SELECT * FROM contact_messages ORDER BY id DESC LIMIT {$limit}");
    }
}
