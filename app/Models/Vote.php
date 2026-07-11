<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDOException;

final class Vote extends Model
{
    protected static string $table = 'votes';

    /**
     * Cast (or resend) a vote. One vote per (category, email), enforced by the
     * DB unique key. Returns a status:
     *   ['status'=>'sent', 'token'=>..., 'vote'=>...]      new pending vote, email it
     *   ['status'=>'resent', 'token'=>..., 'vote'=>...]    existing unverified vote, re-email
     *   ['status'=>'already_voted']                        already verified in this category
     */
    public static function cast(int $nominationId, int $categoryId, string $email, string $ip): array
    {
        $email = mb_strtolower(trim($email));

        // Existing vote in this category for this email?
        $existing = Database::first(
            'SELECT * FROM votes WHERE category_id = ? AND voter_email = ? LIMIT 1',
            [$categoryId, $email]
        );
        if ($existing) {
            if ((int) $existing['verified'] === 1) {
                return ['status' => 'already_voted'];
            }
            // Unverified: point it at the (possibly new) choice and re-send.
            $token = bin2hex(random_bytes(32));
            Database::run(
                'UPDATE votes SET nomination_id = ?, verify_token = ?, ip = ? WHERE id = ?',
                [$nominationId, $token, $ip, (int) $existing['id']]
            );
            return ['status' => 'resent', 'token' => $token, 'vote' => self::find((int) $existing['id'])];
        }

        $token = bin2hex(random_bytes(32));
        try {
            $id = Database::insert('votes', [
                'nomination_id' => $nominationId,
                'category_id'   => $categoryId,
                'voter_email'   => $email,
                'verify_token'  => $token,
                'verified'      => 0,
                'ip'            => $ip,
            ]);
        } catch (PDOException $e) {
            // Race on the unique key — treat as already voted.
            return ['status' => 'already_voted'];
        }
        return ['status' => 'sent', 'token' => $token, 'vote' => self::find($id)];
    }

    /**
     * Verify a vote by token. IDEMPOTENT: only the 0->1 transition increments the
     * nominee's tally. Returns ['result'=>'verified'|'already'|'invalid', 'vote'=>?].
     */
    public static function verify(string $token): array
    {
        if ($token === '') {
            return ['result' => 'invalid'];
        }
        $vote = Database::first('SELECT * FROM votes WHERE verify_token = ? LIMIT 1', [$token]);
        if (!$vote) {
            return ['result' => 'invalid'];
        }
        $affected = Database::run(
            'UPDATE votes SET verified = 1 WHERE id = ? AND verified = 0',
            [(int) $vote['id']]
        )->rowCount();

        if ($affected === 1) {
            Database::run('UPDATE nominations SET votes_count = votes_count + 1 WHERE id = ?', [(int) $vote['nomination_id']]);
            return ['result' => 'verified', 'vote' => $vote];
        }
        return ['result' => 'already', 'vote' => $vote];
    }

    public static function stats(): array
    {
        return [
            'cast'     => (int) Database::scalar('SELECT COUNT(*) FROM votes'),
            'verified' => (int) Database::scalar('SELECT COUNT(*) FROM votes WHERE verified = 1'),
        ];
    }
}
