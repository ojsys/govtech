<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Models\Audit;
use App\Models\Order;
use App\Models\Ticket;

final class DashboardController extends AdminController
{
    public function index(Request $request, array $args = []): void
    {
        $this->render('admin/dashboard', [
            'pageTitle'   => 'Dashboard',
            'orderStats'  => Order::stats(),
            'ticketStats' => Ticket::stats(),
            'recentOrders'=> Order::paginate(1, 8)['rows'],
            'audit'       => Audit::recent(10),
        ]);
    }
}
