<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Paystack;
use App\Core\RateLimit;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Attendee;
use App\Models\Order;
use App\Models\Payment;
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

    /** POST /register — validate, create order, hand off to Paystack. */
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

        // Create attendee + pending order (totals recomputed server-side).
        try {
            $attendeeId = Attendee::create($data);
            $result = Order::createPending($attendeeId, $cart);
        } catch (RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect('/register');
        }
        $order = $result['order'];

        // Initialize Paystack and redirect to the hosted checkout.
        try {
            $paystack = new Paystack();
            $init = $paystack->initialize(
                $data['email'],
                (int) $order['total_kobo'],
                $order['reference'],
                url((string) \Config::get('paystack.callback_path', '/checkout/callback')),
                ['order_id' => (int) $order['id'], 'order_ref' => $order['reference']]
            );
            Order::setAccessCode((int) $order['id'], $init['reference'] ?? $order['reference'], $init['access_code'] ?? '');
            Payment::record((int) $order['id'], $order['reference'], (int) $order['total_kobo'], 'initialized', $init);
            redirect($init['authorization_url']);
        } catch (\Throwable $e) {
            error_log('Paystack init failed: ' . $e->getMessage());
            flash('error', 'We could not start the payment. ' . (\Config::get('app.env') === 'development' ? $e->getMessage() : 'Please try again shortly.'));
            redirect('/register');
        }
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
