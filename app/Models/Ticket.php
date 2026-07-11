<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Ticket extends Model
{
    protected static string $table = 'tickets';

    public static function forOrder(int $orderId): array
    {
        return Database::all(
            'SELECT t.*, tt.name AS ticket_name
             FROM tickets t
             JOIN order_items oi ON oi.id = t.order_item_id
             JOIN ticket_types tt ON tt.id = t.ticket_type_id
             WHERE oi.order_id = ? ORDER BY t.id ASC',
            [$orderId]
        );
    }

    public static function findByCode(string $code): ?array
    {
        return Database::first('SELECT * FROM tickets WHERE ticket_code = ? LIMIT 1', [$code]);
    }

    public static function countForOrder(int $orderId): int
    {
        return (int) Database::scalar(
            'SELECT COUNT(*) FROM tickets t
             JOIN order_items oi ON oi.id = t.order_item_id
             WHERE oi.order_id = ?',
            [$orderId]
        );
    }

    /**
     * Full ticket detail for check-in/verify. LEFT JOINs so complimentary passes
     * (which have no order) are still returned; order_status is NULL for those.
     */
    public static function detailByCode(string $code): ?array
    {
        return Database::first(
            "SELECT t.*, tt.name AS ticket_name, o.reference AS order_reference, o.status AS order_status
             FROM tickets t
             LEFT JOIN order_items oi ON oi.id = t.order_item_id
             LEFT JOIN orders o ON o.id = oi.order_id
             LEFT JOIN ticket_types tt ON tt.id = t.ticket_type_id
             WHERE t.ticket_code = ? LIMIT 1",
            [$code]
        );
    }

    /**
     * Check a ticket in, idempotently. Returns one of:
     *  ['result'=>'ok', ...]            first successful check-in
     *  ['result'=>'already', ...]       was already checked in (with time)
     *  ['result'=>'void']               ticket voided
     *  ['result'=>'not_found']
     *  ['result'=>'unpaid']             order not paid
     */
    public static function checkIn(string $code, int $byUserId): array
    {
        $t = self::detailByCode($code);
        if (!$t) {
            return ['result' => 'not_found'];
        }
        // Paid order OR a complimentary (sponsor) pass is admissible.
        $admissible = $t['order_status'] === 'paid' || ($t['source'] ?? '') === 'comp';
        if (!$admissible) {
            return ['result' => 'unpaid', 'ticket' => $t];
        }
        if ($t['status'] === 'void') {
            return ['result' => 'void', 'ticket' => $t];
        }
        if ($t['status'] === 'checked_in') {
            return ['result' => 'already', 'ticket' => $t];
        }
        // Atomic transition so a double-scan can't double-count.
        $now = date('Y-m-d H:i:s');
        $affected = Database::run(
            "UPDATE tickets SET status='checked_in', checked_in_at=?, checked_in_by=? WHERE id=? AND status='valid'",
            [$now, $byUserId, (int) $t['id']]
        )->rowCount();
        $t = self::detailByCode($code);
        return ['result' => $affected === 1 ? 'ok' : 'already', 'ticket' => $t];
    }

    public static function stats(): array
    {
        return [
            'issued'     => (int) Database::scalar("SELECT COUNT(*) FROM tickets WHERE status<>'void'"),
            'checked_in' => (int) Database::scalar("SELECT COUNT(*) FROM tickets WHERE status='checked_in'"),
        ];
    }

    /**
     * Issue complimentary passes (sponsor tier perk). Not tied to a paid order,
     * so order_item_id is NULL and source = 'comp'. Returns the new ticket rows.
     */
    public static function issueComp(int $count, ?int $ticketTypeId, string $holderName, string $holderEmail): array
    {
        $ids = [];
        for ($i = 0; $i < $count; $i++) {
            $ids[] = Database::insert('tickets', [
                'order_item_id' => null,
                'ticket_type_id' => $ticketTypeId,
                'attendee_id'   => null,
                'ticket_code'   => self::generateCode(),
                'qr_path'       => null,
                'holder_name'   => $holderName,
                'holder_email'  => mb_strtolower(trim($holderEmail)),
                'source'        => 'comp',
                'status'        => 'valid',
            ]);
        }
        if ($ids === []) {
            return [];
        }
        $in = implode(',', array_fill(0, count($ids), '?'));
        return Database::all("SELECT * FROM tickets WHERE id IN ({$in}) ORDER BY id ASC", $ids);
    }

    /** Comp passes belonging to a sponsor (matched by holder email + comp source). */
    public static function compForEmail(string $email): array
    {
        return Database::all(
            "SELECT t.*, tt.name AS ticket_name FROM tickets t
             LEFT JOIN ticket_types tt ON tt.id = t.ticket_type_id
             WHERE t.source = 'comp' AND t.holder_email = ? ORDER BY t.id ASC",
            [mb_strtolower(trim($email))]
        );
    }

    /** Generate a unique ticket code, e.g. GT-3F9A2C7B10. */
    public static function generateCode(): string
    {
        for ($i = 0; $i < 6; $i++) {
            $code = 'GT-' . strtoupper(bin2hex(random_bytes(5)));
            if (!self::findByCode($code)) {
                return $code;
            }
        }
        throw new \RuntimeException('Could not generate a unique ticket code.');
    }
}
