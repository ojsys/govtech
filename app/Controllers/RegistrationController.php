<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\RateLimit;
use App\Core\Request;
use App\Core\TicketIssuer;
use App\Core\Validator;
use App\Models\Attendee;
use App\Models\Order;
use App\Models\TicketType;
use RuntimeException;

final class RegistrationController extends Controller
{
    /** GET /register — attendee form for the single free general-admission pass. */
    public function show(Request $request, array $args = []): void
    {
        $qty = max(1, min(20, (int) $request->str('qty', '1')));

        $this->render('pages/register', [
            'pageTitle' => 'Register',
            'pass'      => TicketType::primary(),
            'qty'       => $qty,
            'errors'    => [],
        ]);
    }

    /** POST /register — validate, create order, and issue free passes immediately. */
    public function store(Request $request, array $args = []): void
    {
        Csrf::verify($request);

        if (!RateLimit::allow('register:' . $request->ip(), 8, 60) || !RateLimit::honeypotPassed($request)) {
            flash('error', 'Too many attempts. Please wait a minute and try again.');
            redirect('/register');
        }

        $pass = TicketType::primary();
        if (!$pass) {
            flash('error', 'Registration is not open yet. Please check back soon.');
            redirect('/register');
        }
        $qty = max(1, min(20, $request->int('quantity') ?: 1));

        // Validate attendee details.
        $data = [
            'first_name'   => $request->str('first_name'),
            'last_name'    => $request->str('last_name'),
            'email'        => $request->str('email'),
            'phone'        => $request->str('phone'),
            'organization' => $request->str('organization'),
            'job_title'    => $request->str('job_title'),
            'sector'       => $request->str('sector', 'other'),
            'state'        => $request->str('state'),
        ];
        $v = new Validator($data, [
            'first_name' => 'required|max:80',
            'last_name'  => 'required|max:80',
            'email'      => 'required|email|max:160',
            'phone'      => 'required|max:40',
            'sector'     => 'in:public,private,academia,other',
        ]);

        if ($v->fails()) {
            $_SESSION['_old'] = $data;
            $this->render('pages/register', [
                'pageTitle' => 'Register',
                'pass'      => $pass,
                'qty'       => $qty,
                'errors'    => $v->errors(),
            ]);
            return;
        }

        // Create attendee + order, then issue passes right away — the event is free.
        $cart = [(int) $pass['id'] => $qty];
        try {
            $attendeeId = Attendee::create($data);
            $result = Order::createPending($attendeeId, $cart);
            $order = $result['order'];

            // Confirm the order and issue tickets idempotently (guards double-submit).
            TicketIssuer::issue((int) $order['id']);
            TicketIssuer::sendConfirmation((int) $order['id']);
        } catch (RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect('/register');
        } catch (\Throwable $e) {
            error_log('Free registration failed: ' . $e->getMessage());
            flash('error', 'We could not complete your registration. ' . (\Config::get('app.env') === 'development' ? $e->getMessage() : 'Please try again shortly.'));
            redirect('/register');
        }

        redirect('/order/' . $order['reference']);
    }
}
