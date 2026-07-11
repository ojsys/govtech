<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Nomination extends Model
{
    protected static string $table = 'nominations';

    public static function create(array $d): int
    {
        return Database::insert('nominations', [
            'category_id'     => (int) $d['category_id'],
            'nominee_name'    => $d['nominee_name'] ?? '',
            'nominee_org'     => $d['nominee_org'] ?? '',
            'nominee_email'   => $d['nominee_email'] ?? '',
            'nominator_name'  => $d['nominator_name'] ?? '',
            'nominator_email' => $d['nominator_email'] ?? '',
            'justification'   => $d['justification'] ?? '',
            'status'          => 'pending',
        ]);
    }

    /** Nominees open for voting (shortlisted) in a category, ranked by verified votes. */
    public static function votableInCategory(int $categoryId): array
    {
        return Database::all(
            "SELECT * FROM nominations
             WHERE category_id = ? AND status = 'shortlisted'
             ORDER BY votes_count DESC, nominee_name ASC",
            [$categoryId]
        );
    }

    /** Find a votable (shortlisted) nomination scoped to the current event. */
    public static function findVotable(int $id): ?array
    {
        return Database::first(
            "SELECT n.* FROM nominations n
             JOIN award_categories c ON c.id = n.category_id
             WHERE n.id = ? AND n.status = 'shortlisted' AND c.is_active = 1 AND c.event_id = ?
             LIMIT 1",
            [$id, self::eventId()]
        );
    }

    /* ---- Admin ---- */

    public static function forAdmin(int $categoryId = 0, string $status = ''): array
    {
        $where = ['c.event_id = ?'];
        $params = [self::eventId()];
        if ($categoryId > 0) {
            $where[] = 'n.category_id = ?';
            $params[] = $categoryId;
        }
        if (in_array($status, ['pending', 'approved', 'shortlisted', 'rejected'], true)) {
            $where[] = 'n.status = ?';
            $params[] = $status;
        }
        return Database::all(
            'SELECT n.*, c.title AS category_title FROM nominations n
             JOIN award_categories c ON c.id = n.category_id
             WHERE ' . implode(' AND ', $where) . '
             ORDER BY n.created_at DESC, n.id DESC',
            $params
        );
    }

    public static function setStatus(int $id, string $status): bool
    {
        if (!in_array($status, ['pending', 'approved', 'shortlisted', 'rejected'], true)) {
            return false;
        }
        Database::run('UPDATE nominations SET status = ? WHERE id = ?', [$status, $id]);
        return true;
    }

    public static function countsByStatus(): array
    {
        $rows = Database::all(
            'SELECT n.status, COUNT(*) AS c FROM nominations n
             JOIN award_categories cat ON cat.id = n.category_id
             WHERE cat.event_id = ? GROUP BY n.status',
            [self::eventId()]
        );
        $out = ['pending' => 0, 'approved' => 0, 'shortlisted' => 0, 'rejected' => 0];
        foreach ($rows as $r) {
            $out[$r['status']] = (int) $r['c'];
        }
        return $out;
    }
}
