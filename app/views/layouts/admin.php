<?php
/** @var \App\Core\View $this */
use App\Core\Auth;
$cur = parse_url($_SERVER['REQUEST_URI'] ?? '/admin', PHP_URL_PATH) ?: '/admin';
$active = function (string $prefix) use ($cur): string {
    if ($prefix === '/admin') {
        return $cur === '/admin' ? 'active' : '';
    }
    return str_starts_with($cur, $prefix) ? 'active' : '';
};
$role = Auth::role();
$can = fn(array $roles) => $role === 'superadmin' || in_array($role, $roles, true);
$ic = fn(string $p) => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">' . $p . '</svg>';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e(($pageTitle ?? 'Admin') . ' — GovTech Admin') ?></title>
<meta name="robots" content="noindex,nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body class="admin">
<div class="admin-shell">
  <aside class="side">
    <a class="brand" href="<?= e(url('/admin')) ?>">
      <svg viewBox="0 0 100 100" fill="none"><circle cx="50" cy="50" r="46" stroke="#C9A227" stroke-width="2"/><circle cx="50" cy="50" r="38" stroke="#0C7A4D" stroke-width="1.5"/><path d="M50 20 L57 44 L82 44 L62 59 L70 83 L50 68 L30 83 L38 59 L18 44 L43 44 Z" fill="#C9A227"/></svg>
      <span><b>GovTech</b><span>Admin Console</span></span>
    </a>
    <nav>
      <a class="<?= $active('/admin') ?>" href="<?= e(url('/admin')) ?>"><?= $ic('<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>') ?> Dashboard</a>

      <?php if ($can(['finance', 'editor'])): ?>
        <div class="seg">Operations</div>
        <a class="<?= $active('/admin/orders') ?>" href="<?= e(url('/admin/orders')) ?>"><?= $ic('<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18M16 10a4 4 0 0 1-8 0"/>') ?> Orders</a>
      <?php endif; ?>
      <?php if ($can(['checkin', 'editor', 'finance'])): ?>
        <a class="<?= $active('/admin/checkin') ?>" href="<?= e(url('/admin/checkin')) ?>"><?= $ic('<path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2M7 12h10"/>') ?> Check-in</a>
      <?php endif; ?>
      <?php if ($can(['finance', 'editor'])): ?>
        <a class="<?= $active('/admin/sponsors') ?>" href="<?= e(url('/admin/sponsors')) ?>"><?= $ic('<path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/><path d="M9 9h.01M9 13h.01M9 17h.01"/>') ?> Sponsors</a>
        <a class="<?= $active('/admin/reports') ?>" href="<?= e(url('/admin/reports')) ?>"><?= $ic('<path d="M3 3v18h18"/><path d="M7 14l3-3 3 3 5-5"/>') ?> Reports</a>
      <?php endif; ?>

      <?php if ($can(['editor'])): ?>
        <a class="<?= $active('/admin/awards') ?>" href="<?= e(url('/admin/awards')) ?>"><?= $ic('<circle cx="12" cy="8" r="6"/><path d="M8.21 13.89 7 23l5-3 5 3-1.21-9.12"/>') ?> Awards</a>
        <div class="seg">Content</div>
        <a class="<?= $cur === '/admin/content/event' ? 'active' : '' ?>" href="<?= e(url('/admin/content/event')) ?>"><?= $ic('<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01"/>') ?> Event date &amp; venue</a>
        <a class="<?= $cur === '/admin/content' || ($active('/admin/content') && $cur !== '/admin/content/event') ? 'active' : '' ?>" href="<?= e(url('/admin/content')) ?>"><?= $ic('<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4z"/>') ?> Site content</a>
        <a class="<?= $active('/admin/agenda') ?>" href="<?= e(url('/admin/agenda')) ?>"><?= $ic('<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/><path d="M8 15h3M8 18h5"/>') ?> Agenda</a>
        <a class="<?= $active('/admin/speakers') ?>" href="<?= e(url('/admin/speakers')) ?>"><?= $ic('<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13A4 4 0 0 1 16 11"/>') ?> Speakers</a>
        <a class="<?= $active('/admin/gallery') ?>" href="<?= e(url('/admin/gallery')) ?>"><?= $ic('<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-5-5L5 21"/>') ?> Gallery</a>
        <a class="<?= $active('/admin/testimonials') ?>" href="<?= e(url('/admin/testimonials')) ?>"><?= $ic('<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>') ?> Testimonials</a>
      <?php endif; ?>
      <?php if ($can(['editor', 'finance'])): ?>
        <div class="seg">Catalog</div>
        <a class="<?= $active('/admin/ticket-types') ?>" href="<?= e(url('/admin/ticket-types')) ?>"><?= $ic('<path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/><path d="M13 5v14"/>') ?> Ticket types</a>
        <a class="<?= $active('/admin/packages') ?>" href="<?= e(url('/admin/packages')) ?>"><?= $ic('<path d="m7.5 4.27 9 5.15M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="m3.3 7 8.7 5 8.7-5M12 22V12"/>') ?> Packages</a>
      <?php endif; ?>
    </nav>
  </aside>

  <div class="main">
    <div class="topbar">
      <h1><?= e($pageTitle ?? 'Admin') ?></h1>
      <div class="who">
        <a href="<?= e(url('/admin/account')) ?>" title="My account &amp; password"><?= e($authUser['name'] ?? '') ?></a>
        <span class="role"><?= e($authRole ?? '') ?></span>
        <form method="post" action="<?= e(url('/admin/logout')) ?>" style="display:inline">
          <input type="hidden" name="_token" value="<?= e($csrf) ?>">
          <button class="btn btn-ghost btn-sm" type="submit">Sign out</button>
        </form>
      </div>
    </div>
    <div class="content">
      <?php if ($m = flash('ok')): ?><div class="alert alert-ok"><?= e($m) ?></div><?php endif; ?>
      <?php if ($m = flash('error')): ?><div class="alert alert-error"><?= e($m) ?></div><?php endif; ?>
      <?= $this->section('content') ?>
    </div>
  </div>
</div>

<!-- Ticket preview modal -->
<div class="modal" id="ticketModal" aria-hidden="true">
  <div class="modal-backdrop" data-close></div>
  <div class="modal-card">
    <div class="modal-head">
      <h3 id="modalTitle">Ticket preview</h3>
      <button class="modal-x" type="button" data-close aria-label="Close">&times;</button>
    </div>
    <div class="modal-body" id="modalBody"></div>
  </div>
</div>

<script src="<?= e(asset('js/admin.js')) ?>" defer></script>
</body>
</html>
