<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Models\Attendee;
use App\Models\Order;
use App\Models\Ticket;

final class OrdersController extends AdminController
{
    protected array $allowedRoles = ['finance', 'editor'];

    public function index(Request $request, array $args = []): void
    {
        $page = max(1, $request->int('page', 1));
        $status = $request->str('status');
        $data = Order::paginate($page, 25, $status);

        $this->render('admin/orders', [
            'pageTitle' => 'Orders',
            'orders'    => $data['rows'],
            'page'      => $data['page'],
            'pages'     => $data['pages'],
            'total'     => $data['total'],
            'status'    => $status,
        ]);
    }

    public function show(Request $request, array $args = []): void
    {
        $order = Order::findByReference((string) ($args['reference'] ?? ''));
        if (!$order) {
            http_response_code(404);
            $this->render('admin/not-found', ['pageTitle' => 'Not found']);
            return;
        }
        $this->render('admin/order-detail', [
            'pageTitle' => 'Order ' . $order['reference'],
            'order'     => $order,
            'attendee'  => Attendee::find((int) $order['attendee_id']),
            'items'     => Order::items((int) $order['id']),
            'tickets'   => Ticket::forOrder((int) $order['id']),
        ]);
    }

    /** GET /admin/orders/{reference}/tickets — HTML fragment of the order's passes (for the modal). */
    public function tickets(Request $request, array $args = []): void
    {
        $order = Order::findByReference((string) ($args['reference'] ?? ''));
        if (!$order) {
            http_response_code(404);
            echo '<p style="padding:20px">Order not found.</p>';
            return;
        }
        echo $this->view->renderPartial('admin/order-tickets', [
            'tickets'     => Ticket::forOrder((int) $order['id']),
            'ticketEvent' => \App\Models\Event::current(),
        ]);
    }

    /** GET /admin/orders/export.csv — stream all orders as CSV. */
    public function export(Request $request, array $args = []): void
    {
        $rows = Order::exportRows();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="govtech-orders-' . date('Ymd-His') . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Reference', 'Status', 'Amount (NGN)', 'Passes', 'First name', 'Last name', 'Email', 'Phone', 'Organization', 'Job title', 'Sector', 'State', 'Paid at', 'Created at']);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['reference'], $r['status'], number_format((int) $r['total_kobo'] / 100, 2, '.', ''),
                $r['passes'], $r['first_name'], $r['last_name'], $r['email'], $r['phone'],
                $r['organization'], $r['job_title'], $r['sector'], $r['state'], $r['paid_at'], $r['created_at'],
            ]);
        }
        fclose($out);
        exit;
    }
}
