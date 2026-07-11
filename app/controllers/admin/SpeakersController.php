<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Upload;
use App\Core\Validator;
use App\Models\Audit;
use App\Models\Speaker;

final class SpeakersController extends AdminController
{
    protected array $allowedRoles = ['editor'];

    public function index(Request $request, array $args = []): void
    {
        $this->render('admin/speakers', [
            'pageTitle' => 'Speakers',
            'speakers'  => Speaker::allForAdmin(),
        ]);
    }

    public function create(Request $request, array $args = []): void
    {
        $this->render('admin/speaker-form', [
            'pageTitle' => 'Add speaker',
            'speaker'   => null,
            'errors'    => [],
        ]);
    }

    public function store(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        $data = $this->collect($request);
        $v = new Validator($data, ['name' => 'required|max:160']);
        if ($v->fails()) {
            $this->renderForm(null, $v->errors(), $data);
            return;
        }
        try {
            if ($photo = Upload::image($_FILES['photo'] ?? [], 'spk')) {
                $data['photo'] = $photo;
            }
        } catch (\RuntimeException $e) {
            $this->renderForm(null, ['photo' => $e->getMessage()], $data);
            return;
        }
        $id = Speaker::create($data);
        Audit::log('create', 'speaker', $id, ['name' => $data['name']]);
        flash('ok', 'Speaker added.');
        redirect('/admin/speakers');
    }

    public function edit(Request $request, array $args = []): void
    {
        $speaker = Speaker::find((int) ($args['id'] ?? 0));
        if (!$speaker) {
            http_response_code(404);
            $this->render('admin/not-found', ['pageTitle' => 'Not found']);
            return;
        }
        $this->render('admin/speaker-form', [
            'pageTitle' => 'Edit speaker',
            'speaker'   => $speaker,
            'errors'    => [],
        ]);
    }

    public function update(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        $id = (int) ($args['id'] ?? 0);
        $speaker = Speaker::find($id);
        if (!$speaker) {
            http_response_code(404);
            $this->render('admin/not-found', ['pageTitle' => 'Not found']);
            return;
        }
        $data = $this->collect($request);
        $data['photo'] = $speaker['photo']; // keep existing unless replaced
        $v = new Validator($data, ['name' => 'required|max:160']);
        if ($v->fails()) {
            $this->renderForm($speaker, $v->errors(), $data);
            return;
        }
        try {
            if ($photo = Upload::image($_FILES['photo'] ?? [], 'spk')) {
                Upload::delete($speaker['photo']);
                $data['photo'] = $photo;
            }
        } catch (\RuntimeException $e) {
            $this->renderForm($speaker, ['photo' => $e->getMessage()], $data);
            return;
        }
        Speaker::update($id, $data);
        Audit::log('update', 'speaker', $id, ['name' => $data['name']]);
        flash('ok', 'Speaker updated.');
        redirect('/admin/speakers');
    }

    public function destroy(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        $id = (int) ($args['id'] ?? 0);
        $speaker = Speaker::find($id);
        if ($speaker) {
            Upload::delete($speaker['photo']);
            Speaker::delete($id);
            Audit::log('delete', 'speaker', $id, ['name' => $speaker['name']]);
            flash('ok', 'Speaker removed.');
        }
        redirect('/admin/speakers');
    }

    private function collect(Request $request): array
    {
        return [
            'name'         => $request->str('name'),
            'role'         => $request->str('role'),
            'organization' => $request->str('organization'),
            'bio'          => $request->str('bio'),
            'featured'     => $request->input('featured') ? 1 : 0,
            'sort'         => $request->int('sort'),
            'photo'        => '',
        ];
    }

    private function renderForm(?array $speaker, array $errors, array $data): void
    {
        // Merge submitted values back so the form repopulates.
        $merged = $speaker ? array_merge($speaker, $data) : $data;
        $this->render('admin/speaker-form', [
            'pageTitle' => $speaker ? 'Edit speaker' : 'Add speaker',
            'speaker'   => $merged,
            'errors'    => $errors,
        ]);
    }
}
