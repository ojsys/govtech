<?php
/** @var \App\Core\View $this */

use App\Models\Speaker;

$this->layout('layouts/app');

// ---- Derived display values ------------------------------------------------
$ev = $event ?? null;
$theme = $ev['theme'] ?? 'Redefining Possibilities: Harnessing Emerging Technologies for Public Service Delivery.';
$venue = $ev['venue'] ?? 'Banquet Hall, Presidential Villa, Abuja';

// Hero date range, e.g. "Oct 7 – 8, 2026"
$heroDates = 'Oct 7 – 8, 2026';
if ($ev && !empty($ev['start_date'])) {
    $s = strtotime($ev['start_date']);
    $e = strtotime($ev['end_date'] ?? $ev['start_date']);
    $heroDates = date('M j', $s) . ' – ' . (date('M', $s) === date('M', $e) ? date('j', $e) : date('M j', $e)) . ', ' . date('Y', $e);
}
// Hero venue label — shown exactly as entered in admin.
$venueShort = $venue;
$countdownTarget = content('countdown_target', $settings['countdown_target'] ?? (($ev['start_date'] ?? '2026-10-07') . 'T09:00:00+01:00'));

$chk = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg>';
$wmk = '<svg class="wmk" viewBox="0 0 100 100" fill="none"><circle cx="50" cy="50" r="46" stroke="currentColor" stroke-width="1"/><path d="M50 26 L56 44 L75 44 L60 56 L66 74 L50 62 L34 74 L40 56 L25 44 L44 44Z" fill="currentColor"/></svg>';
?>

<!-- HERO -->
<section class="hero">
  <div class="hero-bg">
    <div class="guilloche" id="guilloche"></div>
    <div class="grain"></div>
  </div>
  <div class="wrap">
   <div class="hero-inner">
    <span class="eyebrow reveal in"><?= e(content('hero_eyebrow')) ?></span>
    <h1 class="reveal in d1"><?= e(content('hero_title_lead')) ?><br><em><?= e(content('hero_title_em')) ?></em> <?= e(content('hero_title_tail')) ?></h1>
    <p class="lede reveal in d2"><?= e(content('hero_lede')) ?></p>

    <div class="hero-meta reveal in d2">
      <div class="item"><div class="k">Dates</div><div class="v"><?= e($heroDates) ?></div></div>
      <div class="item"><div class="k">Venue</div><div class="v"><?= e($venueShort) ?></div></div>
      <div class="item"><div class="k">Format</div><div class="v"><?= e(content('hero_format')) ?></div></div>
    </div>

    <div class="hero-cta reveal in d3">
      <a href="<?= e(url('/register')) ?>" class="btn btn-gold"><?= e(content('hero_cta_primary')) ?> <span class="arrow">→</span></a>
      <a href="<?= e(url('/awards/nominate')) ?>" class="btn btn-ghost"><?= e(content('hero_cta_secondary')) ?></a>
    </div>

    <div class="countdown reveal in d4" id="countdown" data-target="<?= e($countdownTarget) ?>" aria-label="Countdown to event">
      <div class="cd-unit"><div class="cd-num" id="cd-d">00</div><div class="cd-lab">Days</div></div>
      <div class="cd-sep">:</div>
      <div class="cd-unit"><div class="cd-num" id="cd-h">00</div><div class="cd-lab">Hours</div></div>
      <div class="cd-sep">:</div>
      <div class="cd-unit"><div class="cd-num" id="cd-m">00</div><div class="cd-lab">Minutes</div></div>
      <div class="cd-sep">:</div>
      <div class="cd-unit"><div class="cd-num" id="cd-s">00</div><div class="cd-lab">Seconds</div></div>
    </div>
   </div>
  </div>
</section>

<!-- STATS -->
<section class="stats">
  <div class="wrap stats-grid">
    <?php foreach (['1', '2', '3', '4'] as $i => $n):
      $num = content("stat{$n}_num");
      $isNumeric = ctype_digit($num);
    ?>
    <div class="stat reveal<?= $i ? ' d' . $i : '' ?>">
      <div class="num"><span class="count" data-to="<?= e($isNumeric ? $num : '0') ?>"><?= e($isNumeric ? '0' : $num) ?></span><span class="suf"><?= e(content("stat{$n}_suffix")) ?></span></div>
      <div class="lab"><?= e(content("stat{$n}_label")) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ABOUT -->
<section class="section about" id="about">
  <div class="wrap">
    <div class="about-grid">
      <div class="reveal">
        <div class="sec-head light" style="margin-bottom:30px">
          <span class="eyebrow"><?= e(content('about_eyebrow')) ?></span>
          <h2><?= e(content('about_heading')) ?></h2>
        </div>
        <p class="body"><?= e(content('about_p1')) ?></p>
        <p class="body"><?= e(content('about_p2')) ?></p>
        <div class="organizer">
          <svg class="seal" viewBox="0 0 100 100" fill="none"><circle cx="50" cy="50" r="46" stroke="currentColor" stroke-width="2"/><circle cx="50" cy="50" r="34" stroke="currentColor" stroke-width="1" opacity=".5"/><path d="M50 30 L55 46 L72 46 L58 56 L63 72 L50 62 L37 72 L42 56 L28 46 L45 46Z" fill="currentColor"/></svg>
          <div><b><?= e(content('organizer_name')) ?></b><span><?= e(content('organizer_note')) ?></span></div>
        </div>
      </div>
      <div class="about-visual reveal d2">
        <?php $aboutImg = content_image('about_image') ?: 'https://govtechconference.ng/wp-content/uploads/2025/07/GovTech-2025-07-13-at-08.47.35-1-1024x683.jpeg'; ?>
        <img src="<?= e($aboutImg) ?>" alt="Conference hall" onerror="this.style.display='none'">
        <div class="tag">Banquet Hall · <b>Presidential Villa, Abuja</b></div>
      </div>
    </div>

    <div class="obj-grid">
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

<!-- SPEAKERS -->
<section class="section speakers" id="speakers">
  <div class="wrap">
    <div class="spk-head">
      <div class="sec-head reveal" style="margin-bottom:0">
        <span class="eyebrow">The Voices</span>
        <h2>Leaders shaping the agenda.</h2>
      </div>
      <a href="<?= e(url('/#speakers')) ?>" class="btn btn-ghost reveal d1">View all speakers <span class="arrow">→</span></a>
    </div>
    <div class="spk-grid" id="spkGrid">
      <?php foreach ($speakers as $i => $sp):
        $photo = Speaker::photoUrl($sp['photo'] ?? '');
        $roleLine = trim(($sp['role'] ?? '') . (!empty($sp['organization']) ? ', ' . $sp['organization'] : ''), ', ');
        $bio = trim((string) ($sp['bio'] ?? ''));
      ?>
      <div class="spk reveal d<?= (int) ($i % 4) ?>" role="button" tabindex="0"
           aria-label="View details for <?= e($sp['name']) ?>"
           data-name="<?= e($sp['name']) ?>"
           data-role="<?= e($roleLine) ?>"
           data-photo="<?= e($photo) ?>"
           data-initials="<?= e(Speaker::initials($sp['name'])) ?>"
           data-bio="<?= e($bio) ?>">
        <?php if ($photo !== ''): ?>
          <img class="ph" src="<?= e($photo) ?>" alt="<?= e($sp['name']) ?>" loading="lazy"
               onerror="this.outerHTML='<div class=&quot;fallback&quot;><?= e(Speaker::initials($sp['name'])) ?></div>'">
        <?php else: ?>
          <div class="fallback"><?= e(Speaker::initials($sp['name'])) ?></div>
        <?php endif; ?>
        <div class="ring"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#C9A227" stroke-width="2"><path d="M7 17L17 7M17 7H8M17 7v9"/></svg></div>
        <div class="ov"><h4><?= e($sp['name']) ?></h4><div class="role"><?= e($roleLine) ?></div></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Speaker detail modal -->
  <div class="spk-modal" id="spkModal" aria-hidden="true">
    <div class="spk-modal-backdrop" data-close></div>
    <div class="spk-modal-card" role="dialog" aria-modal="true" aria-labelledby="spkModalName">
      <button class="spk-modal-x" type="button" data-close aria-label="Close">&times;</button>
      <div class="spk-modal-media">
        <img id="spkModalImg" src="" alt="" hidden>
        <div class="spk-modal-fallback" id="spkModalFallback" hidden></div>
      </div>
      <div class="spk-modal-body">
        <h3 id="spkModalName"></h3>
        <div class="spk-modal-role" id="spkModalRole"></div>
        <p class="spk-modal-bio" id="spkModalBio"></p>
      </div>
    </div>
  </div>
</section>

<!-- AWARDS -->
<section class="section awards" id="awards">
  <div class="glow"></div>
  <div class="wrap awards-grid">
    <div class="medallion reveal">
      <svg viewBox="0 0 200 200" fill="none">
        <circle cx="100" cy="100" r="96" stroke="#C9A227" stroke-width="1" opacity=".7"/>
        <circle cx="100" cy="100" r="78" stroke="#C9A227" stroke-width=".6" opacity=".4"/>
        <g id="medRays"></g>
        <path d="M100 50 L110 84 L146 84 L117 105 L128 140 L100 119 L72 140 L83 105 L54 84 L90 84Z" fill="#C9A227" opacity=".9"/>
      </svg>
      <div class="core"><div class="yr"><?= e(content('awards_est')) ?></div><b><?= e(content('brand_name')) ?><br>Awards</b></div>
    </div>
    <div class="reveal d1">
      <span class="eyebrow"><?= e(content('awards_eyebrow')) ?></span>
      <h2 style="margin-top:16px"><?= e(content('awards_heading_lead')) ?> <em><?= e(content('awards_heading_em')) ?></em> <?= e(content('awards_heading_tail')) ?></h2>
      <p><?= e(content('awards_body')) ?></p>
      <div class="award-cats">
        <?php foreach ($categories as $cat): ?>
          <span><?= e($cat['title']) ?></span>
        <?php endforeach; ?>
      </div>
      <a href="<?= e(url('/awards/nominate')) ?>" class="btn btn-gold">Submit a Nomination <span class="arrow">→</span></a>
    </div>
  </div>
</section>

<!-- TICKETS -->
<section class="section tickets" id="tickets">
  <div class="wrap">
    <div class="sec-head light reveal">
      <span class="eyebrow">Registration &amp; Passes</span>
      <h2>Secure your place.</h2>
      <p>Choose a pass below. Admission is free — public-sector group and virtual access available.</p>
    </div>

    <div class="tk-grid" id="tkGrid">
      <?php foreach ($tickets as $i => $t):
        $perks = json_col($t['perks_json'] ?? null);
        $feat = !empty($t['featured']);
      ?>
      <div class="tk <?= $feat ? 'feat' : '' ?> reveal d<?= (int) ($i % 4) ?>"
           data-id="<?= (int) $t['id'] ?>" data-qty="0">
        <?= $wmk ?><?= $feat ? '<div class="badge">Most popular</div>' : '' ?>
        <div class="tname"><?= e($t['name']) ?></div>
        <div class="price">Free</div>
        <div class="per"><?= e($t['description'] ?? '') ?></div>
        <ul>
          <?php foreach ($perks as $perk): ?>
            <li><?= $chk ?><span><?= e($perk) ?></span></li>
          <?php endforeach; ?>
        </ul>
        <div class="stepper">
          <button type="button" data-op="-1" aria-label="Remove">−</button>
          <span class="qty">0</span>
          <button type="button" data-op="1" aria-label="Add">+</button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="order-bar" id="orderBar">
      <div class="sum">
        <div><div class="lab">Selected</div><div class="cnt" id="orderCnt">No passes selected</div></div>
        <div><div class="lab">Admission</div><div class="tot">Free</div></div>
      </div>
      <a href="#" class="btn btn-gold" id="checkoutBtn">Register free <span class="arrow">→</span></a>
    </div>
  </div>
</section>

<!-- SPONSORSHIP -->
<section class="section sponsor" id="sponsor">
  <div class="wrap">
    <div class="sec-head reveal">
      <span class="eyebrow">Partnership &amp; Sponsorship</span>
      <h2>Align your brand with national digital transformation.</h2>
    </div>
    <div class="sp-tiers">
      <?php foreach ($sponsorTiers as $i => $pkg):
        $perks = json_col($pkg['perks_json'] ?? null);
        $isPlat = stripos($pkg['name'] ?? '', 'platinum') !== false;
      ?>
      <div class="sp <?= $isPlat ? 'platinum' : '' ?> reveal d<?= (int) ($i % 4) ?>">
        <div class="tier"><?= e($pkg['name']) ?></div>
        <div class="amt">₦<?= e(naira((int) $pkg['price_kobo'])) ?></div>
        <ul>
          <?php foreach ($perks as $perk): ?>
            <li><?= e($perk) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endforeach; ?>
    </div>
    <?php if (!empty($booths)): ?>
    <div class="exhibit">
      <?php foreach ($booths as $i => $booth): ?>
      <div class="booth reveal d<?= (int) ($i % 3) ?>"><span class="b1"><?= e($booth['name']) ?></span><span class="b2">₦<?= e(naira((int) $booth['price_kobo'])) ?></span></div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div style="margin-top:36px" class="reveal">
      <a href="<?= e(url('/sponsor')) ?>" class="btn btn-gold">Become a Sponsor <span class="arrow">→</span></a>
    </div>
  </div>
</section>

<!-- GALLERY -->
<section class="section gallery">
  <div class="wrap">
    <div class="sec-head reveal">
      <span class="eyebrow">Proceedings</span>
      <h2>Moments from the floor.</h2>
    </div>
    <?php
    $galLayout = ['wide tall', '', '', 'wide', ''];
    $galImages = [];
    if (!empty($gallery)) {
        foreach ($gallery as $g) {
            $galImages[] = ['img' => $g['image'], 'cap' => $g['caption'] ?? '', 'ed' => trim((string) ($g['edition'] ?? ''))];
        }
    } else {
        // Fallback to the approved prototype imagery until gallery rows are added in admin.
        $base = 'https://govtechconference.ng/wp-content/uploads/2025/07/';
        foreach ([
            'GovTech-2025-07-13-at-08.48.10-3.jpeg',
            'GovTech-2025-07-13-at-08.48.11-3.jpeg',
            'GovTech-2025-07-13-at-08.48.10-1.jpeg',
            'GovTech-2025-07-13-at-08.48.11-2.jpeg',
            'GovTech-2025-07-13-at-08.47.35-1-1024x683.jpeg',
        ] as $f) {
            $galImages[] = ['img' => $base . $f, 'cap' => '', 'ed' => ''];
        }
    }
    $editions = $galEditions ?? [];
    ?>
    <?php if (count($editions) > 1): ?>
    <div class="gal-tabs reveal" role="tablist" aria-label="Filter gallery by edition">
      <button class="gal-tab active" type="button" data-ed="*">All</button>
      <?php foreach ($editions as $ed): ?>
        <button class="gal-tab" type="button" data-ed="<?= e($ed) ?>"><?= e($ed) ?></button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div class="gal-grid" id="galGrid">
      <?php foreach ($galImages as $i => $g):
          $cls = $galLayout[$i % count($galLayout)];
          $src = preg_match('#^https?://#', $g['img']) ? $g['img'] : (rtrim((string) \Config::get('app.uploads_url', '/uploads'), '/') . '/' . ltrim($g['img'], '/'));
      ?>
      <div class="gal <?= e($cls) ?> reveal d<?= (int) ($i % 3) ?>" data-edition="<?= e($g['ed']) ?>"><img src="<?= e($src) ?>" alt="<?= e($g['cap']) ?>" loading="lazy" onerror="this.parentNode.style.background='linear-gradient(160deg,#102B20,#0B1E16)'"></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- TESTIMONIALS -->
<section class="section testi">
  <div class="wrap">
    <div class="sec-head reveal"><span class="eyebrow">What participants say</span><h2>Trusted by the people in the room.</h2></div>
    <div class="testi-grid">
      <?php
      $quotes = !empty($testimonials) ? $testimonials : [
          ['quote' => 'Attending the conference will go a long way in improving our remarkable strides in leveraging technology to enhance public service delivery.', 'name' => 'Ogbeide Ifaluyi-Isibor', 'role' => 'Commissioner for Digital Economy, Edo State'],
          ['quote' => 'A great event showcasing and recognizing best practices in digital transformation across the public sector.', 'name' => 'Olakunle Osobu', 'role' => 'Chief Technology Officer, NNPC'],
          ['quote' => 'The awards ceremony was a highlight, recognizing the outstanding pursuit of excellence in ICT-driven governance.', 'name' => 'Dr. Bashir Jamoh', 'role' => 'Former DG, NIMASA'],
      ];
      foreach ($quotes as $i => $q):
      ?>
      <div class="quote reveal d<?= (int) ($i % 3) ?>"><div class="mark">"</div><p><?= e($q['quote']) ?></p><div class="who"><b><?= e($q['name']) ?></b><span><?= e($q['role']) ?></span></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
