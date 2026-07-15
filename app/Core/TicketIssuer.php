<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\Attendee;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\Event;
use App\Core\Mailer;
use App\Core\View;

/**
 * Issues passes for a confirmed order — IDEMPOTENTLY.
 *
 * The event is free, so registration confirms the order directly (no payment
 * gateway). issue() still guards with a single atomic UPDATE — only the call
 * that flips the order to 'paid' (i.e. confirmed) creates tickets; a repeated
 * submit or refresh is a no-op, so passes are never double-issued.
 */
final class TicketIssuer
{
    /**
     * @return array{issued:bool, tickets:array}  issued=false means it was already done.
     */
    public static function issue(int $orderId): array
    {
        $now = date('Y-m-d H:i:s');

        Database::beginTransaction();
        try {
            // Atomic win: only one caller transitions the order to paid.
            $affected = Database::run(
                "UPDATE orders SET status = 'paid', paid_at = ? WHERE id = ? AND status <> 'paid'",
                [$now, $orderId]
            )->rowCount();

            if ($affected === 0) {
                // Already paid/issued by a prior call — nothing to do.
                Database::commit();
                return ['issued' => false, 'tickets' => Ticket::forOrder($orderId)];
            }

            $order = Order::find($orderId);
            $attendee = $order ? Attendee::find((int) $order['attendee_id']) : null;
            $holderName = $attendee ? Attendee::fullName($attendee) : 'Delegate';
            $holderEmail = $attendee['email'] ?? '';

            $items = Order::items($orderId);
            foreach ($items as $item) {
                $seats = max(1, (int) $item['quantity']) * max(1, (int) $item['group_size']);
                for ($n = 0; $n < $seats; $n++) {
                    Database::insert('tickets', [
                        'order_item_id' => (int) $item['id'],
                        'ticket_type_id' => (int) $item['ticket_type_id'],
                        'attendee_id'   => (int) $order['attendee_id'],
                        'ticket_code'   => Ticket::generateCode(),
                        'qr_path'       => null,
                        'holder_name'   => $holderName,
                        'holder_email'  => $holderEmail,
                        'source'        => 'purchase',
                        'status'        => 'valid',
                    ]);
                }
                // Keep sold counts in step for quota tracking.
                Database::run(
                    'UPDATE ticket_types SET sold = sold + ? WHERE id = ?',
                    [$seats, (int) $item['ticket_type_id']]
                );
            }

            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            throw $e;
        }

        // Side effects AFTER commit: render QR files, then email the buyer.
        $tickets = Ticket::forOrder($orderId);
        self::renderQrFiles($tickets);

        return ['issued' => true, 'tickets' => $tickets];
    }

    /** Render + email the pass confirmation to the attendee. Safe to skip if no email. */
    public static function sendConfirmation(int $orderId): void
    {
        $order = Order::find($orderId);
        $attendee = $order ? Attendee::find((int) $order['attendee_id']) : null;
        if (!$order || !$attendee || empty($attendee['email'])) {
            return;
        }
        $tickets = Ticket::forOrder($orderId);

        $view = new View();
        $html = $view->renderPartial('emails/ticket-confirmation', [
            'order'       => $order,
            'attendee'    => $attendee,
            'tickets'     => $tickets,
            'items'       => Order::items($orderId),
            'ticketEvent' => Event::current(),
        ]);

        (new Mailer())->send(
            $attendee['email'],
            Attendee::fullName($attendee),
            'Your passes — Nigeria GovTech Conference & Awards',
            $html
        );
    }

    /** Generate + persist a QR file path for any ticket missing one. */
    public static function renderQrFiles(array $tickets): void
    {
        foreach ($tickets as $t) {
            if (!empty($t['qr_path'])) {
                continue;
            }
            $code = $t['ticket_code'];
            // Encode an absolute verify URL so a phone camera also resolves it.
            $payload = ticket_verify_url($code);
            $file = Qr::toFile($payload, $code);
            $rel = 'qr/' . basename($file);
            Database::run('UPDATE tickets SET qr_path = ? WHERE id = ?', [$rel, (int) $t['id']]);
        }
    }
}
