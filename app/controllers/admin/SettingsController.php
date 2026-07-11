<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Models\Audit;
use App\Models\Setting;

final class SettingsController extends AdminController
{
    protected array $allowedRoles = ['editor'];

    /** Keys we expose for editing in the admin. */
    private const KEYS = [
        'organizer_name'   => 'Organizer name',
        'organizer_note'   => 'Organizer note',
        'countdown_target' => 'Countdown target (ISO 8601, e.g. 2026-10-07T09:00:00+01:00)',
        'contact_email'    => 'Contact email',
    ];

    public function edit(Request $request, array $args = []): void
    {
        $this->render('admin/settings', [
            'pageTitle' => 'Settings',
            'keys'      => self::KEYS,
            'values'    => Setting::all(),
        ]);
    }

    public function update(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        foreach (array_keys(self::KEYS) as $key) {
            $val = $request->str($key);
            Setting::set($key, $val);
        }
        Audit::log('update', 'settings', null, ['keys' => array_keys(self::KEYS)]);
        flash('ok', 'Settings saved.');
        redirect('/admin/settings');
    }
}
