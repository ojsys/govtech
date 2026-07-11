<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Audit;
use App\Models\AwardCategory;
use App\Models\Nomination;
use App\Models\Vote;

final class AwardsController extends AdminController
{
    protected array $allowedRoles = ['editor'];

    /** GET /admin/awards — categories overview + counts + live tally. */
    public function index(Request $request, array $args = []): void
    {
        $this->render('admin/awards', [
            'pageTitle'  => 'Awards',
            'categories' => AwardCategory::allForAdmin(),
            'counts'     => Nomination::countsByStatus(),
            'voteStats'  => Vote::stats(),
        ]);
    }

    public function storeCategory(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        $data = [
            'title'       => $request->str('title'),
            'description' => $request->str('description'),
            'is_active'   => $request->input('is_active') ? 1 : 0,
            'sort'        => $request->int('sort'),
        ];
        $v = new Validator($data, ['title' => 'required|max:160']);
        if ($v->passes()) {
            $id = AwardCategory::create($data);
            Audit::log('create', 'award_category', $id, ['title' => $data['title']]);
            flash('ok', 'Category added.');
        } else {
            flash('error', $v->first());
        }
        redirect('/admin/awards');
    }

    public function toggleCategory(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        $id = (int) ($args['id'] ?? 0);
        AwardCategory::toggle($id);
        Audit::log('toggle', 'award_category', $id);
        redirect('/admin/awards');
    }

    public function editCategory(Request $request, array $args = []): void
    {
        $cat = AwardCategory::findInEvent((int) ($args['id'] ?? 0));
        if (!$cat) {
            http_response_code(404);
            $this->render('admin/not-found', ['pageTitle' => 'Not found']);
            return;
        }
        $this->render('admin/award-category-form', ['pageTitle' => 'Edit category', 'category' => $cat, 'errors' => []]);
    }

    public function updateCategory(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        $id = (int) ($args['id'] ?? 0);
        $cat = AwardCategory::findInEvent($id);
        if (!$cat) {
            http_response_code(404);
            $this->render('admin/not-found', ['pageTitle' => 'Not found']);
            return;
        }
        $data = ['title' => $request->str('title'), 'description' => $request->str('description'), 'sort' => $request->int('sort')];
        $v = new Validator($data, ['title' => 'required|max:160']);
        if ($v->fails()) {
            $this->render('admin/award-category-form', ['pageTitle' => 'Edit category', 'category' => array_merge($cat, $data), 'errors' => $v->errors()]);
            return;
        }
        AwardCategory::update($id, $data);
        Audit::log('update', 'award_category', $id, ['title' => $data['title']]);
        flash('ok', 'Category updated.');
        redirect('/admin/awards');
    }

    public function deleteCategory(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        $id = (int) ($args['id'] ?? 0);
        $cat = AwardCategory::findInEvent($id);
        if (!$cat) {
            redirect('/admin/awards');
        }
        if (AwardCategory::isReferenced($id)) {
            flash('error', 'This category has nominations, so it can\'t be deleted. Use Hide to remove it from the site.');
        } else {
            AwardCategory::delete($id);
            Audit::log('delete', 'award_category', $id, ['title' => $cat['title']]);
            flash('ok', 'Category deleted.');
        }
        redirect('/admin/awards');
    }

    /** GET /admin/awards/nominations — moderation queue. */
    public function nominations(Request $request, array $args = []): void
    {
        $categoryId = $request->int('category');
        $status = $request->str('status');
        $this->render('admin/nominations', [
            'pageTitle'   => 'Nominations',
            'nominations' => Nomination::forAdmin($categoryId, $status),
            'categories'  => AwardCategory::active(),
            'category'    => $categoryId,
            'status'      => $status,
        ]);
    }

    /** POST /admin/awards/nominations/{id}/status — approve/shortlist/reject. */
    public function moderate(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        $id = (int) ($args['id'] ?? 0);
        $status = $request->str('status');
        if (Nomination::setStatus($id, $status)) {
            Audit::log('moderate', 'nomination', $id, ['status' => $status]);
            flash('ok', 'Nomination marked as ' . $status . '.');
        } else {
            flash('error', 'Invalid status.');
        }
        redirect('/admin/awards/nominations' . ($request->int('category') ? '?category=' . $request->int('category') : ''));
    }
}
