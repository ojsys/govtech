<!-- NAV -->
<header class="nav" id="nav">
  <div class="wrap nav-inner">
    <a class="brand" href="<?= e(url('/')) ?>">
      <?php $logo = content_image('logo'); ?>
      <?php if ($logo !== ''): ?>
        <img class="brand-seal" src="<?= e($logo) ?>" alt="<?= e(content('brand_name')) ?>" style="object-fit:contain">
      <?php else: ?>
        <svg class="brand-seal" viewBox="0 0 100 100" fill="none">
          <circle cx="50" cy="50" r="46" stroke="#C9A227" stroke-width="1.5"/>
          <circle cx="50" cy="50" r="38" stroke="#0C7A4D" stroke-width="1"/>
          <path d="M50 20 L57 44 L82 44 L62 59 L70 83 L50 68 L30 83 L38 59 L18 44 L43 44 Z" fill="#C9A227"/>
        </svg>
      <?php endif; ?>
      <span class="brand-text"><b><?= e(content('brand_name')) ?></b><span><?= e(content('brand_sub')) ?></span></span>
    </a>
    <nav class="nav-links">
      <a href="<?= e(url('/about')) ?>">About</a>
      <a href="<?= e(url('/agenda')) ?>">Agenda</a>
      <a href="<?= e(url('/#speakers')) ?>">Speakers</a>
      <a href="<?= e(url('/awards')) ?>">Awards</a>
      <a href="<?= e(url('/sponsor')) ?>">Sponsor</a>
      <a href="<?= e(url('/contact')) ?>">Contact</a>
    </nav>
    <div class="nav-cta">
      <a href="<?= e(url('/register')) ?>" class="btn btn-ghost">Get Tickets</a>
      <a href="<?= e(url('/awards/nominate')) ?>" class="btn btn-gold">Nominate <span class="arrow">→</span></a>
      <button class="menu-toggle" id="menuToggle" aria-label="Open menu"><span></span><span></span><span></span></button>
    </div>
  </div>
</header>

<!-- DRAWER -->
<div class="drawer" id="drawer">
  <button class="drawer-close" id="drawerClose" aria-label="Close menu">×</button>
  <a href="<?= e(url('/about')) ?>">About</a>
  <a href="<?= e(url('/agenda')) ?>">Agenda</a>
  <a href="<?= e(url('/#speakers')) ?>">Speakers</a>
  <a href="<?= e(url('/awards')) ?>">Awards</a>
  <a href="<?= e(url('/sponsor')) ?>">Sponsor</a>
  <a href="<?= e(url('/contact')) ?>">Contact</a>
  <a href="<?= e(url('/register')) ?>" class="btn btn-gold">Get Tickets →</a>
</div>
