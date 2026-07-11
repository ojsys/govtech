<?php
/** @var \App\Core\View $this */
$this->layout('layouts/app');
?>
<section class="section" style="padding-top:0">
  <div class="wrap">
    <div class="page-head reveal in">
      <span class="eyebrow">The Nigeria GovTech Awards</span>
      <h1>Vote for the trailblazers.</h1>
      <p>Cast your vote in each category for the individuals and organisations advancing public-sector technology. One vote per category — confirmed by email. <a style="color:var(--gold-soft)" href="<?= e(url('/awards/results')) ?>">See the live tally →</a></p>
    </div>

    <?php if ($m = flash('ok')): ?><div class="alert alert-ok" style="margin-top:24px"><?= e($m) ?></div><?php endif; ?>
    <?php if ($m = flash('error')): ?><div class="alert alert-error" style="margin-top:24px"><?= e($m) ?></div><?php endif; ?>

    <div style="margin:30px 0">
      <a href="<?= e(url('/awards/nominate')) ?>" class="btn btn-gold">Submit a nomination <span class="arrow">→</span></a>
    </div>

    <?php foreach ($categories as $c):
      $list = $nominees[(int) $c['id']] ?? [];
    ?>
    <div class="award-cat-block reveal">
      <div class="cat-h">
        <h3><?= e($c['title']) ?></h3>
        <div class="desc"><?= e($c['description'] ?? '') ?></div>
      </div>

      <?php if (!$list): ?>
        <div class="empty-note">Shortlist coming soon — nominations are being reviewed.</div>
      <?php else: ?>
        <form method="post" action="<?= e(url('/awards/vote')) ?>" class="vote-form">
          <input type="hidden" name="_token" value="<?= e($csrf) ?>">
          <div class="hp" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
          <div class="nominee-grid">
            <?php foreach ($list as $n): ?>
              <label class="nominee">
                <input type="radio" name="nomination_id" value="<?= (int) $n['id'] ?>" required>
                <span>
                  <span class="nn"><?= e($n['nominee_name']) ?></span>
                  <?php if (!empty($n['nominee_org'])): ?><span class="no"><?= e($n['nominee_org']) ?></span><?php endif; ?>
                  <span class="nv"><?= (int) $n['votes_count'] ?> vote<?= (int) $n['votes_count'] === 1 ? '' : 's' ?></span>
                </span>
              </label>
            <?php endforeach; ?>
          </div>
          <div class="vote-bar">
            <input type="email" name="email" placeholder="Your email (to confirm your vote)" autocomplete="email" required>
            <button class="btn btn-green" type="submit">Cast vote</button>
          </div>
        </form>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<script>
// Highlight the selected nominee card within each category.
document.querySelectorAll('.vote-form').forEach(function (form) {
  var radios = form.querySelectorAll('input[name="nomination_id"]');
  radios.forEach(function (r) {
    r.addEventListener('change', function () {
      form.querySelectorAll('.nominee').forEach(function (n) { n.classList.remove('sel'); });
      if (r.checked) r.closest('.nominee').classList.add('sel');
    });
  });
});
</script>
