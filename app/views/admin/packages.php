<?php
/** @var \App\Core\View $this */
$this->layout('layouts/admin');
?>
<div class="panel">
  <div class="ph">
    <h2><?= count($packages) ?> package<?= count($packages) === 1 ? '' : 's' ?></h2>
    <a class="btn btn-gold btn-sm" href="<?= e(url('/admin/packages/create')) ?>">+ New package</a>
  </div>
  <div class="pb" style="padding-top:0">
    <table class="table">
      <thead><tr><th>Name</th><th>Type</th><th>Price</th><th>Comp passes</th><th>Booth</th><th>Active</th><th></th></tr></thead>
      <tbody>
      <?php if (!$packages): ?><tr><td colspan="7" class="muted" style="padding:20px 12px">No packages yet.</td></tr><?php endif; ?>
      <?php foreach ($packages as $p): ?>
        <tr>
          <td><b><?= e($p['name']) ?></b></td>
          <td class="muted"><?= e(ucfirst($p['type'])) ?></td>
          <td class="num">₦<?= e(naira((int) $p['price_kobo'])) ?></td>
          <td class="num"><?= (int) $p['comp_passes'] ?></td>
          <td class="muted"><?= e($p['booth_size'] ?? '—') ?></td>
          <td><?= (int) $p['is_active'] ? '<span class="tag paid">Active</span>' : '<span class="tag">Hidden</span>' ?></td>
          <td style="text-align:right;white-space:nowrap">
            <a class="btn btn-ghost btn-sm" href="<?= e(url('/admin/packages/' . (int) $p['id'] . '/edit')) ?>">Edit</a>
            <form method="post" action="<?= e(url('/admin/packages/' . (int) $p['id'] . '/delete')) ?>" style="display:inline" onsubmit="return confirm('Delete this package? If it has applications it will be hidden instead.')">
              <input type="hidden" name="_token" value="<?= e($csrf) ?>">
              <button class="btn btn-danger btn-sm" type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
