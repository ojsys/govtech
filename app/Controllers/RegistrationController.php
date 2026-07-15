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
    /** GET /register — show the pass selector + attendee form. */
    public function show(Request $request, array $args = []): void
    {
        $tickets = TicketType::active();
        $cart = $this->parseCart($request->str('cart'), $tickets);

        $this->render('pages/register', [
            'pageTitle' => 'Register',
            'tickets'   => $tickets,
            'cart'      => $cart,            // ticket_type_id => qty (prefill)
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

        $tickets = TicketType::active();
        $cart = $this->collectCartFromPost($request, $tickets);

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
        $v = new Validator($data + ['cart' => $cart === [] ? '' : '1'], [
            'first_name' => 'required|max:80',
            'last_name'  => 'required|max:80',
            'email'      => 'required|email|max:160',
            'phone'      => 'required|max:40',
            'sector'     => 'in:public,private,academia,other',
            'cart'       => 'required',
        ], ['cart' => 'Pass selection']);

        if ($v->fails()) {
            $_SESSION['_old'] = $data;
            $this->render('pages/register', [
                'pageTitle' => 'Register',
                'tickets'   => $tickets,
                'cart'      => $cart,
                'errors'    => $v->errors(),
            ]);
            return;
        }

        // Create attendee + order, then issue passes right away — the event is free.
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

    /** Parse "1:2,3:1" into [ticket_type_id => qty], keeping only active passes. */
    private function parseCart(string $raw, array $tickets): array
    {
        $valid = array_column($tickets, null, 'id');
        $cart = [];
        foreach (array_filter(explode(',', $raw)) as $pair) {
            [$id, $qty] = array_pad(explode(':', $pair, 2), 2, '0');
            $id = (int) $id;
            $qty = max(0, min(50, (int) $qty));
            if ($qty > 0 && isset($valid[$id])) {
                $cart[$id] = $qty;
            }
        }
        return $cart;
    }

    /** Build the cart from posted qty[ticket_type_id] inputs. */
    private function collectCartFromPost(Request $request, array $tickets): array
    {
        $valid = array_column($tickets, null, 'id');
        $posted = $_POST['qty'] ?? [];
        $cart = [];
        if (is_array($posted)) {
            foreach ($posted as $id => $qty) {
                $id = (int) $id;
                $qty = max(0, min(50, (int) $qty));
                if ($qty > 0 && isset($valid[$id])) {
                    $cart[$id] = $qty;
                }
            }
        }
        return $cart;
    }
}
