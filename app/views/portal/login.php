<?php /** Standalone — sponsor portal login. */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sponsor portal — Sign in</title>
<meta name="robots" content="noindex,nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600&family=Plus+Jakarta+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body>
<div style="min-height:100vh;display:grid;place-items:center;padding:24px;background:radial-gradient(ellipse 60% 50% at 50% 20%,rgba(12,122,77,.18),transparent 60%)">
  <div class="card" style="width:100%;max-width:400px">
    <div style="display:flex;justify-content:center;margin-bottom:14px">
      <svg width="44" height="44" viewBox="0 0 100 100" fill="none"><circle cx="50" cy="50" r="46" stroke="#C9A227" stroke-width="2"/><circle cx="50" cy="50" r="38" stroke="#0C7A4D" stroke-width="1.5"/><path d="M50 20 L57 44 L82 44 L62 59 L70 83 L50 68 L30 83 L38 59 L18 44 L43 44 Z" fill="#C9A227"/></svg>
    </div>
    <h3 style="text-align:center;font-size:22px">Sponsor Portal</h3>
    <p class="hint" style="text-align:center">Sign in with the credentials we emailed you.</p>

    <?php if (!empty($error)): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <form method="post" action="<?= e(url('/portal/login')) ?>">
      <input type="hidden" name="_token" value="<?= e($csrf) ?>">
      <div class="field"><label>Email</label><input type="email" name="email" autocomplete="username" required autofocus></div>
      <div class="field"><label>Password</label><input type="password" name="password" autocomplete="current-password" required></div>
      <button class="btn btn-gold btn-block" type="submit">Sign in</button>
    </form>
    <p style="text-align:center;margin-top:18px"><a href="<?= e(url('/sponsor')) ?>" style="color:var(--sage);font-size:13.5px">Not a sponsor yet? View packages →</a></p>
  </div>
</div>
</body>
</html>
