<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Models\Order;
use App\Models\Report;
use App\Models\Ticket;
use App\Models\Vote;

final class ReportsController extends AdminController
{
    protected array $allowedRoles = ['finance', 'editor'];

    public function index(Request $request, array $args = []): void
    {
        $this->render('admin/reports', [
            'pageTitle'   => 'Reports',
            'orderStats'  => Order::stats(),
            'ticketStats' => Ticket::stats(),
            'voteStats'   => Vote::stats(),
            'revenue'     => Report::revenueByTicketType(),
            'bySector'    => Report::registrationsBySector(),
            'checkin'     => Report::checkinByType(),
            'votes'       => Report::votesByCategory(),
            'sponsors'    => Report::sponsorSummary(),
        ]);
    }

    /** GET /admin/reports/passes.csv — master delegate/pass list. */
    public function exportPasses(Request $request, array $args = []): void
    {
        $rows = Report::passRows();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="govtech-passes-' . date('Ymd-His') . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Ticket code', 'Pass type', 'Holder', 'Email', 'Organization', 'Phone', 'Source', 'Status', 'Checked in at', 'Order ref']);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['ticket_code'], $r['pass_type'], $r['holder_name'], $r['holder_email'],
                $r['organization'], $r['phone'], $r['source'], $r['status'],
                $r['checked_in_at'], $r['order_reference'],
            ]);
        }
        fclose($out);
        exit;
    }
}
