<?php
/** @var \App\Core\View $this */
$pageTitle = $pageTitle ?? null;
$title = $pageTitle
    ? $pageTitle . ' — Nigeria GovTech Conference & Awards'
    : 'Nigeria GovTech Conference & Awards';
$desc = $metaDescription
    ?? 'The premier gathering where government officials, innovators, policymakers and industry shape Nigeria\'s digital transformation — and honour those leading it.';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:type" content="website">
<?php $favicon = content_image('favicon'); ?>
<?php if ($favicon !== ''): ?><link rel="icon" href="<?= e($favicon) ?>"><?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;1,9..144,400&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body>

<?php include VIEW_PATH . '/partials/nav.php'; ?>

<?= $this->section('content') ?>

<?php include VIEW_PATH . '/partials/footer.php'; ?>

<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>
