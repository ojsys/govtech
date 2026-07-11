<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Audit;
use App\Models\SponsorshipPackage;

final class PackagesController extends AdminController
{
    protected array $allowedRoles = ['editor', 'finance'];

    public function index(Request $request, array $args = []): void
    {
        $this->render('admin/packages', [
            'pageTitle' => 'Sponsorship packages',
            'packages'  => SponsorshipPackage::allForAdmin(),
        ]);
    }

    public function create(Request $request, array $args = []): void
    {
        $this->form(null, []);
    }

    public function store(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        [$data, $raw] = $this->collect($request);
        $v = new Validator($raw, ['name' => 'required|max:120', 'price' => 'required', 'type' => 'in:sponsor,exhibition']);
        if ($v->fails()) {
            $this->form(null, $v->errors(), $raw);
            return;
        }
        $id = SponsorshipPackage::create($data);
        Audit::log('create', 'sponsorship_package', $id, ['name' => $data['name']]);
        flash('ok', 'Package created.');
        redirect('/admin/packages');
    }

    public function edit(Request $request, array $args = []): void
    {
        $p = SponsorshipPackage::find((int) ($args['id'] ?? 0));
        if (!$p) {
            http_response_code(404);
            $this->render('admin/not-found', ['pageTitle' => 'Not found']);
            return;
        }
        $this->form($p, []);
    }

    public function update(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        $id = (int) ($args['id'] ?? 0);
        $p = SponsorshipPackage::find($id);
        if (!$p) {
            http_response_code(404);
            $this->render('admin/not-found', ['pageTitle' => 'Not found']);
            return;
        }
        [$data, $raw] = $this->collect($request);
        $v = new Validator($raw, ['name' => 'required|max:120', 'price' => 'required', 'type' => 'in:sponsor,exhibition']);
        if ($v->fails()) {
            $this->form(array_merge($p, $raw), $v->errors());
            return;
        }
        SponsorshipPackage::update($id, $data);
        Audit::log('update', 'sponsorship_package', $id, ['name' => $data['name']]);
        flash('ok', 'Package updated.');
        redirect('/admin/packages');
    }

    public function destroy(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        $id = (int) ($args['id'] ?? 0);
        $p = SponsorshipPackage::find($id);
        if (!$p) {
            redirect('/admin/packages');
        }
        if (SponsorshipPackage::isReferenced($id)) {
            SponsorshipPackage::update($id, array_merge($p, ['is_active' => 0]));
            flash('error', 'This package has applications against it, so it was hidden (deactivated) rather than deleted.');
        } else {
            SponsorshipPackage::delete($id);
            Audit::log('delete', 'sponsorship_package', $id, ['name' => $p['name']]);
            flash('ok', 'Package deleted.');
        }
        redirect('/admin/packages');
    }

    /** @return array{0:array,1:array} */
    private function collect(Request $request): array
    {
        $raw = [
            'type'        => $request->str('type', 'sponsor'),
            'name'        => $request->str('name'),
            'price'       => $request->str('price'),
            'booth_size'  => $request->str('booth_size'),
            'perks'       => $request->str('perks'),
            'comp_passes' => $request->int('comp_passes'),
            'is_active'   => $request->input('is_active') ? 1 : 0,
            'sort'        => $request->int('sort'),
        ];
        $data = [
            'type'        => $raw['type'],
            'name'        => $raw['name'],
            'price_kobo'  => kobo_from_naira($raw['price']),
            'booth_size'  => $raw['booth_size'],
            'perks_json'  => perks_from_lines($raw['perks']),
            'comp_passes' => $raw['comp_passes'],
            'is_active'   => $raw['is_active'],
            'sort'        => $raw['sort'],
        ];
        return [$data, $raw];
    }

    private function form(?array $package, array $errors, array $raw = []): void
    {
        $this->render('admin/package-form', [
            'pageTitle' => $package && !empty($package['id']) ? 'Edit package' : 'New package',
            'package'   => $package,
            'raw'       => $raw,
            'errors'    => $errors,
        ]);
    }
}
