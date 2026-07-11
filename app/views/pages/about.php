<?php
/** @var \App\Core\View $this */
$this->layout('layouts/app');
$s = $settings ?? [];
// Build the image set: DB gallery, else the approved prototype imagery.
$images = [];
if (!empty($gallery)) {
    foreach ($gallery as $g) {
        $images[] = ['img' => $g['image'], 'cap' => $g['caption'] ?? ''];
    }
} else {
    $base = 'https://govtechconference.ng/wp-content/uploads/2025/07/';
    foreach ([
        'GovTech-2025-07-13-at-08.48.10-3.jpeg', 'GovTech-2025-07-13-at-08.48.11-3.jpeg',
        'GovTech-2025-07-13-at-08.48.10-1.jpeg', 'GovTech-2025-07-13-at-08.48.11-2.jpeg',
        'GovTech-2025-07-13-at-08.47.35-1-1024x683.jpeg',
    ] as $f) {
        $images[] = ['img' => $base . $f, 'cap' => ''];
    }
}
$galLayout = ['wide tall', '', '', 'wide', ''];
?>
<section class="section" style="padding-top:0">
  <div class="wrap">
    <div class="page-head reveal in">
      <span class="eyebrow"><?= e(content('about_eyebrow')) ?></span>
      <h1><?= e(content('about_heading')) ?></h1>
      <p>Where policy meets practice, and the people building Nigeria's digital future come together.</p>
    </div>

    <div class="about-grid reveal" style="margin-top:54px">
      <div>
        <p class="body" style="color:#C2D2C8;font-size:16.5px;margin-bottom:20px"><?= e(content('about_p1')) ?></p>
        <p class="body" style="color:#C2D2C8;font-size:16.5px;margin-bottom:20px"><?= e(content('about_p2')) ?></p>
        <div class="organizer" style="border-top:1px solid var(--line)">
          <svg class="seal" viewBox="0 0 100 100" fill="none" style="color:var(--green)"><circle cx="50" cy="50" r="46" stroke="currentColor" stroke-width="2"/><circle cx="50" cy="50" r="34" stroke="currentColor" stroke-width="1" opacity=".5"/><path d="M50 30 L55 46 L72 46 L58 56 L63 72 L50 62 L37 72 L42 56 L28 46 L45 46Z" fill="currentColor"/></svg>
          <div><b style="color:#fff"><?= e(content('organizer_name')) ?></b><span style="color:var(--sage)"><?= e(content('organizer_note')) ?></span></div>
        </div>
      </div>
      <div class="about-visual reveal d2">
        <img src="https://govtechconference.ng/wp-content/uploads/2025/07/GovTech-2025-07-13-at-08.47.35-1-1024x683.jpeg" alt="Conference hall" onerror="this.style.display='none'">
        <div class="tag">Banquet Hall · <b>Presidential Villa, Abuja</b></div>
      </div>
    </div>

    <!-- Objectives -->
    <div class="obj-grid" style="margin-top:70px">
      <div class="obj-card reveal">
        <div class="no">01</div>
        <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/></svg>
        <h3><?= e(content('obj1_title')) ?></h3>
        <p><?= e(content('obj1_desc')) ?></p>
      </div>
      <div class="obj-card reveal d1">
        <div class="no">02</div>
        <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="3"/><path d="M12 2v4M12 18v4M2 12h4M18 12h4M5 5l3 3M16 16l3 3M19 5l-3 3M8 16l-3 3"/></svg>
        <h3><?= e(content('obj2_title')) ?></h3>
        <p><?= e(content('obj2_desc')) ?></p>
      </div>
      <div class="obj-card reveal d2">
        <div class="no">03</div>
        <svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
        <h3><?= e(content('obj3_title')) ?></h3>
        <p><?= e(content('obj3_desc')) ?></p>
      </div>
    </div>
  </div>
</section>

<!-- Image section -->
<section class="section gallery" style="padding-top:0">
  <div class="wrap">
    <div class="sec-head reveal"><span class="eyebrow">In pictures</span><h2>Moments from the floor.</h2></div>
    <div class="gal-grid">
      <?php foreach ($images as $i => $g):
        $cls = $galLayout[$i % count($galLayout)];
        $src = preg_match('#^https?://#', $g['img']) ? $g['img'] : (rtrim((string) \Config::get('app.uploads_url', '/uploads'), '/') . '/' . ltrim($g['img'], '/'));
      ?>
      <div class="gal <?= e($cls) ?> reveal d<?= (int) ($i % 3) ?>"><img src="<?= e($src) ?>" alt="<?= e($g['cap']) ?>" loading="lazy" onerror="this.parentNode.style.background='linear-gradient(160deg,#102B20,#0B1E16)'"></div>
      <?php endforeach; ?>
    </div>
    <div style="margin-top:40px" class="reveal">
      <a href="<?= e(url('/register')) ?>" class="btn btn-gold">Register to attend <span class="arrow">→</span></a>
      <a href="<?= e(url('/#speakers')) ?>" class="btn btn-ghost" style="margin-left:10px">Meet the speakers</a>
    </div>
  </div>
</section>
