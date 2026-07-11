<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Upload;
use App\Models\Audit;
use App\Models\Event;
use App\Models\Gallery;

final class GalleryController extends AdminController
{
    protected array $allowedRoles = ['editor'];

    public function index(Request $request, array $args = []): void
    {
        $this->render('admin/gallery', [
            'pageTitle'      => 'Gallery',
            'images'         => Gallery::allForAdmin(),
            'editions'       => Gallery::editions(),
            'currentEdition' => (string) (Event::current()['edition'] ?? ''),
        ]);
    }

    /** POST /admin/gallery — upload one or more images (caption applied to each). */
    public function store(Request $request, array $args = []): void
    {
        Csrf::verify($request);

        $caption = $request->str('caption');
        $edition = $request->str('edition');
        $entries = $this->normalizeFiles($_FILES['images'] ?? []);
        if ($entries === []) {
            flash('error', 'Please choose at least one image to upload.');
            redirect('/admin/gallery');
        }

        $sort = Gallery::nextSort();
        $added = 0;
        $errors = [];
        foreach ($entries as $entry) {
            try {
                $name = Upload::image($entry, 'gal');
                if ($name) {
                    Gallery::create($name, $caption, $sort++, $edition);
                    $added++;
                }
            } catch (\RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if ($added > 0) {
            Audit::log('upload', 'gallery', null, ['count' => $added]);
            flash('ok', $added . ' image' . ($added === 1 ? '' : 's') . ' added to the gallery.');
        }
        if ($errors) {
            flash('error', implode(' ', array_unique($errors)));
        }
        redirect('/admin/gallery');
    }

    /** POST /admin/gallery/{id} — update caption/sort. */
    public function update(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        $id = (int) ($args['id'] ?? 0);
        if (Gallery::find($id)) {
            Gallery::updateMeta($id, $request->str('caption'), $request->int('sort'), $request->str('edition'));
            Audit::log('update', 'gallery', $id);
            flash('ok', 'Image updated.');
        }
        redirect('/admin/gallery');
    }

    /** POST /admin/gallery/{id}/delete */
    public function destroy(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        $id = (int) ($args['id'] ?? 0);
        $img = Gallery::find($id);
        if ($img) {
            Upload::delete($img['image']);
            Gallery::delete($id);
            Audit::log('delete', 'gallery', $id);
            flash('ok', 'Image removed.');
        }
        redirect('/admin/gallery');
    }

    /** Turn a multi-file $_FILES entry into a list of single-file arrays. */
    private function normalizeFiles(array $f): array
    {
        if (empty($f['name'])) {
            return [];
        }
        // Single file (non-array input)
        if (!is_array($f['name'])) {
            return ($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE ? [] : [$f];
        }
        $entries = [];
        foreach ($f['name'] as $i => $name) {
            if (($f['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $entries[] = [
                'name'     => $f['name'][$i],
                'type'     => $f['type'][$i] ?? '',
                'tmp_name' => $f['tmp_name'][$i],
                'error'    => $f['error'][$i],
                'size'     => $f['size'][$i] ?? 0,
            ];
        }
        return $entries;
    }
}
