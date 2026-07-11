<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use RuntimeException;

final class Order extends Model
{
    protected static string $table = 'orders';

    /**
     * Create a pending order + items from a validated cart.
     * Totals are RECOMPUTED here from ticket_types.price_kobo — posted prices
     * are never trusted.
     *
     * @param array<int,int> $cart  ticket_type_id => quantity
     * @return array{order:array,items:array}
     */
    public static function createPending(int $attendeeId, array $cart): array
    {
        $items = [];
        $subtotal = 0;
        foreach ($cart as $ticketTypeId => $qty) {
            $qty = (int) $qty;
            if ($qty < 1) {
                continue;
            }
            $tt = TicketType::findActiveById((int) $ticketTypeId);
            if (!$tt) {
                throw new RuntimeException('A selected pass is no longer available.');
            }
            $unit = (int) $tt['price_kobo'];
            $lineSubtotal = $unit * $qty;
            $subtotal += $lineSubtotal;
            $items[] = [
                'ticket_type_id'  => (int) $tt['id'],
                'unit_price_kobo' => $unit,
                'quantity'        => $qty,
                'subtotal_kobo'   => $lineSubtotal,
                'group_size'      => max(1, (int) $tt['group_size']),
            ];
        }
        if ($items === []) {
            throw new RuntimeException('Your cart is empty.');
        }

        $reference = self::generateReference();
        $total = $subtotal; // no fees/discounts yet — fees can be added here later

        Database::beginTransaction();
        try {
            $orderId = Database::insert('orders', [
                'reference'     => $reference,
                'attendee_id'   => $attendeeId,
                'subtotal_kobo' => $subtotal,
                'total_kobo'    => $total,
                'currency'      => 'NGN',
                'status'        => 'pending',
            ]);
            foreach ($items as $it) {
                Database::insert('order_items', [
                    'order_id'        => $orderId,
                    'ticket_type_id'  => $it['ticket_type_id'],
                    'unit_price_kobo' => $it['unit_price_kobo'],
                    'quantity'        => $it['quantity'],
                    'subtotal_kobo'   => $it['subtotal_kobo'],
                ]);
            }
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            throw $e;
        }

        return ['order' => self::find($orderId), 'items' => $items];
    }

    public static function findByReference(string $reference): ?array
    {
        return Database::first('SELECT * FROM orders WHERE reference = ? LIMIT 1', [$reference]);
    }

    public static function items(int $orderId): array
    {
        return Database::all(
            'SELECT oi.*, tt.name AS ticket_name, tt.group_size
             FROM order_items oi JOIN ticket_types tt ON tt.id = oi.ticket_type_id
             WHERE oi.order_id = ? ORDER BY oi.id ASC',
            [$orderId]
        );
    }

    public static function setAccessCode(int $orderId, string $reference, string $accessCode): void
    {
        Database::run(
            'UPDATE orders SET paystack_ref = ?, paystack_access_code = ? WHERE id = ?',
            [$reference, $accessCode, $orderId]
        );
    }

    public static function markFailed(int $orderId): void
    {
        Database::run("UPDATE orders SET status = 'failed' WHERE id = ? AND status = 'pending'", [$orderId]);
    }

    /* ---- Admin queries ---- */

    /** Paginated order list with buyer details. */
    public static function paginate(int $page = 1, int $perPage = 25, string $status = ''): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;
        $where = '';
        $params = [];
        if ($status !== '' && in_array($status, ['pending', 'paid', 'failed', 'cancelled'], true)) {
            $where = 'WHERE o.status = ?';
            $params[] = $status;
        }
        $rows = Database::all(
            "SELECT o.*, a.first_name, a.last_name, a.email, a.organization
             FROM orders o LEFT JOIN attendees a ON a.id = o.attendee_id
             {$where} ORDER BY o.id DESC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );
        $total = (int) Database::scalar("SELECT COUNT(*) FROM orders o {$where}", $params);
        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'perPage' => $perPage, 'pages' => (int) ceil($total / $perPage)];
    }

    /** All paid orders with buyer + item summary, for CSV export. */
    public static function exportRows(): array
    {
        return Database::all(
            "SELECT o.reference, o.status, o.total_kobo, o.paid_at, o.created_at,
                    a.first_name, a.last_name, a.email, a.phone, a.organization, a.job_title, a.sector, a.state,
                    (SELECT COUNT(*) FROM tickets t JOIN order_items oi ON oi.id=t.order_item_id WHERE oi.order_id=o.id) AS passes
             FROM orders o LEFT JOIN attendees a ON a.id = o.attendee_id
             ORDER BY o.id DESC"
        );
    }

    /** Dashboard headline figures. */
    public static function stats(): array
    {
        return [
            'orders_total'   => (int) Database::scalar('SELECT COUNT(*) FROM orders'),
            'orders_paid'    => (int) Database::scalar("SELECT COUNT(*) FROM orders WHERE status='paid'"),
            'orders_pending' => (int) Database::scalar("SELECT COUNT(*) FROM orders WHERE status='pending'"),
            'revenue_kobo'   => (int) Database::scalar("SELECT COALESCE(SUM(total_kobo),0) FROM orders WHERE status='paid'"),
            'attendees'      => (int) Database::scalar('SELECT COUNT(*) FROM attendees'),
        ];
    }

    private static function generateReference(): string
    {
        // Short, unique, URL-safe. Retry on the unlikely collision.
        for ($i = 0; $i < 5; $i++) {
            $ref = 'GTC-' . strtoupper(bin2hex(random_bytes(6)));
            if (!self::findByReference($ref)) {
                return $ref;
            }
        }
        throw new RuntimeException('Could not generate a unique order reference.');
    }
}
