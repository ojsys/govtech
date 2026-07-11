<?php
/**
 * Route table. Returns a configured Router.
 * Add routes here as later phases land (registration, awards, sponsor, admin).
 */
declare(strict_types=1);

use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\PageController;
use App\Controllers\RegistrationController;
use App\Controllers\PaymentController;
use App\Controllers\AwardsController;
use App\Controllers\SponsorController;
use App\Controllers\PortalController;
use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\OrdersController;
use App\Controllers\Admin\CheckInController;
use App\Controllers\Admin\SpeakersController;
use App\Controllers\Admin\GalleryController;
use App\Controllers\Admin\ContentController;
use App\Controllers\Admin\TestimonialsController;
use App\Controllers\Admin\TicketTypesController;
use App\Controllers\Admin\PackagesController;
use App\Controllers\Admin\SettingsController;
use App\Controllers\Admin\AwardsController as AdminAwardsController;
use App\Controllers\Admin\SponsorsController as AdminSponsorsController;
use App\Controllers\Admin\ReportsController;

$router = new Router();

/* ---- Public site (Phase 1) ---- */
$router->get('/', [HomeController::class, 'index']);
$router->get('/about', [PageController::class, 'about']);
$router->get('/sponsor', [PageController::class, 'sponsorship']);
$router->get('/contact', [PageController::class, 'contact']);
$router->post('/contact', [PageController::class, 'contactSubmit']);
$router->post('/newsletter/subscribe', [PageController::class, 'subscribe']);

/* ---- Registration + Paystack + tickets (Phase 2) ---- */
$router->get('/register', [RegistrationController::class, 'show']);
$router->post('/register', [RegistrationController::class, 'store']);
$router->get('/checkout/callback', [PaymentController::class, 'callback']);
$router->post('/payment/webhook', [PaymentController::class, 'webhook']);
$router->get('/order/{reference}', [PaymentController::class, 'confirmation']);
$router->get('/verify', [PaymentController::class, 'verifyTicket']);   // public QR target
$router->get('/ticket/{code}/qr.png', [PaymentController::class, 'qrPng']);
$router->get('/ticket/{code}/qr', [PaymentController::class, 'qr']);
$router->get('/ticket/{code}', [PaymentController::class, 'ticket']);

/* ---- Awards: nominations + email-verified voting (Phase 4) ---- */
$router->get('/awards', [AwardsController::class, 'index']);
$router->get('/awards/results', [AwardsController::class, 'results']);
$router->get('/awards/nominate', [AwardsController::class, 'nominateForm']);
$router->post('/awards/nominate', [AwardsController::class, 'nominate']);
$router->post('/awards/vote', [AwardsController::class, 'vote']);
$router->get('/awards/vote/verify', [AwardsController::class, 'verify']);

/* ---- Sponsor/Exhibitor portal (Phase 5) ---- */
// Public application
$router->get('/sponsor/apply', [SponsorController::class, 'applyForm']);
$router->post('/sponsor/apply', [SponsorController::class, 'apply']);
// Sponsor portal (SponsorAuth)
$router->get('/portal/login', [PortalController::class, 'showLogin']);
$router->post('/portal/login', [PortalController::class, 'login']);
$router->post('/portal/logout', [PortalController::class, 'logout']);
$router->get('/portal', [PortalController::class, 'dashboard']);
$router->post('/portal/assets', [PortalController::class, 'uploadAsset']);

/* ---- Admin dashboard (Phase 3) ---- */
// Auth
$router->get('/admin/login', [AuthController::class, 'showLogin']);
$router->post('/admin/login', [AuthController::class, 'login']);
$router->post('/admin/logout', [AuthController::class, 'logout']);
// Dashboard
$router->get('/admin', [DashboardController::class, 'index']);
// Orders + attendees + export
$router->get('/admin/orders', [OrdersController::class, 'index']);
$router->get('/admin/orders/export.csv', [OrdersController::class, 'export']);
$router->get('/admin/orders/{reference}/tickets', [OrdersController::class, 'tickets']);
$router->get('/admin/orders/{reference}', [OrdersController::class, 'show']);
// Check-in
$router->get('/admin/checkin', [CheckInController::class, 'index']);
$router->post('/admin/checkin/scan', [CheckInController::class, 'scan']);
$router->get('/checkin/verify', [CheckInController::class, 'verify']); // QR target
// Awards moderation
$router->get('/admin/awards', [AdminAwardsController::class, 'index']);
$router->post('/admin/awards/categories', [AdminAwardsController::class, 'storeCategory']);
$router->get('/admin/awards/categories/{id}/edit', [AdminAwardsController::class, 'editCategory']);
$router->put('/admin/awards/categories/{id}', [AdminAwardsController::class, 'updateCategory']);
$router->post('/admin/awards/categories/{id}/toggle', [AdminAwardsController::class, 'toggleCategory']);
$router->post('/admin/awards/categories/{id}/delete', [AdminAwardsController::class, 'deleteCategory']);
$router->get('/admin/awards/nominations', [AdminAwardsController::class, 'nominations']);
$router->post('/admin/awards/nominations/{id}/status', [AdminAwardsController::class, 'moderate']);
// Sponsor review + provisioning
$router->get('/admin/sponsors', [AdminSponsorsController::class, 'index']);
$router->post('/admin/sponsors/assets/{id}/review', [AdminSponsorsController::class, 'reviewAsset']);
$router->get('/admin/sponsors/{id}', [AdminSponsorsController::class, 'show']);
$router->post('/admin/sponsors/{id}/status', [AdminSponsorsController::class, 'setStatus']);
$router->post('/admin/sponsors/{id}/confirm', [AdminSponsorsController::class, 'confirm']);
// Content — site text/branding editor
$router->get('/admin/content', [ContentController::class, 'index']);
$router->get('/admin/content/{section}', [ContentController::class, 'edit']);
$router->post('/admin/content/{section}', [ContentController::class, 'save']);
// Content — speakers CRUD
$router->get('/admin/speakers', [SpeakersController::class, 'index']);
$router->get('/admin/speakers/create', [SpeakersController::class, 'create']);
$router->post('/admin/speakers', [SpeakersController::class, 'store']);
$router->get('/admin/speakers/{id}/edit', [SpeakersController::class, 'edit']);
$router->put('/admin/speakers/{id}', [SpeakersController::class, 'update']);
$router->post('/admin/speakers/{id}/delete', [SpeakersController::class, 'destroy']);
// Content — gallery
$router->get('/admin/gallery', [GalleryController::class, 'index']);
$router->post('/admin/gallery', [GalleryController::class, 'store']);
$router->post('/admin/gallery/{id}', [GalleryController::class, 'update']);
$router->post('/admin/gallery/{id}/delete', [GalleryController::class, 'destroy']);
// Content — testimonials CRUD
$router->get('/admin/testimonials', [TestimonialsController::class, 'index']);
$router->get('/admin/testimonials/create', [TestimonialsController::class, 'create']);
$router->post('/admin/testimonials', [TestimonialsController::class, 'store']);
$router->get('/admin/testimonials/{id}/edit', [TestimonialsController::class, 'edit']);
$router->put('/admin/testimonials/{id}', [TestimonialsController::class, 'update']);
$router->post('/admin/testimonials/{id}/delete', [TestimonialsController::class, 'destroy']);
// Catalog — ticket types CRUD
$router->get('/admin/ticket-types', [TicketTypesController::class, 'index']);
$router->get('/admin/ticket-types/create', [TicketTypesController::class, 'create']);
$router->post('/admin/ticket-types', [TicketTypesController::class, 'store']);
$router->get('/admin/ticket-types/{id}/edit', [TicketTypesController::class, 'edit']);
$router->put('/admin/ticket-types/{id}', [TicketTypesController::class, 'update']);
$router->post('/admin/ticket-types/{id}/delete', [TicketTypesController::class, 'destroy']);
// Catalog — sponsorship packages CRUD
$router->get('/admin/packages', [PackagesController::class, 'index']);
$router->get('/admin/packages/create', [PackagesController::class, 'create']);
$router->post('/admin/packages', [PackagesController::class, 'store']);
$router->get('/admin/packages/{id}/edit', [PackagesController::class, 'edit']);
$router->put('/admin/packages/{id}', [PackagesController::class, 'update']);
$router->post('/admin/packages/{id}/delete', [PackagesController::class, 'destroy']);
// Reports + exports
$router->get('/admin/reports', [ReportsController::class, 'index']);
$router->get('/admin/reports/passes.csv', [ReportsController::class, 'exportPasses']);
// Settings
$router->get('/admin/settings', [SettingsController::class, 'edit']);
$router->post('/admin/settings', [SettingsController::class, 'update']);

/* ---- Health check ---- */
$router->get('/health', function () {
    json_response(['ok' => true, 'time' => date('c')]);
});

/*
 * Phase 4+ routes (awards, sponsor portal) get registered here as each phase
 * is implemented.
 */

return $router;
