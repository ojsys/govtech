<?php
/** @var \App\Core\View $this */
$this->layout('layouts/app');
?>
<section class="section sponsor" style="padding-top:0">
  <div class="wrap">
    <div class="page-head reveal in">
      <span class="eyebrow">Partnership &amp; Sponsorship</span>
      <h1>Align your brand with national digital transformation.</h1>
      <p>Put your organisation in the room with the decision-makers shaping Nigeria's public-sector technology agenda — across keynotes, exhibition, branding and the awards gala.</p>
    </div>

    <!-- Why sponsor -->
    <div class="obj-grid" style="margin-top:54px">
      <div class="obj-card reveal">
        <div class="no">01</div>
        <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/></svg>
        <h3>Reach the right room</h3>
        <p>Engage 1,500+ delegates — government officials, MDA leaders, innovators and industry — over two focused days.</p>
      </div>
      <div class="obj-card reveal d1">
        <div class="no">02</div>
        <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 2l3 7h7l-5.5 4 2 7L12 17l-6.5 3 2-7L2 9h7z"/></svg>
        <h3>Premium visibility</h3>
        <p>Brochure adverts, on-screen branding, exhibition placement and acknowledgement from the main stage.</p>
      </div>
      <div class="obj-card reveal d2">
        <div class="no">03</div>
        <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg>
        <h3>Complimentary delegate passes</h3>
        <p>Every sponsorship tier includes complimentary delegate passes for your team — issued automatically on confirmation.</p>
      </div>
    </div>

    <!-- Sponsorship tiers -->
    <div class="sec-head reveal" style="margin-top:80px"><span class="eyebrow">Sponsorship Tiers</span><h2>Choose your level of partnership.</h2></div>
    <div class="sp-tiers">
      <?php foreach ($sponsorTiers as $i => $pkg):
        $perks = json_col($pkg['perks_json'] ?? null);
        $isPlat = stripos($pkg['name'] ?? '', 'platinum') !== false;
        $comp = (int) ($pkg['comp_passes'] ?? 0);
      ?>
      <div class="sp <?= $isPlat ? 'platinum' : '' ?> reveal d<?= (int) ($i % 4) ?>">
        <div class="tier"><?= e($pkg['name']) ?></div>
        <div class="amt">₦<?= e(naira((int) $pkg['price_kobo'])) ?></div>
        <ul>
          <?php foreach ($perks as $perk): ?><li><?= e($perk) ?></li><?php endforeach; ?>
          <?php if ($comp > 0): ?><li><?= $comp ?> complimentary delegate pass<?= $comp === 1 ? '' : 'es' ?></li><?php endif; ?>
        </ul>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Exhibition -->
    <?php if (!empty($booths)): ?>
    <div class="sec-head reveal" style="margin-top:70px"><span class="eyebrow">Exhibition</span><h2>Showcase on the exhibition floor.</h2></div>
    <div class="exhibit">
      <?php foreach ($booths as $i => $booth): ?>
      <div class="booth reveal d<?= (int) ($i % 3) ?>"><span class="b1"><?= e($booth['name']) ?></span><span class="b2">₦<?= e(naira((int) $booth['price_kobo'])) ?></span></div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- CTA -->
    <div class="reveal" style="margin-top:60px;background:var(--ink-2);border:1px solid var(--line);border-radius:8px;padding:38px 34px;display:flex;align-items:center;justify-content:space-between;gap:24px;flex-wrap:wrap">
      <div>
        <h2 class="serif" style="font-size:clamp(22px,3vw,30px);color:#fff;font-weight:500">Ready to partner with us?</h2>
        <p style="color:var(--sage);margin-top:8px;max-width:520px">Tell us which tier fits your goals and our partnerships team will share the full prospectus and next steps.</p>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <a href="<?= e(url('/sponsor/apply')) ?>" class="btn btn-gold">Apply to sponsor <span class="arrow">→</span></a>
        <a href="<?= e(url('/contact')) ?>" class="btn btn-ghost">Talk to partnerships</a>
      </div>
    </div>
  </div>
</section>
