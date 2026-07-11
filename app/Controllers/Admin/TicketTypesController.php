<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Audit;
use App\Models\TicketType;

final class TicketTypesController extends AdminController
{
    protected array $allowedRoles = ['editor', 'finance'];

    public function index(Request $request, array $args = []): void
    {
        $this->render('admin/ticket-types', [
            'pageTitle' => 'Ticket types',
            'tickets'   => TicketType::allForAdmin(),
        ]);
    }

    public function create(Request $request, array $args = []): void
    {
        $this->form(null, []);
    }

    public function store(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        [$data, $raw] = $this->collect($request, 0);
        $v = new Validator($raw, ['name' => 'required|max:120', 'price' => 'required']);
        if ($v->fails()) {
            $this->form(null, $v->errors(), $raw);
            return;
        }
        $id = TicketType::create($data);
        Audit::log('create', 'ticket_type', $id, ['name' => $data['name']]);
        flash('ok', 'Ticket type created.');
        redirect('/admin/ticket-types');
    }

    public function edit(Request $request, array $args = []): void
    {
        $t = TicketType::find((int) ($args['id'] ?? 0));
        if (!$t) {
            http_response_code(404);
            $this->render('admin/not-found', ['pageTitle' => 'Not found']);
            return;
        }
        $this->form($t, []);
    }

    public function update(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        $id = (int) ($args['id'] ?? 0);
        $t = TicketType::find($id);
        if (!$t) {
            http_response_code(404);
            $this->render('admin/not-found', ['pageTitle' => 'Not found']);
            return;
        }
        [$data, $raw] = $this->collect($request, $id);
        $v = new Validator($raw, ['name' => 'required|max:120', 'price' => 'required']);
        if ($v->fails()) {
            $this->form(array_merge($t, $raw), $v->errors());
            return;
        }
        TicketType::update($id, $data);
        Audit::log('update', 'ticket_type', $id, ['name' => $data['name']]);
        flash('ok', 'Ticket type updated.');
        redirect('/admin/ticket-types');
    }

    public function destroy(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        $id = (int) ($args['id'] ?? 0);
        $t = TicketType::find($id);
        if (!$t) {
            redirect('/admin/ticket-types');
        }
        if (TicketType::isReferenced($id)) {
            // Don't break historical orders — deactivate instead.
            TicketType::update($id, array_merge($t, ['is_active' => 0]));
            flash('error', 'This pass has orders against it, so it was hidden (deactivated) rather than deleted.');
        } else {
            TicketType::delete($id);
            Audit::log('delete', 'ticket_type', $id, ['name' => $t['name']]);
            flash('ok', 'Ticket type deleted.');
        }
        redirect('/admin/ticket-types');
    }

    /**
     * @return array{0:array,1:array} [model-ready data, raw-for-redisplay]
     */
    private function collect(Request $request, int $id): array
    {
        $name = $request->str('name');
        $slug = $request->str('slug');
        $slug = TicketType::uniqueSlug(slugify($slug !== '' ? $slug : $name), $id);
        $raw = [
            'name'        => $name,
            'slug'        => $slug,
            'price'       => $request->str('price'),
            'description' => $request->str('description'),
            'perks'       => $request->str('perks'),
            'group_size'  => $request->int('group_size', 1),
            'quota'       => $request->str('quota'),
            'featured'    => $request->input('featured') ? 1 : 0,
            'is_active'   => $request->input('is_active') ? 1 : 0,
            'sort'        => $request->int('sort'),
        ];
        $data = [
            'name'        => $name,
            'slug'        => $slug,
            'price_kobo'  => kobo_from_naira($raw['price']),
            'description' => $raw['description'],
            'perks_json'  => perks_from_lines($raw['perks']),
            'group_size'  => $raw['group_size'],
            'quota'       => $raw['quota'],
            'featured'    => $raw['featured'],
            'is_active'   => $raw['is_active'],
            'sort'        => $raw['sort'],
        ];
        return [$data, $raw];
    }

    private function form(?array $ticket, array $errors, array $raw = []): void
    {
        $this->render('admin/ticket-type-form', [
            'pageTitle' => $ticket && !empty($ticket['id']) ? 'Edit ticket type' : 'New ticket type',
            'ticket'    => $ticket,
            'raw'       => $raw,
            'errors'    => $errors,
        ]);
    }
}
