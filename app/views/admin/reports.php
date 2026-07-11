<?php
/** @var \App\Core\View $this */
$this->layout('layouts/admin');
$checkinRate = $ticketStats['issued'] > 0 ? round($ticketStats['checked_in'] / $ticketStats['issued'] * 100) : 0;
?>
<div class="stat-grid">
  <div class="scard"><div class="k">Revenue (paid)</div><div class="v"><span class="cur">₦</span><?= e(naira((int) $orderStats['revenue_kobo'])) ?></div></div>
  <div class="scard"><div class="k">Paid orders</div><div class="v"><?= (int) $orderStats['orders_paid'] ?></div></div>
  <div class="scard"><div class="k">Passes issued</div><div class="v"><?= (int) $ticketStats['issued'] ?></div></div>
  <div class="scard"><div class="k">Checked in</div><div class="v"><?= (int) $ticketStats['checked_in'] ?> <span style="font-size:14px;color:var(--sage)"><?= $checkinRate ?>%</span></div></div>
  <div class="scard"><div class="k">Votes verified</div><div class="v"><?= (int) $voteStats['verified'] ?></div></div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:22px" class="rep-cols">
  <div class="panel">
    <div class="ph"><h2>Revenue by pass</h2><a class="btn btn-green btn-sm" href="<?= e(url('/admin/reports/passes.csv')) ?>">Export passes CSV</a></div>
    <div class="pb" style="padding-top:0">
      <table class="table">
        <thead><tr><th>Pass</th><th>Qty</th><th>Revenue</th></tr></thead>
        <tbody>
        <?php if (!$revenue): ?><tr><td colspan="3" class="muted" style="padding:18px 12px">No paid sales yet.</td></tr><?php endif; ?>
        <?php foreach ($revenue as $r): ?>
          <tr><td><?= e($r['name']) ?></td><td class="num"><?= (int) $r['qty'] ?></td><td class="num">₦<?= e(naira((int) $r['revenue_kobo'])) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="panel">
    <div class="ph"><h2>Registrations by sector</h2></div>
    <div class="pb" style="padding-top:0">
      <table class="table">
        <thead><tr><th>Sector</th><th>Delegates</th></tr></thead>
        <tbody>
        <?php if (!$bySector): ?><tr><td colspan="2" class="muted" style="padding:18px 12px">No data yet.</td></tr><?php endif; ?>
        <?php foreach ($bySector as $r): ?>
          <tr><td style="text-transform:capitalize"><?= e($r['sector']) ?></td><td class="num"><?= (int) $r['c'] ?></td></tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="panel">
    <div class="ph"><h2>Check-in by pass type</h2></div>
    <div class="pb" style="padding-top:0">
      <table class="table">
        <thead><tr><th>Pass</th><th>Issued</th><th>In</th></tr></thead>
        <tbody>
        <?php if (!$checkin): ?><tr><td colspan="3" class="muted" style="padding:18px 12px">No passes issued.</td></tr><?php endif; ?>
        <?php foreach ($checkin as $r): ?>
          <tr><td><?= e($r['name']) ?></td><td class="num"><?= (int) $r['issued'] ?></td><td class="num"><?= (int) $r['checked_in'] ?></td></tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="panel">
    <div class="ph"><h2>Votes by category</h2></div>
    <div class="pb" style="padding-top:0">
      <table class="table">
        <thead><tr><th>Category</th><th>Shortlisted</th><th>Votes</th></tr></thead>
        <tbody>
        <?php if (!$votes): ?><tr><td colspan="3" class="muted" style="padding:18px 12px">No categories.</td></tr><?php endif; ?>
        <?php foreach ($votes as $r): ?>
          <tr><td><?= e($r['title']) ?></td><td class="num"><?= (int) $r['shortlisted'] ?></td><td class="num"><?= (int) $r['votes'] ?></td></tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="panel">
  <div class="ph"><h2>Sponsor pipeline</h2></div>
  <div class="pb">
    <div style="display:flex;gap:24px;flex-wrap:wrap">
      <?php foreach ($sponsors['byStatus'] as $s): ?>
        <div><div class="k" style="font-family:var(--mono);font-size:10.5px;letter-spacing:.1em;text-transform:uppercase;color:var(--sage)"><?= e($s['status']) ?></div><div style="font-family:var(--display);font-size:26px;color:#fff"><?= (int) $s['c'] ?></div></div>
      <?php endforeach; ?>
      <div><div class="k" style="font-family:var(--mono);font-size:10.5px;letter-spacing:.1em;text-transform:uppercase;color:var(--sage)">Comp passes</div><div style="font-family:var(--display);font-size:26px;color:var(--gold-soft)"><?= (int) $sponsors['comp_passes'] ?></div></div>
    </div>
  </div>
</div>

<style>@media(max-width:840px){.rep-cols{grid-template-columns:1fr!important}}</style>
