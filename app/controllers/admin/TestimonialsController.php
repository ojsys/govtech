<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Audit;
use App\Models\Testimonial;

final class TestimonialsController extends AdminController
{
    protected array $allowedRoles = ['editor'];

    public function index(Request $request, array $args = []): void
    {
        $this->render('admin/testimonials', [
            'pageTitle'    => 'Testimonials',
            'testimonials' => Testimonial::allForAdmin(),
        ]);
    }

    public function create(Request $request, array $args = []): void
    {
        $this->form(null, []);
    }

    public function store(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        $data = $this->collect($request);
        $v = new Validator($data, ['name' => 'required|max:160', 'quote' => 'required|max:1000']);
        if ($v->fails()) {
            $this->form(null, $v->errors(), $data);
            return;
        }
        $id = Testimonial::create($data);
        Audit::log('create', 'testimonial', $id);
        flash('ok', 'Testimonial added.');
        redirect('/admin/testimonials');
    }

    public function edit(Request $request, array $args = []): void
    {
        $t = Testimonial::find((int) ($args['id'] ?? 0));
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
        $t = Testimonial::find($id);
        if (!$t) {
            http_response_code(404);
            $this->render('admin/not-found', ['pageTitle' => 'Not found']);
            return;
        }
        $data = $this->collect($request);
        $v = new Validator($data, ['name' => 'required|max:160', 'quote' => 'required|max:1000']);
        if ($v->fails()) {
            $this->form(array_merge($t, $data), $v->errors());
            return;
        }
        Testimonial::update($id, $data);
        Audit::log('update', 'testimonial', $id);
        flash('ok', 'Testimonial updated.');
        redirect('/admin/testimonials');
    }

    public function destroy(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        $id = (int) ($args['id'] ?? 0);
        if (Testimonial::find($id)) {
            Testimonial::delete($id);
            Audit::log('delete', 'testimonial', $id);
            flash('ok', 'Testimonial removed.');
        }
        redirect('/admin/testimonials');
    }

    private function collect(Request $request): array
    {
        return [
            'name'  => $request->str('name'),
            'role'  => $request->str('role'),
            'quote' => $request->str('quote'),
            'sort'  => $request->int('sort'),
        ];
    }

    private function form(?array $testimonial, array $errors, array $data = []): void
    {
        $merged = $testimonial ? array_merge($testimonial, $data) : $data;
        $this->render('admin/testimonial-form', [
            'pageTitle'   => $testimonial ? 'Edit testimonial' : 'Add testimonial',
            'testimonial' => $merged ?: null,
            'errors'      => $errors,
        ]);
    }
}
