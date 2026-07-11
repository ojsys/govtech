<?php
/** @var \App\Core\View $this */
$this->layout('layouts/app');
?>
<section class="section" style="padding-top:0">
  <div class="wrap" style="max-width:860px">
    <div class="page-head reveal in">
      <span class="eyebrow">Live Tally</span>
      <h1>Award results, as they come in.</h1>
      <p>Verified votes only. Counts update as voters confirm their ballots by email. <a style="color:var(--gold-soft)" href="<?= e(url('/awards')) ?>">Cast your vote →</a></p>
    </div>

    <?php foreach ($categories as $c):
      $list = $nominees[(int) $c['id']] ?? [];
      $total = max(1, (int) ($totals[(int) $c['id']] ?? 0));
      $leadId = $list[0]['id'] ?? null; // list is ordered by votes desc
    ?>
    <div class="award-cat-block reveal">
      <div class="cat-h"><h3><?= e($c['title']) ?></h3><div class="desc"><?= (int) ($totals[(int) $c['id']] ?? 0) ?> total votes</div></div>
      <?php if (!$list): ?>
        <div class="empty-note">No shortlist yet.</div>
      <?php else: foreach ($list as $i => $n):
        $count = (int) $n['votes_count'];
        $pct = (int) round($count / $total * 100);
      ?>
        <div class="tally-row">
          <div class="tl"><b><?= e($n['nominee_name']) ?><?= !empty($n['nominee_org']) ? ' · <span style="color:var(--sage);font-weight:400">' . e($n['nominee_org']) . '</span>' : '' ?></b><span class="v"><?= $count ?> (<?= $pct ?>%)</span></div>
          <div class="tally-track"><div class="tally-fill <?= ($i === 0 && $count > 0) ? 'lead' : '' ?>" style="width:<?= max(2, $pct) ?>%"></div></div>
        </div>
      <?php endforeach; endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</section>
