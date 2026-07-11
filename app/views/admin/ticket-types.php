<?php
/** @var \App\Core\View $this */
$this->layout('layouts/admin');
?>
<div class="panel">
  <div class="ph">
    <h2><?= count($tickets) ?> ticket type<?= count($tickets) === 1 ? '' : 's' ?></h2>
    <a class="btn btn-gold btn-sm" href="<?= e(url('/admin/ticket-types/create')) ?>">+ New ticket type</a>
  </div>
  <div class="pb" style="padding-top:0">
    <table class="table">
      <thead><tr><th>Name</th><th>Price</th><th>Group</th><th>Sold</th><th>Featured</th><th>Active</th><th>Sort</th><th></th></tr></thead>
      <tbody>
      <?php if (!$tickets): ?><tr><td colspan="8" class="muted" style="padding:20px 12px">No ticket types yet.</td></tr><?php endif; ?>
      <?php foreach ($tickets as $t): ?>
        <tr>
          <td><b><?= e($t['name']) ?></b><br><span class="muted" style="font-size:12px"><?= e($t['slug']) ?></span></td>
          <td class="num">₦<?= e(naira((int) $t['price_kobo'])) ?></td>
          <td class="num"><?= (int) $t['group_size'] ?></td>
          <td class="num"><?= (int) $t['sold'] ?></td>
          <td><?= !empty($t['featured']) ? '<span class="tag paid">Yes</span>' : '<span class="tag">No</span>' ?></td>
          <td><?= (int) $t['is_active'] ? '<span class="tag paid">Active</span>' : '<span class="tag">Hidden</span>' ?></td>
          <td class="num"><?= (int) $t['sort'] ?></td>
          <td style="text-align:right;white-space:nowrap">
            <a class="btn btn-ghost btn-sm" href="<?= e(url('/admin/ticket-types/' . (int) $t['id'] . '/edit')) ?>">Edit</a>
            <form method="post" action="<?= e(url('/admin/ticket-types/' . (int) $t['id'] . '/delete')) ?>" style="display:inline" onsubmit="return confirm('Delete this ticket type? If it has orders it will be hidden instead.')">
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

<!-- Live preview of the pass cards as shown on the public site -->
<div class="panel">
  <div class="ph">
    <h2>Live preview</h2>
    <a class="btn btn-ghost btn-sm" href="<?= e(url('/admin/content/event')) ?>">Edit date &amp; venue on tickets</a>
  </div>
  <div class="pb">
    <?php if (!$tickets): ?>
      <p class="muted" style="color:var(--sage)">No ticket types to preview.</p>
    <?php else: ?>
    <div class="tkp-stage">
      <div class="tkp-grid">
        <?php
        $chk = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><path d="M20 6L9 17l-5-5"/></svg>';
        foreach ($tickets as $t):
          $perks = json_col($t['perks_json'] ?? null);
          $feat = !empty($t['featured']);
          $hidden = empty($t['is_active']);
        ?>
        <div class="tkp <?= $feat ? 'feat' : '' ?> <?= $hidden ? 'is-hidden' : '' ?>">
          <?php if ($feat): ?><div class="tkp-badge">Most popular</div><?php endif; ?>
          <?php if ($hidden): ?><div class="tkp-hidden">Hidden</div><?php endif; ?>
          <div class="tkp-name"><?= e($t['name']) ?></div>
          <div class="tkp-price"><span class="tkp-cur">₦</span><?= e(naira((int) $t['price_kobo'])) ?></div>
          <div class="tkp-per"><?= e($t['description'] ?? '') ?><?= (int) $t['group_size'] > 1 ? ' · group of ' . (int) $t['group_size'] : '' ?></div>
          <ul class="tkp-perks">
            <?php foreach ($perks as $p): ?><li><span class="tkp-ico"><?= $chk ?></span><span><?= e($p) ?></span></li><?php endforeach; ?>
          </ul>
          <div class="tkp-foot"><a href="<?= e(url('/admin/ticket-types/' . (int) $t['id'] . '/edit')) ?>" class="btn btn-ghost btn-sm">Edit this pass</a></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<style>
.tkp-stage{background:#F5F2E9;border-radius:8px;padding:22px}
.tkp-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px}
.tkp{position:relative;background:#fff;border:1px solid rgba(7,20,14,.1);border-radius:6px;padding:24px 22px;color:#07140E;display:flex;flex-direction:column;overflow:hidden;font-family:var(--body)}
.tkp::before{content:"";position:absolute;top:0;left:0;right:0;height:4px;background:#0C7A4D}
.tkp.feat::before{background:#C9A227}
.tkp.feat{box-shadow:0 24px 50px -28px rgba(7,20,14,.4);border-color:#C9A227}
.tkp.is-hidden{opacity:.5}
.tkp-badge{position:absolute;top:12px;right:12px;font-family:var(--mono);font-size:9px;letter-spacing:.12em;text-transform:uppercase;background:#C9A227;color:#1a1405;padding:5px 9px;border-radius:30px;font-weight:600}
.tkp-hidden{position:absolute;top:12px;right:12px;font-family:var(--mono);font-size:9px;letter-spacing:.12em;text-transform:uppercase;background:#102B20;color:#9FB3A8;padding:5px 9px;border-radius:30px}
.tkp-name{font-family:var(--mono);font-size:11px;letter-spacing:.16em;text-transform:uppercase;color:#0C7A4D}
.tkp.feat .tkp-name{color:#9a7d14}
.tkp-price{font-family:var(--display);font-size:32px;color:#07140E;margin:12px 0 2px;font-weight:600}
.tkp-cur{font-size:17px;color:#7a8a80;vertical-align:super}
.tkp-per{font-size:12.5px;color:#7a8a80;margin-bottom:16px}
.tkp-perks{list-style:none;margin:8px 0 18px;display:flex;flex-direction:column;gap:10px;padding:0}
.tkp-perks li{font-size:13.5px;color:#3c4b42;display:flex;gap:9px;align-items:flex-start}
.tkp-ico{flex:0 0 15px;margin-top:2px;color:#0C7A4D}
.tkp-ico svg{width:15px;height:15px}
.tkp-foot{margin-top:auto}
.tkp-foot .btn{color:#0C7A4D;border-color:rgba(7,20,14,.18)}
.tkp-foot .btn:hover{border-color:#C9A227}
</style>

