<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Mailer;
use App\Core\Paystack;
use App\Core\Qr;
use App\Core\Request;
use App\Core\TicketIssuer;
use App\Core\View;
use App\Models\Attendee;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Ticket;

final class PaymentController extends Controller
{
    /**
     * GET /checkout/callback?reference=...
     * Buyer returns here after paying. We verify by reference, then (if good)
     * issue tickets idempotently and show the confirmation.
     */
    public function callback(Request $request, array $args = []): void
    {
        $reference = $request->str('reference') ?: $request->str('trxref');
        $order = $reference ? Order::findByReference($reference) : null;
        if (!$order) {
            flash('error', 'We could not find that order.');
            redirect('/register');
        }

        try {
            $paystack = new Paystack();
            $data = $paystack->verify($order['reference']);
            Payment::record((int) $order['id'], $order['reference'], (int) ($data['amount'] ?? 0), (string) ($data['status'] ?? 'unknown'), $data);

            $paid = ($data['status'] ?? '') === 'success'
                && (int) ($data['amount'] ?? 0) === (int) $order['total_kobo'];

            if ($paid) {
                $result = TicketIssuer::issue((int) $order['id']);
                $this->sendConfirmation((int) $order['id']);
                redirect('/order/' . $order['reference']);
            }
        } catch (\Throwable $e) {
            error_log('Callback verify failed: ' . $e->getMessage());
        }

        // Not confirmed here — the webhook may still confirm it shortly.
        $this->render('pages/checkout-pending', [
            'pageTitle' => 'Payment processing',
            'order'     => Order::findByReference($order['reference']),
        ]);
    }

    /**
     * POST /payment/webhook — Paystack's source of truth.
     * Verify HMAC-SHA512 of the RAW body, then idempotently issue tickets.
     * Always returns 200 quickly so Paystack doesn't retry needlessly.
     */
    public function webhook(Request $request, array $args = []): void
    {
        $raw = $request->rawBody();
        $signature = $request->header('X-Paystack-Signature');

        try {
            $paystack = new Paystack();
            if (!$paystack->verifyWebhookSignature($raw, $signature)) {
                http_response_code(401);
                echo 'invalid signature';
                return;
            }
        } catch (\Throwable $e) {
            // Misconfigured keys etc. — log and 200 so we can inspect, not retry-storm.
            error_log('Webhook setup error: ' . $e->getMessage());
            http_response_code(200);
            echo 'ok';
            return;
        }

        $event = json_decode($raw, true);
        $type = $event['event'] ?? '';
        $data = $event['data'] ?? [];
        $reference = (string) ($data['reference'] ?? '');

        if ($type === 'charge.success' && $reference !== '') {
            $order = Order::findByReference($reference);
            if ($order
                && ($data['status'] ?? '') === 'success'
                && (int) ($data['amount'] ?? 0) === (int) $order['total_kobo']) {
                Payment::record((int) $order['id'], $reference, (int) $data['amount'], 'webhook.success', $data);
                $result = TicketIssuer::issue((int) $order['id']);
                if ($result['issued']) {
                    $this->sendConfirmation((int) $order['id']);
                }
            }
        }

        http_response_code(200);
        echo 'ok';
    }

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

    /** Render + email the ticket confirmation to the buyer. */
    private function sendConfirmation(int $orderId): void
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
            'ticketEvent' => \App\Models\Event::current(),
        ]);

        (new Mailer())->send(
            $attendee['email'],
            Attendee::fullName($attendee),
            'Your passes — Nigeria GovTech Conference & Awards',
            $html
        );
    }
}
