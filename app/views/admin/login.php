<?php /** Standalone — renders its own document, no admin layout. */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign in — GovTech Admin</title>
<meta name="robots" content="noindex,nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600&family=Plus+Jakarta+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body class="admin">
<div class="login-wrap">
  <div class="login-card">
    <div class="brand">
      <svg viewBox="0 0 100 100" fill="none"><circle cx="50" cy="50" r="46" stroke="#C9A227" stroke-width="2"/><circle cx="50" cy="50" r="38" stroke="#0C7A4D" stroke-width="1.5"/><path d="M50 20 L57 44 L82 44 L62 59 L70 83 L50 68 L30 83 L38 59 L18 44 L43 44 Z" fill="#C9A227"/></svg>
    </div>
    <h1>Admin Console</h1>
    <div class="sub">Nigeria GovTech Conference &amp; Awards</div>

    <?php if (!empty($error)): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <form method="post" action="<?= e(url('/admin/login')) ?>">
      <input type="hidden" name="_token" value="<?= e($csrf) ?>">
      <div class="field">
        <label>Email</label>
        <input type="email" name="email" autocomplete="username" required autofocus>
      </div>
      <div class="field">
        <label>Password</label>
        <input type="password" name="password" autocomplete="current-password" required>
      </div>
      <button class="btn btn-gold" style="width:100%;justify-content:center" type="submit">Sign in</button>
    </form>
  </div>
</div>
</body>
</html>
