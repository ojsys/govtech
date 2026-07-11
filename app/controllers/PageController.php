<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Mailer;
use App\Core\RateLimit;
use App\Core\Request;
use App\Core\Validator;
use App\Models\ContactMessage;
use App\Models\Event;
use App\Models\Gallery;
use App\Models\NewsletterSubscriber;
use App\Models\Setting;
use App\Models\SponsorshipPackage;

final class PageController extends Controller
{
    /** GET /about — story, objectives, and an image section. */
    public function about(Request $request, array $args = []): void
    {
        $this->render('pages/about', [
            'pageTitle' => 'About',
            'event'     => Event::current(),
            'settings'  => Setting::all(),
            'gallery'   => Gallery::forEvent(),
        ]);
    }

    /** GET /sponsor — sponsorship & partnership packages. */
    public function sponsorship(Request $request, array $args = []): void
    {
        $packages = SponsorshipPackage::grouped();
        $this->render('pages/sponsorship', [
            'pageTitle'    => 'Sponsorship & Partnership',
            'settings'     => Setting::all(),
            'sponsorTiers' => $packages['sponsor'],
            'booths'       => $packages['exhibition'],
        ]);
    }

    /** GET /contact — contact details + message form. */
    public function contact(Request $request, array $args = []): void
    {
        $this->render('pages/contact', [
            'pageTitle' => 'Contact',
            'settings'  => Setting::all(),
            'event'     => Event::current(),
            'errors'    => [],
        ]);
    }

    /** POST /contact — store + notify. */
    public function contactSubmit(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        if (!RateLimit::allow('contact:' . $request->ip(), 5, 120) || !RateLimit::honeypotPassed($request)) {
            flash('error', 'Too many messages. Please wait a moment and try again.');
            redirect('/contact');
        }

        $data = [
            'name'    => $request->str('name'),
            'email'   => $request->str('email'),
            'subject' => $request->str('subject'),
            'message' => $request->str('message'),
        ];
        $v = new Validator($data, [
            'name'    => 'required|max:160',
            'email'   => 'required|email|max:160',
            'subject' => 'max:200',
            'message' => 'required|min:10|max:4000',
        ]);
        if ($v->fails()) {
            $_SESSION['_old'] = $data;
            $this->render('pages/contact', [
                'pageTitle' => 'Contact',
                'settings'  => Setting::all(),
                'event'     => Event::current(),
                'errors'    => $v->errors(),
            ]);
            return;
        }

        ContactMessage::create($data);
        $this->notifyOrganizer($data);

        flash('ok', 'Thank you — your message has been sent. We\'ll get back to you shortly.');
        redirect('/contact');
    }

    /** POST /newsletter/subscribe — footer form on every page. */
    public function subscribe(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        $email = $request->str('email');
        $name = $request->str('name');

        if (RateLimit::allow('subscribe:' . $request->ip(), 6, 120)
            && RateLimit::honeypotPassed($request)
            && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            NewsletterSubscriber::subscribe($name, $email);
            flash('newsletter', 'You\'re subscribed — watch your inbox for programme updates.');
        } else {
            flash('newsletter', 'Please enter a valid email address.');
        }
        // Return to wherever they were (footer is global); default home.
        $back = $request->header('Referer');
        redirect($back && str_contains((string) $back, (string) \Config::get('app.base_url')) ? $back : '/');
    }

    private function notifyOrganizer(array $data): void
    {
        $to = Setting::get('contact_email', 'info@govtechconference.ng');
        if (!$to) {
            return;
        }
        $html = '<p><strong>New contact message</strong></p>'
            . '<p><strong>From:</strong> ' . e($data['name']) . ' &lt;' . e($data['email']) . '&gt;</p>'
            . '<p><strong>Subject:</strong> ' . e($data['subject'] ?: '(none)') . '</p>'
            . '<p><strong>Message:</strong><br>' . nl2br(e($data['message'])) . '</p>';
        (new Mailer())->send($to, 'GovTech Conference', 'Contact form: ' . ($data['subject'] ?: 'New message'), $html);
    }
}
