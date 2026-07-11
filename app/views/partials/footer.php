<?php
$venueLine = isset($event) && $event
    ? strtoupper(($event['venue'] ?? 'Presidential Villa, Abuja'))
    : 'PRESIDENTIAL VILLA · ABUJA';
$dateLine = isset($event) && $event && !empty($event['start_date'])
    ? strtoupper(date('d', strtotime($event['start_date'])) . '–' . date('d M Y', strtotime($event['end_date'] ?? $event['start_date'])))
    : '07–08 OCT 2026';
$year = date('Y');
?>
<!-- CTA STRIP -->
<section class="section cta-strip">
  <svg class="g" viewBox="0 0 200 200" fill="none"><circle cx="100" cy="100" r="90" stroke="currentColor" stroke-width="1"/><circle cx="100" cy="100" r="70" stroke="currentColor" stroke-width="1"/><circle cx="100" cy="100" r="50" stroke="currentColor" stroke-width="1"/></svg>
  <div class="wrap cta-inner">
    <div class="reveal">
      <h2>Stay close to the agenda.</h2>
      <p>Programme updates, speaker announcements and registration windows — straight to your inbox.</p>
    </div>
    <div class="reveal d1">
      <form class="subform" method="post" action="<?= e(url('/newsletter/subscribe')) ?>">
        <input type="hidden" name="_token" value="<?= e($csrf ?? '') ?>">
        <div class="hp" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
        <input type="text" name="name" placeholder="Full name" aria-label="Full name" autocomplete="name">
        <input type="email" name="email" placeholder="Email address" aria-label="Email" autocomplete="email" required>
        <button class="btn btn-gold" type="submit">Subscribe</button>
      </form>
      <?php if ($nl = flash('newsletter')): ?>
        <p style="color:var(--gold-soft);font-size:13.5px;margin-top:12px"><?= e($nl) ?></p>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="wrap">
    <div class="flag-stripe"><i></i><i></i><i></i></div>
    <div class="foot-grid">
      <div class="foot-brand">
        <a class="brand" href="<?= e(url('/')) ?>">
          <?php $flogo = content_image('logo'); ?>
          <?php if ($flogo !== ''): ?>
            <img class="brand-seal" src="<?= e($flogo) ?>" alt="<?= e(content('brand_name')) ?>" style="object-fit:contain">
          <?php else: ?>
            <svg class="brand-seal" viewBox="0 0 100 100" fill="none"><circle cx="50" cy="50" r="46" stroke="#C9A227" stroke-width="1.5"/><circle cx="50" cy="50" r="38" stroke="#0C7A4D" stroke-width="1"/><path d="M50 20 L57 44 L82 44 L62 59 L70 83 L50 68 L30 83 L38 59 L18 44 L43 44 Z" fill="#C9A227"/></svg>
          <?php endif; ?>
          <span class="brand-text"><b><?= e(content('brand_name')) ?></b><span><?= e(content('brand_sub')) ?></span></span>
        </a>
        <p><?= e(content('footer_tagline')) ?></p>
      </div>
      <div class="foot-col"><h5>Event</h5><a href="<?= e(url('/about')) ?>">About</a><a href="<?= e(url('/#speakers')) ?>">Speakers</a><a href="<?= e(url('/awards')) ?>">Awards</a><a href="<?= e(url('/register')) ?>">Register</a></div>
      <div class="foot-col"><h5>Participate</h5><a href="<?= e(url('/register')) ?>">Register</a><a href="<?= e(url('/awards/nominate')) ?>">Nominate</a><a href="<?= e(url('/sponsor')) ?>">Sponsor</a><a href="<?= e(url('/sponsor')) ?>">Exhibit</a></div>
      <div class="foot-col"><h5>Connect</h5><a href="<?= e(url('/contact')) ?>">Contact</a><a href="<?= e(content('social_linkedin', '#')) ?>"<?= content('social_linkedin', '#') !== '#' ? ' target="_blank" rel="noopener"' : '' ?>>LinkedIn</a><a href="<?= e(content('social_twitter', '#')) ?>"<?= content('social_twitter', '#') !== '#' ? ' target="_blank" rel="noopener"' : '' ?>>X / Twitter</a><a href="<?= e(url('/contact')) ?>">Press</a></div>
    </div>
    <div class="foot-bot">
      <span>© <?= e($year) ?> Nigeria GovTech Conference &amp; Awards. All rights reserved.</span>
      <span class="mono"><?= e($venueLine) ?> · <?= e($dateLine) ?></span>
    </div>
  </div>
</footer>
