<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\RateLimit;
use App\Core\Request;
use App\Core\SponsorAuth;
use App\Core\Upload;
use App\Core\View;
use App\Models\SponsorAccount;
use App\Models\SponsorAsset;
use App\Models\Ticket;

/**
 * Sponsor/exhibitor self-service portal. Uses SponsorAuth (separate from admin).
 */
final class PortalController
{
    public function showLogin(Request $request, array $args = []): void
    {
        if (SponsorAuth::check()) {
            redirect('/portal');
        }
        echo (new View())->render('portal/login', [
            'csrf'  => Csrf::token(),
            'error' => flash('error'),
        ]);
    }

    public function login(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        if (!RateLimit::allow('portal-login:' . $request->ip(), 6, 300)) {
            flash('error', 'Too many attempts. Please wait a few minutes.');
            redirect('/portal/login');
        }
        if (SponsorAuth::attempt($request->str('email'), (string) $request->input('password', ''))) {
            $intended = $_SESSION['_portal_intended'] ?? '/portal';
            unset($_SESSION['_portal_intended']);
            redirect($intended);
        }
        flash('error', 'Invalid email or password.');
        redirect('/portal/login');
    }

    public function logout(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        SponsorAuth::logout();
        redirect('/portal/login');
    }

    /** GET /portal — sponsor dashboard: package, comp passes, assets. */
    public function dashboard(Request $request, array $args = []): void
    {
        SponsorAuth::requireLogin();
        $ctx = SponsorAccount::context(SponsorAuth::id() ?? 0);
        if (!$ctx) {
            SponsorAuth::logout();
            redirect('/portal/login');
        }
        echo (new View())->render('portal/dashboard', [
            'csrf'       => Csrf::token(),
            'pageTitle'  => 'Sponsor portal',
            'ctx'        => $ctx,
            'compPasses' => Ticket::compForEmail($ctx['account_email']),
            'assets'     => SponsorAsset::forAccount(SponsorAuth::id() ?? 0),
            'flashOk'    => flash('ok'),
            'flashErr'   => flash('error'),
        ]);
    }

    /** POST /portal/assets — upload a branding asset for review. */
    public function uploadAsset(Request $request, array $args = []): void
    {
        SponsorAuth::requireLogin();
        Csrf::verify($request);

        $type = $request->str('type');
        if (!in_array($type, SponsorAsset::TYPES, true)) {
            flash('error', 'Choose a valid asset type.');
            redirect('/portal');
        }
        try {
            $file = Upload::document($_FILES['file'] ?? [], 'asset');
            if (!$file) {
                flash('error', 'Please choose a file to upload.');
                redirect('/portal');
            }
            SponsorAsset::create(SponsorAuth::id() ?? 0, $type, $file);
            flash('ok', 'Uploaded — your ' . str_replace('_', ' ', $type) . ' is now pending review.');
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
        }
        redirect('/portal');
    }
}
