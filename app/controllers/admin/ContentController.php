<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Upload;
use App\Models\Audit;
use App\Models\Content;
use App\Models\Event;

/**
 * Schema-driven editor for all static frontend content (branding, hero, stats,
 * about, awards, footer) plus the Event details. Sections come from
 * Content::schema(); 'event' is a special section backed by the events table.
 */
final class ContentController extends AdminController
{
    protected array $allowedRoles = ['editor'];

    public function index(Request $request, array $args = []): void
    {
        $this->render('admin/content-index', [
            'pageTitle' => 'Content',
            'schema'    => Content::schema(),
        ]);
    }

    public function edit(Request $request, array $args = []): void
    {
        $slug = (string) ($args['section'] ?? '');

        if ($slug === 'event') {
            $this->render('admin/content-event', [
                'pageTitle' => 'Event details',
                'event'     => Event::current() ?? [],
            ]);
            return;
        }

        $section = Content::section($slug);
        if (!$section) {
            http_response_code(404);
            $this->render('admin/not-found', ['pageTitle' => 'Not found']);
            return;
        }
        $this->render('admin/content-section', [
            'pageTitle' => $section['title'],
            'slug'      => $slug,
            'section'   => $section,
        ]);
    }

    public function save(Request $request, array $args = []): void
    {
        Csrf::verify($request);
        $slug = (string) ($args['section'] ?? '');

        if ($slug === 'event') {
            Event::updateCurrent([
                'name'       => $request->str('name'),
                'edition'    => $request->str('edition'),
                'theme'      => $request->str('theme'),
                'start_date' => $request->str('start_date'),
                'end_date'   => $request->str('end_date'),
                'venue'      => $request->str('venue'),
            ]);
            Audit::log('update', 'event', null);
            flash('ok', 'Event details saved.');
            redirect('/admin/content/event');
        }

        $section = Content::section($slug);
        if (!$section) {
            http_response_code(404);
            $this->render('admin/not-found', ['pageTitle' => 'Not found']);
            return;
        }

        foreach ($section['fields'] as $f) {
            $key = $f['key'];
            if (($f['type'] ?? 'text') === 'image') {
                // Clear if requested.
                if ($request->input('remove_' . $key)) {
                    Upload::delete(\App\Models\Setting::get($key));
                    Content::set($key, '');
                    continue;
                }
                // Replace if a new file was uploaded.
                try {
                    $file = Upload::image($_FILES[$key] ?? [], 'brand');
                    if ($file) {
                        Upload::delete(\App\Models\Setting::get($key));
                        Content::set($key, $file);
                    }
                } catch (\RuntimeException $e) {
                    flash('error', $e->getMessage());
                    redirect('/admin/content/' . $slug);
                }
                continue;
            }
            Content::set($key, $request->str($key));
        }

        Audit::log('update', 'content', null, ['section' => $slug]);
        flash('ok', $section['title'] . ' saved.');
        redirect('/admin/content/' . $slug);
    }
}
