<?php $this->layout('layouts/app'); $pageTitle = 'Page not found'; ?>
<section class="section" style="min-height:60vh;display:grid;place-items:center;text-align:center">
  <div class="wrap reveal in">
    <span class="eyebrow" style="justify-content:center;display:inline-flex">Error 404</span>
    <h1 class="serif" style="font-size:clamp(40px,8vw,90px);color:#fff;margin:14px 0 10px;font-weight:500">Page not found.</h1>
    <p style="color:var(--sage);max-width:480px;margin:0 auto 30px">The page you were looking for has moved or no longer exists.</p>
    <a href="<?= e(url('/')) ?>" class="btn btn-gold">Back to home <span class="arrow">→</span></a>
  </div>
</section>
