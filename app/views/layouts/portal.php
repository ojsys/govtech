<?php
/** @var \App\Core\View $this */
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e(($pageTitle ?? 'Sponsor portal') . ' — Nigeria GovTech') ?></title>
<meta name="robots" content="noindex,nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body>
<header class="nav scrolled" style="position:sticky">
  <div class="wrap nav-inner">
    <a class="brand" href="<?= e(url('/portal')) ?>">
      <svg class="brand-seal" viewBox="0 0 100 100" fill="none"><circle cx="50" cy="50" r="46" stroke="#C9A227" stroke-width="1.5"/><circle cx="50" cy="50" r="38" stroke="#0C7A4D" stroke-width="1"/><path d="M50 20 L57 44 L82 44 L62 59 L70 83 L50 68 L30 83 L38 59 L18 44 L43 44 Z" fill="#C9A227"/></svg>
      <span class="brand-text"><b>Sponsor Portal</b><span>Nigeria GovTech · NG</span></span>
    </a>
    <div class="nav-cta">
      <a href="<?= e(url('/')) ?>" class="btn btn-ghost">Main site</a>
      <form method="post" action="<?= e(url('/portal/logout')) ?>" style="display:inline">
        <input type="hidden" name="_token" value="<?= e($csrf ?? '') ?>">
        <button class="btn btn-gold" type="submit">Sign out</button>
      </form>
    </div>
  </div>
</header>
<main style="padding-top:30px">
  <?= $this->section('content') ?>
</main>
<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>
