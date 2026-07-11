<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Mailer;
use App\Core\RateLimit;
use App\Core\Request;
use App\Core\Upload;
use App\Core\Validator;
use App\Models\Setting;
use App\Models\SponsorApplication;
use App\Models\SponsorshipPackage;

final class SponsorController extends Controller
{
    /** GET /sponsor/apply — application form (optionally pre-selecting a package). */
    public function applyForm(Request $request, array $args = []): void
    {
        $packages = SponsorshipPackage::grouped();
        $this->render('pages/sponsor-apply', [
            'pageTitle' => 'Apply to sponsor',
            'packages'  => array_merge($packages['sponsor'], $packages['exhibition']),
            'selected'  => $request->int('package'),
            'errors'    => [],
        ]);
    }

    /** POST /sponsor/apply — create a sponsor application. */
    public function apply(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        if (!RateLimit::allow('sponsor-apply:' . $request->ip(), 5, 180) || !RateLimit::honeypotPassed($request)) {
            flash('error', 'Too many submissions. Please wait a moment and try again.');
            redirect('/sponsor/apply');
        }

        $data = [
            'package_id'   => $request->int('package_id'),
            'company_name' => $request->str('company_name'),
            'contact_name' => $request->str('contact_name'),
            'email'        => $request->str('email'),
            'phone'        => $request->str('phone'),
            'message'      => $request->str('message'),
        ];
        $v = new Validator($data, [
            'package_id'   => 'required|int',
            'company_name' => 'required|max:200',
            'contact_name' => 'required|max:160',
            'email'        => 'required|email|max:160',
            'phone'        => 'required|max:40',
        ]);

        $package = SponsorshipPackage::findActiveById((int) $data['package_id']);
        $errors = $v->errors();
        if (!$package && !isset($errors['package_id'])) {
            $errors['package_id'] = 'Please choose a valid package.';
        }

        // Optional logo upload.
        $logo = '';
        if (!$errors) {
            try {
                $logo = Upload::image($_FILES['logo'] ?? [], 'splogo') ?? '';
            } catch (\RuntimeException $e) {
                $errors['logo'] = $e->getMessage();
            }
        }

        if ($errors) {
            $_SESSION['_old'] = $data;
            $packages = SponsorshipPackage::grouped();
            $this->render('pages/sponsor-apply', [
                'pageTitle' => 'Apply to sponsor',
                'packages'  => array_merge($packages['sponsor'], $packages['exhibition']),
                'selected'  => (int) $data['package_id'],
                'errors'    => $errors,
            ]);
            return;
        }

        $data['logo_path'] = $logo;
        SponsorApplication::create($data);
        // Email is best-effort — never fail a saved application on a mail hiccup.
        try {
            $this->notify($data, $package);
        } catch (\Throwable $e) {
            error_log('Sponsor application notify failed: ' . $e->getMessage());
        }

        flash('ok', 'Thank you — your application has been received. Our partnerships team will be in touch shortly.');
        redirect('/sponsor/apply');
    }

    private function notify(array $data, array $package): void
    {
        $mailer = new Mailer();
        // Applicant acknowledgement.
        $ackHtml = '<p>Hi ' . e($data['contact_name']) . ',</p>'
            . '<p>Thank you for your interest in partnering with the Nigeria GovTech Conference &amp; Awards. '
            . 'We\'ve received your application for the <strong>' . e($package['name']) . '</strong> package and will be in touch shortly with next steps.</p>'
            . '<p>— The Partnerships Team</p>';
        $mailer->send($data['email'], $data['contact_name'], 'We received your sponsorship application', $ackHtml);

        // Organiser notification.
        if ($to = Setting::get('contact_email')) {
            $html = '<p><strong>New sponsor application</strong></p>'
                . '<p><strong>Company:</strong> ' . e($data['company_name']) . '<br>'
                . '<strong>Package:</strong> ' . e($package['name']) . '<br>'
                . '<strong>Contact:</strong> ' . e($data['contact_name']) . ' &lt;' . e($data['email']) . '&gt; · ' . e($data['phone']) . '</p>'
                . '<p>' . nl2br(e($data['message'])) . '</p>';
            $mailer->send($to, 'GovTech Conference', 'New sponsor application: ' . $data['company_name'], $html);
        }
    }
}
