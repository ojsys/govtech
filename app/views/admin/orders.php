<?php
/** @var \App\Core\View $this */
$this->layout('layouts/admin');
$statuses = ['' => 'All', 'paid' => 'Paid', 'pending' => 'Pending', 'failed' => 'Failed', 'cancelled' => 'Cancelled'];
$q = fn(array $extra) => e(url('/admin/orders') . '?' . http_build_query(array_merge(['status' => $status], $extra)));
?>
<div class="panel">
  <div class="ph">
    <h2><?= (int) $total ?> order<?= $total === 1 ? '' : 's' ?></h2>
    <div style="display:flex;gap:10px;align-items:center">
      <form method="get" action="<?= e(url('/admin/orders')) ?>">
        <select name="status" onchange="this.form.submit()" style="padding:8px 12px;background:var(--ink);border:1px solid var(--line);border-radius:3px;color:#E8EFE9;font-family:var(--body)">
          <?php foreach ($statuses as $val => $lbl): ?>
            <option value="<?= e($val) ?>" <?= $status === $val ? 'selected' : '' ?>><?= e($lbl) ?></option>
          <?php endforeach; ?>
        </select>
      </form>
      <a class="btn btn-green btn-sm" href="<?= e(url('/admin/orders/export.csv')) ?>">Export CSV</a>
    </div>
  </div>
  <div class="pb" style="padding-top:0">
    <table class="table">
      <thead><tr><th>Reference</th><th>Buyer</th><th>Organization</th><th>Amount</th><th>Status</th><th>Date</th><th>Ticket</th></tr></thead>
      <tbody>
      <?php if (!$orders): ?><tr><td colspan="7" class="muted" style="padding:20px 12px">No orders match.</td></tr><?php endif; ?>
      <?php foreach ($orders as $o): ?>
        <tr>
          <td class="num"><a style="color:var(--gold-soft)" href="<?= e(url('/admin/orders/' . $o['reference'])) ?>"><?= e($o['reference']) ?></a></td>
          <td><?= e(trim(($o['first_name'] ?? '') . ' ' . ($o['last_name'] ?? '')) ?: '—') ?><br><span class="muted"><?= e($o['email'] ?? '') ?></span></td>
          <td><?= e($o['organization'] ?? '—') ?></td>
          <td class="num">₦<?= e(naira((int) $o['total_kobo'])) ?></td>
          <td><span class="tag <?= e($o['status']) ?>"><?= e($o['status']) ?></span></td>
          <td class="num muted"><?= e($o['created_at'] ? date('d M Y, H:i', strtotime($o['created_at'])) : '') ?></td>
          <td>
            <button class="btn btn-ghost btn-sm" type="button"
                    data-ticket-preview="<?= e(url('/admin/orders/' . rawurlencode($o['reference']) . '/tickets')) ?>"
                    data-title="<?= e($o['reference']) ?> — passes">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" style="vertical-align:-2px"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2z"/></svg>
              Preview
            </button>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php if ($pages > 1): ?>
    <div class="pager">
      <?php if ($page > 1): ?><a href="<?= $q(['page' => $page - 1]) ?>">← Prev</a><?php endif; ?>
      <span class="cur">Page <?= $page ?> of <?= $pages ?></span>
      <?php if ($page < $pages): ?><a href="<?= $q(['page' => $page + 1]) ?>">Next →</a><?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
