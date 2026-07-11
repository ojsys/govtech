<?php
/** @var \App\Core\View $this */
$this->layout('layouts/admin');
$statuses = ['' => 'All', 'new' => 'New', 'contacted' => 'Contacted', 'invoiced' => 'Invoiced', 'confirmed' => 'Confirmed', 'paid' => 'Paid'];
$tagFor = fn(string $s) => in_array($s, ['confirmed', 'paid'], true) ? 'paid' : ($s === 'new' ? 'pending' : 'valid');
?>
<div class="stat-grid" style="grid-template-columns:repeat(3,1fr)">
  <div class="scard"><div class="k">Applications</div><div class="v"><?= (int) $stats['total'] ?></div></div>
  <div class="scard"><div class="k">New / unactioned</div><div class="v"><?= (int) $stats['new'] ?></div></div>
  <div class="scard"><div class="k">Confirmed</div><div class="v"><?= (int) $stats['confirmed'] ?></div></div>
</div>

<div class="panel">
  <div class="ph">
    <h2>Applications</h2>
    <form method="get" action="<?= e(url('/admin/sponsors')) ?>">
      <select name="status" onchange="this.form.submit()" style="padding:8px 12px;background:var(--ink);border:1px solid var(--line);border-radius:3px;color:#E8EFE9;font-family:var(--body)">
        <?php foreach ($statuses as $val => $lbl): ?>
          <option value="<?= e($val) ?>" <?= $status === $val ? 'selected' : '' ?>><?= e($lbl) ?></option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>
  <div class="pb" style="padding-top:0">
    <table class="table">
      <thead><tr><th>Company</th><th>Package</th><th>Contact</th><th>Comp</th><th>Status</th><th>Portal</th><th></th></tr></thead>
      <tbody>
      <?php if (!$applications): ?><tr><td colspan="7" class="muted" style="padding:20px 12px">No applications yet.</td></tr><?php endif; ?>
      <?php foreach ($applications as $a): ?>
        <tr>
          <td><b><?= e($a['company_name']) ?></b></td>
          <td class="muted"><?= e(ucfirst($a['package_type'])) ?> · <?= e($a['package_name']) ?></td>
          <td class="muted"><?= e($a['contact_name']) ?><br><span style="font-size:12px"><?= e($a['email']) ?></span></td>
          <td class="num"><?= (int) $a['comp_passes'] ?></td>
          <td><span class="tag <?= $tagFor($a['status']) ?>"><?= e($a['status']) ?></span></td>
          <td><?= (int) $a['has_account'] ? '<span class="tag paid">Yes</span>' : '<span class="tag">—</span>' ?></td>
          <td style="text-align:right"><a class="btn btn-ghost btn-sm" href="<?= e(url('/admin/sponsors/' . (int) $a['id'])) ?>">Open</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Asset review queue -->
<div class="panel">
  <div class="ph"><h2>Sponsor assets</h2></div>
  <div class="pb" style="padding-top:0">
    <table class="table">
      <thead><tr><th>Company</th><th>Type</th><th>File</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php if (!$assets): ?><tr><td colspan="5" class="muted" style="padding:20px 12px">No assets uploaded yet.</td></tr><?php endif; ?>
      <?php foreach ($assets as $s):
        $url = rtrim((string) \Config::get('app.uploads_url', '/uploads'), '/') . '/' . rawurlencode($s['file_path']);
      ?>
        <tr>
          <td><?= e($s['company_name']) ?></td>
          <td class="muted"><?= e(str_replace('_', ' ', $s['type'])) ?></td>
          <td><a href="<?= e($url) ?>" target="_blank" rel="noopener" style="color:var(--gold-soft)">View</a></td>
          <td><span class="tag <?= $s['status'] === 'approved' ? 'paid' : ($s['status'] === 'rejected' ? 'failed' : 'pending') ?>"><?= e($s['status']) ?></span></td>
          <td style="white-space:nowrap">
            <?php foreach (['approved' => 'Approve', 'rejected' => 'Reject'] as $st => $lbl):
              if ($s['status'] === $st) continue; ?>
              <form method="post" action="<?= e(url('/admin/sponsors/assets/' . (int) $s['id'] . '/review')) ?>" style="display:inline">
                <input type="hidden" name="_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="status" value="<?= e($st) ?>">
                <button class="btn btn-ghost btn-sm" type="submit"><?= e($lbl) ?></button>
              </form>
            <?php endforeach; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
