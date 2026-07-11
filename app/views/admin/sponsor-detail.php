<?php
/** @var \App\Core\View $this */
$this->layout('layouts/admin');
$logoUrl = !empty($app['logo_path']) ? rtrim((string) \Config::get('app.uploads_url', '/uploads'), '/') . '/' . rawurlencode($app['logo_path']) : '';
?>
<div style="margin-bottom:18px"><a class="btn btn-ghost btn-sm" href="<?= e(url('/admin/sponsors')) ?>">← All sponsors</a></div>

<div class="stat-grid" style="grid-template-columns:repeat(4,1fr)">
  <div class="scard"><div class="k">Company</div><div class="v" style="font-size:18px"><?= e($app['company_name']) ?></div></div>
  <div class="scard"><div class="k">Package</div><div class="v" style="font-size:18px"><?= e($app['package_name']) ?></div></div>
  <div class="scard"><div class="k">Comp passes</div><div class="v"><?= (int) $app['comp_passes'] ?></div></div>
  <div class="scard"><div class="k">Status</div><div class="v" style="font-size:18px"><span class="tag <?= in_array($app['status'], ['confirmed','paid'], true) ? 'paid' : 'pending' ?>"><?= e($app['status']) ?></span></div></div>
</div>

<div class="panel">
  <div class="ph"><h2>Application</h2></div>
  <div class="pb">
    <table class="table">
      <tr><th style="width:170px">Contact</th><td><?= e($app['contact_name']) ?></td></tr>
      <tr><th>Email</th><td><?= e($app['email']) ?></td></tr>
      <tr><th>Phone</th><td><?= e($app['phone']) ?></td></tr>
      <tr><th>Package value</th><td>₦<?= e(naira((int) $app['price_kobo'])) ?></td></tr>
      <tr><th>Message</th><td><?= nl2br(e($app['message'] ?? '')) ?: '—' ?></td></tr>
      <?php if ($logoUrl): ?><tr><th>Logo</th><td><img src="<?= e($logoUrl) ?>" alt="" style="max-height:60px;background:#fff;padding:6px;border-radius:4px"></td></tr><?php endif; ?>
    </table>
  </div>
</div>

<div class="panel">
  <div class="ph"><h2>Workflow</h2></div>
  <div class="pb">
    <?php if (!$account): ?>
      <p class="muted" style="color:var(--sage);margin-bottom:14px">Confirming creates the sponsor's portal login and issues their <?= (int) $app['comp_passes'] ?> complimentary delegate pass<?= (int) $app['comp_passes'] === 1 ? '' : 'es' ?> automatically. A welcome email with credentials is sent.</p>
      <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
        <?php foreach (['contacted' => 'Mark contacted', 'invoiced' => 'Mark invoiced', 'paid' => 'Mark paid'] as $st => $lbl): ?>
          <form method="post" action="<?= e(url('/admin/sponsors/' . (int) $app['id'] . '/status')) ?>" style="display:inline">
            <input type="hidden" name="_token" value="<?= e($csrf) ?>"><input type="hidden" name="status" value="<?= e($st) ?>">
            <button class="btn btn-ghost btn-sm" type="submit"><?= e($lbl) ?></button>
          </form>
        <?php endforeach; ?>
        <form method="post" action="<?= e(url('/admin/sponsors/' . (int) $app['id'] . '/confirm')) ?>" style="display:inline" onsubmit="return confirm('Confirm this sponsor? This creates their portal login and issues comp passes.')">
          <input type="hidden" name="_token" value="<?= e($csrf) ?>">
          <button class="btn btn-gold btn-sm" type="submit">Confirm &amp; provision →</button>
        </form>
      </div>
    <?php else: ?>
      <div class="alert alert-ok" style="margin-bottom:14px">Provisioned — portal login active for <?= e($account['email']) ?>.</div>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <form method="post" action="<?= e(url('/admin/sponsors/' . (int) $app['id'] . '/status')) ?>" style="display:inline">
          <input type="hidden" name="_token" value="<?= e($csrf) ?>"><input type="hidden" name="status" value="paid">
          <button class="btn btn-ghost btn-sm" type="submit">Mark paid</button>
        </form>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php if ($compPasses): ?>
<div class="panel">
  <div class="ph"><h2><?= count($compPasses) ?> complimentary pass<?= count($compPasses) === 1 ? '' : 'es' ?></h2></div>
  <div class="pb" style="padding-top:0">
    <table class="table">
      <thead><tr><th>Code</th><th>Holder</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($compPasses as $t): ?>
        <tr><td class="num"><?= e($t['ticket_code']) ?></td><td><?= e($t['holder_name']) ?></td><td><span class="tag <?= e($t['status']) ?>"><?= e(str_replace('_', ' ', $t['status'])) ?></span></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
