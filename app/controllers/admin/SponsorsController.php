<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Database;
use App\Core\Mailer;
use App\Core\Request;
use App\Core\TicketIssuer;
use App\Models\Audit;
use App\Models\SponsorAccount;
use App\Models\SponsorApplication;
use App\Models\SponsorAsset;
use App\Models\Ticket;
use App\Models\TicketType;

final class SponsorsController extends AdminController
{
    protected array $allowedRoles = ['finance', 'editor'];

    public function index(Request $request, array $args = []): void
    {
        $status = $request->str('status');
        $this->render('admin/sponsors', [
            'pageTitle'    => 'Sponsors',
            'applications' => SponsorApplication::forAdmin($status),
            'stats'        => SponsorApplication::stats(),
            'status'       => $status,
            'assets'       => SponsorAsset::pendingForAdmin(),
        ]);
    }

    public function show(Request $request, array $args = []): void
    {
        $app = SponsorApplication::withPackage((int) ($args['id'] ?? 0));
        if (!$app) {
            http_response_code(404);
            $this->render('admin/not-found', ['pageTitle' => 'Not found']);
            return;
        }
        $account = SponsorAccount::findByApplication((int) $app['id']);
        $this->render('admin/sponsor-detail', [
            'pageTitle' => $app['company_name'],
            'app'       => $app,
            'account'   => $account,
            'compPasses'=> Ticket::compForEmail($app['email']),
        ]);
    }

    /** Update status (contacted / invoiced / paid). */
    public function setStatus(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        $id = (int) ($args['id'] ?? 0);
        $status = $request->str('status');
        if (SponsorApplication::setStatus($id, $status)) {
            Audit::log('sponsor_status', 'sponsor_application', $id, ['status' => $status]);
            flash('ok', 'Status updated to ' . $status . '.');
        } else {
            flash('error', 'Invalid status.');
        }
        redirect('/admin/sponsors/' . $id);
    }

    /**
     * Confirm a sponsor: provision a portal login + auto-issue complimentary
     * delegate passes from the package's comp_passes. Idempotent — re-confirming
     * an already-provisioned sponsor does not create a second account or passes.
     */
    public function confirm(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        $id = (int) ($args['id'] ?? 0);
        $app = SponsorApplication::withPackage($id);
        if (!$app) {
            http_response_code(404);
            $this->render('admin/not-found', ['pageTitle' => 'Not found']);
            return;
        }

        if (SponsorAccount::findByApplication($id)) {
            SponsorApplication::setStatus($id, 'confirmed');
            flash('ok', 'This sponsor is already provisioned. Status set to confirmed.');
            redirect('/admin/sponsors/' . $id);
        }

        $comp = (int) ($app['comp_passes'] ?? 0);
        $compType = $comp > 0 ? TicketType::compType() : null;
        $password = $this->generatePassword();

        Database::beginTransaction();
        try {
            $accountId = SponsorAccount::create($id, $app['email'], $password);
            $tickets = [];
            if ($comp > 0) {
                $tickets = Ticket::issueComp(
                    $comp,
                    $compType ? (int) $compType['id'] : null,
                    $app['company_name'],
                    $app['email']
                );
            }
            SponsorApplication::setStatus($id, 'confirmed');
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            error_log('Sponsor confirm failed: ' . $e->getMessage());
            flash('error', 'Could not confirm the sponsor: ' . $e->getMessage());
            redirect('/admin/sponsors/' . $id);
        }

        // Side effects after commit: QR files + welcome email.
        if (!empty($tickets)) {
            TicketIssuer::renderQrFiles($tickets);
        }
        $this->sendWelcome($app, $password, $comp);
        Audit::log('sponsor_confirm', 'sponsor_application', $id, ['comp_passes' => $comp]);

        flash('ok', 'Sponsor confirmed — portal login created' . ($comp > 0 ? " and {$comp} complimentary pass" . ($comp === 1 ? '' : 'es') . ' issued.' : '.'));
        redirect('/admin/sponsors/' . $id);
    }

    /** Approve / reject an uploaded sponsor asset. */
    public function reviewAsset(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        $id = (int) ($args['id'] ?? 0);
        $status = $request->str('status');
        $notes = $request->str('notes');
        if (SponsorAsset::setStatus($id, $status, $notes)) {
            Audit::log('asset_review', 'sponsor_asset', $id, ['status' => $status]);
            flash('ok', 'Asset marked as ' . $status . '.');
        } else {
            flash('error', 'Invalid status.');
        }
        redirect('/admin/sponsors');
    }

    private function generatePassword(): string
    {
        // Readable, no ambiguous characters.
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
        $out = '';
        for ($i = 0; $i < 12; $i++) {
            $out .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
        return $out;
    }

    private function sendWelcome(array $app, string $password, int $comp): void
    {
        $view = new \App\Core\View();
        $html = $view->renderPartial('emails/sponsor-welcome', [
            'app'        => $app,
            'password'   => $password,
            'comp'       => $comp,
            'portalUrl'  => url('/portal/login'),
        ]);
        (new Mailer())->send($app['email'], $app['contact_name'], 'Your sponsor portal access — Nigeria GovTech', $html);
    }
}
