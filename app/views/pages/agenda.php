<?php
/** @var \App\Core\View $this */
$this->layout('layouts/app');
$days = $days ?? [];
?>
<section class="section" style="padding-top:0">
  <div class="wrap" style="max-width:900px">
    <div class="page-head reveal in">
      <span class="eyebrow">Programme</span>
      <h1>Conference agenda.</h1>
      <p>The full running order across both days — keynotes, panels, workshops, the exhibition and the awards gala. Times are subject to change.</p>
    </div>

    <?php if (!$days): ?>
      <div class="card reveal" style="margin-top:40px;text-align:center">
        <p style="color:var(--sage)">The detailed programme will be published here shortly. Check back soon.</p>
      </div>
    <?php endif; ?>

    <?php foreach ($days as $label => $sessions): ?>
      <div class="reveal" style="margin-top:48px">
        <h2 class="serif" style="font-size:clamp(20px,3vw,26px);color:#fff;font-weight:500;padding-bottom:14px;border-bottom:1px solid var(--line)">
          <?= e($label) ?>
        </h2>

        <div style="display:flex;flex-direction:column;gap:2px;margin-top:18px">
          <?php foreach ($sessions as $s):
            $isBreak = !empty($s['is_break']);
            $time = trim(($s['start_time'] ?? '') . (!empty($s['end_time']) ? ' – ' . $s['end_time'] : ''));
          ?>
          <div style="display:grid;grid-template-columns:120px 1fr;gap:18px;padding:18px 16px;border-radius:8px;<?= $isBreak ? 'background:transparent' : 'background:var(--ink-2);border:1px solid var(--line)' ?>">
            <div class="mono" style="color:var(--gold-soft);font-size:13px;padding-top:2px;white-space:nowrap"><?= e($time) ?></div>
            <div>
              <?php if (!empty($s['track'])): ?>
                <span class="mono" style="display:inline-block;font-size:10px;letter-spacing:1px;text-transform:uppercase;color:var(--verdant);margin-bottom:6px"><?= e($s['track']) ?></span>
              <?php endif; ?>
              <div style="font-weight:<?= $isBreak ? '500' : '600' ?>;color:<?= $isBreak ? 'var(--sage)' : '#fff' ?>;font-size:<?= $isBreak ? '15px' : '17px' ?>"><?= e($s['title']) ?></div>
              <?php if (!empty($s['description'])): ?>
                <p style="color:var(--sage);margin-top:6px;font-size:14px;line-height:1.55"><?= e($s['description']) ?></p>
              <?php endif; ?>
              <?php if (!empty($s['speaker']) || !empty($s['location'])): ?>
                <div style="margin-top:10px;display:flex;gap:18px;flex-wrap:wrap;color:var(--sage);font-size:12.5px">
                  <?php if (!empty($s['speaker'])): ?><span>▸ <?= e($s['speaker']) ?></span><?php endif; ?>
                  <?php if (!empty($s['location'])): ?><span class="mono"><?= e($s['location']) ?></span><?php endif; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>

    <div class="reveal" style="margin-top:56px;text-align:center">
      <a href="<?= e(url('/register')) ?>" class="btn btn-gold">Register free <span class="arrow">→</span></a>
    </div>
  </div>
</section>
