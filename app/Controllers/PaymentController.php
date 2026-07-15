<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Qr;
use App\Core\Request;
use App\Models\Attendee;
use App\Models\Order;
use App\Models\Ticket;

/**
 * Serves tickets after registration: order confirmation, printable passes,
 * QR images, and the public authenticity check. The event is free, so there
 * is no payment step — passes are issued at registration time.
 */
final class PaymentController extends Controller
{
    /** GET /order/{reference} — confirmation + tickets + QR. */
    public function confirmation(Request $request, array $args = []): void
    {
        $order = Order::findByReference((string) ($args['reference'] ?? ''));
        if (!$order) {
            http_response_code(404);
            $this->render('pages/errors/404', []);
            return;
        }
        $attendee = Attendee::find((int) $order['attendee_id']);
        $tickets = Ticket::forOrder((int) $order['id']);

        $this->render('pages/order-confirmation', [
            'pageTitle'   => 'Your tickets',
            'order'       => $order,
            'attendee'    => $attendee,
            'tickets'     => $tickets,
            'items'       => Order::items((int) $order['id']),
            'ticketEvent' => \App\Models\Event::current(),
        ]);
    }

    /** GET /ticket/{code} — a single printable event ticket (the attendee's pass). */
    public function ticket(Request $request, array $args = []): void
    {
        $ticket = Ticket::detailByCode((string) ($args['code'] ?? ''));
        if (!$ticket) {
            http_response_code(404);
            $this->render('pages/errors/404', []);
            return;
        }
        $this->render('pages/ticket', [
            'pageTitle'   => 'Your ticket',
            'ticket'      => $ticket,
            'ticketEvent' => \App\Models\Event::current(),
        ]);
    }

    /** GET /ticket/{code}/qr — serve a ticket's QR as SVG. */
    public function qr(Request $request, array $args = []): void
    {
        $code = (string) ($args['code'] ?? '');
        if (!Ticket::findByCode($code)) {
            http_response_code(404);
            echo 'Not found';
            return;
        }
        header('Content-Type: image/svg+xml');
        header('Cache-Control: public, max-age=86400');
        echo Qr::svg(ticket_verify_url($code));
    }

    /** GET /ticket/{code}/qr.png — PNG QR (used on-page + in email). Cached on disk. */
    public function qrPng(Request $request, array $args = []): void
    {
        $code = (string) ($args['code'] ?? '');
        if (!Ticket::findByCode($code)) {
            http_response_code(404);
            echo 'Not found';
            return;
        }
        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=604800');

        $dir = STORAGE_PATH . '/qr';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $file = $dir . '/' . preg_replace('/[^A-Za-z0-9_\-]/', '', $code) . '.png';

        if (is_file($file)) {
            readfile($file);
            return;
        }
        // Generate once; cache only a real QR (never the placeholder).
        $png = Qr::pngReal(ticket_verify_url($code));
        if ($png !== null) {
            @file_put_contents($file, $png);
            echo $png;
        } else {
            echo Qr::placeholderPng();
        }
    }

    /**
     * GET /verify?code=... — PUBLIC ticket authenticity check. This is what the
     * ticket QR encodes; scanning it affirms (or denies) the pass. Read-only —
     * it never checks anyone in. Admins additionally get a check-in shortcut.
     */
    public function verifyTicket(Request $request, array $args = []): void
    {
        $code = strtoupper(trim($request->str('code')));
        $t = $code !== '' ? Ticket::detailByCode($code) : null;

        // Authentic if the pass exists and is backed by a paid order or is a comp pass.
        $authentic = false;
        if ($t) {
            $authentic = ($t['order_status'] ?? null) === 'paid' || ($t['source'] ?? '') === 'comp';
        }

        $this->render('pages/verify', [
            'pageTitle'   => 'Ticket verification',
            'code'        => $code,
            'ticket'      => $t,
            'authentic'   => $authentic,
            'isAdmin'     => \App\Core\Auth::check(),
            'ticketEvent' => \App\Models\Event::current(),
        ]);
    }
}
