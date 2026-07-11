<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Models\Audit;
use App\Models\Ticket;

final class CheckInController extends AdminController
{
    protected array $allowedRoles = ['checkin', 'editor', 'finance'];

    /** GET /admin/checkin — camera scanner + manual entry. */
    public function index(Request $request, array $args = []): void
    {
        $this->render('admin/checkin', [
            'pageTitle' => 'Check-in',
            'stats'     => Ticket::stats(),
            'prefill'   => $request->str('code'),
        ]);
    }

    /**
     * POST /admin/checkin/scan — JSON {code}. Validates + checks the ticket in
     * idempotently and returns a JSON verdict for the scanner UI.
     */
    public function scan(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        $code = $this->normalizeCode($request->str('code'));
        if ($code === '') {
            $this->json(['result' => 'empty', 'message' => 'No code provided.'], 422);
        }

        $res = Ticket::checkIn($code, Auth::id() ?? 0);
        if ($res['result'] === 'ok') {
            Audit::log('checkin', 'ticket', (int) ($res['ticket']['id'] ?? 0), ['code' => $code]);
        }

        $messages = [
            'ok'        => 'Checked in.',
            'already'   => 'Already checked in.',
            'void'      => 'This pass has been voided.',
            'unpaid'    => 'Order is not paid — do not admit.',
            'not_found' => 'Unknown code — not a valid pass.',
        ];
        $t = $res['ticket'] ?? null;
        $this->json([
            'result'  => $res['result'],
            'message' => $messages[$res['result']] ?? 'Unknown.',
            'ticket'  => $t ? [
                'code'        => $t['ticket_code'] ?? $code,
                'holder'      => $t['holder_name'] ?? '',
                'type'        => $t['ticket_name'] ?? '',
                'reference'   => $t['order_reference'] ?? '',
                'checked_in_at' => $t['checked_in_at'] ?? null,
            ] : null,
        ]);
    }

    /** The QR encodes /checkin/verify?code=... — route staff to the scanner. */
    public function verify(Request $request, array $args = []): void
    {
        Auth::requireLogin();
        redirect('/admin/checkin?code=' . rawurlencode($request->str('code')));
    }

    private function normalizeCode(string $raw): string
    {
        $raw = trim($raw);
        // If a full verify URL was scanned, pull out the code parameter.
        if (str_contains($raw, 'code=')) {
            parse_str((string) parse_url($raw, PHP_URL_QUERY), $q);
            $raw = $q['code'] ?? $raw;
        }
        return strtoupper(trim($raw));
    }
}
