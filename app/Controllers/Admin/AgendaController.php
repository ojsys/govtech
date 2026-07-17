<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Validator;
use App\Models\AgendaSession;
use App\Models\Audit;

final class AgendaController extends AdminController
{
    protected array $allowedRoles = ['editor'];

    public function index(Request $request, array $args = []): void
    {
        $this->render('admin/agenda', [
            'pageTitle' => 'Agenda',
            'sessions'  => AgendaSession::allForAdmin(),
        ]);
    }

    public function create(Request $request, array $args = []): void
    {
        $this->render('admin/agenda-form', [
            'pageTitle' => 'Add session',
            'session'   => null,
            'days'      => AgendaSession::dayLabels(),
            'errors'    => [],
        ]);
    }

    public function store(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        $data = $this->collect($request);
        $v = new Validator($data, ['title' => 'required|max:200', 'day_label' => 'required|max:80']);
        if ($v->fails()) {
            $this->renderForm(null, $v->errors(), $data);
            return;
        }
        $id = AgendaSession::create($data);
        Audit::log('create', 'agenda_session', $id, ['title' => $data['title']]);
        flash('ok', 'Session added.');
        redirect('/admin/agenda');
    }

    public function edit(Request $request, array $args = []): void
    {
        $session = AgendaSession::find((int) ($args['id'] ?? 0));
        if (!$session) {
            http_response_code(404);
            $this->render('admin/not-found', ['pageTitle' => 'Not found']);
            return;
        }
        $this->render('admin/agenda-form', [
            'pageTitle' => 'Edit session',
            'session'   => $session,
            'days'      => AgendaSession::dayLabels(),
            'errors'    => [],
        ]);
    }

    public function update(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        $id = (int) ($args['id'] ?? 0);
        $session = AgendaSession::find($id);
        if (!$session) {
            http_response_code(404);
            $this->render('admin/not-found', ['pageTitle' => 'Not found']);
            return;
        }
        $data = $this->collect($request);
        $v = new Validator($data, ['title' => 'required|max:200', 'day_label' => 'required|max:80']);
        if ($v->fails()) {
            $this->renderForm($session, $v->errors(), $data);
            return;
        }
        AgendaSession::update($id, $data);
        Audit::log('update', 'agenda_session', $id, ['title' => $data['title']]);
        flash('ok', 'Session updated.');
        redirect('/admin/agenda');
    }

    public function destroy(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        $id = (int) ($args['id'] ?? 0);
        $session = AgendaSession::find($id);
        if ($session) {
            AgendaSession::delete($id);
            Audit::log('delete', 'agenda_session', $id, ['title' => $session['title']]);
            flash('ok', 'Session removed.');
        }
        redirect('/admin/agenda');
    }

    private function collect(Request $request): array
    {
        return [
            'day_label'    => $request->str('day_label'),
            'start_time'   => $request->str('start_time'),
            'end_time'     => $request->str('end_time'),
            'title'        => $request->str('title'),
            'description'  => $request->str('description'),
            'speaker'      => $request->str('speaker'),
            'location'     => $request->str('location'),
            'track'        => $request->str('track'),
            'is_break'     => $request->input('is_break') ? 1 : 0,
            'is_published' => $request->input('is_published') ? 1 : 0,
            'sort'         => $request->int('sort'),
        ];
    }

    private function renderForm(?array $session, array $errors, array $data): void
    {
        $merged = $session ? array_merge($session, $data) : $data;
        $this->render('admin/agenda-form', [
            'pageTitle' => $session ? 'Edit session' : 'Add session',
            'session'   => $merged,
            'days'      => AgendaSession::dayLabels(),
            'errors'    => $errors,
        ]);
    }
}
