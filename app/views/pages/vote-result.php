<?php
/** @var \App\Core\View $this */
$this->layout('layouts/app');
?>
<section class="section" style="min-height:60vh;display:grid;place-items:center;text-align:center;padding-top:120px">
  <div class="wrap reveal in" style="max-width:560px">
    <span class="eyebrow" style="justify-content:center;display:inline-flex"><?= $ok ? 'Thank you' : 'Notice' ?></span>
    <h1 class="serif" style="font-size:clamp(32px,5vw,52px);color:#fff;margin:14px 0 12px;font-weight:500"><?= e($title) ?></h1>
    <p style="color:var(--sage);margin-bottom:30px"><?= e($body) ?></p>
    <a href="<?= e(url('/awards/results')) ?>" class="btn btn-gold">View live results <span class="arrow">→</span></a>
    <a href="<?= e(url('/awards')) ?>" class="btn btn-ghost" style="margin-left:10px">Back to awards</a>
  </div>
</section>
