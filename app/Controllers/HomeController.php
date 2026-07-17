<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\AwardCategory;
use App\Models\Event;
use App\Models\Gallery;
use App\Models\Setting;
use App\Models\Speaker;
use App\Models\SponsorshipPackage;
use App\Models\TicketType;
use App\Models\Testimonial;

final class HomeController extends Controller
{
    public function index(Request $request, array $args = []): void
    {
        $packages = SponsorshipPackage::grouped();

        $this->render('pages/home', [
            'pageTitle'    => null, // use default
            'event'        => Event::current(),
            'settings'     => Setting::all(),
            'speakers'     => Speaker::forEvent(),
            'pass'         => TicketType::primary(),
            'sponsorTiers' => $packages['sponsor'],
            'booths'       => $packages['exhibition'],
            'categories'   => AwardCategory::active(),
            'testimonials' => Testimonial::forEvent(),
            'gallery'      => Gallery::all(),
            'galEditions'  => Gallery::editions(),
        ]);
    }
}
