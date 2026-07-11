<?php
/** @var \App\Core\View $this */
use App\Models\Attendee;
$this->layout('layouts/admin');
?>
<div style="margin-bottom:18px"><a class="btn btn-ghost btn-sm" href="<?= e(url('/admin/orders')) ?>">← All orders</a></div>

<div class="stat-grid" style="grid-template-columns:repeat(4,1fr)">
  <div class="scard"><div class="k">Reference</div><div class="v" style="font-size:20px"><?= e($order['reference']) ?></div></div>
  <div class="scard"><div class="k">Status</div><div class="v" style="font-size:20px"><span class="tag <?= e($order['status']) ?>"><?= e($order['status']) ?></span></div></div>
  <div class="scard"><div class="k">Amount</div><div class="v"><span class="cur">₦</span><?= e(naira((int) $order['total_kobo'])) ?></div></div>
  <div class="scard"><div class="k">Paid at</div><div class="v" style="font-size:18px"><?= e($order['paid_at'] ? date('d M Y, H:i', strtotime($order['paid_at'])) : '—') ?></div></div>
</div>

<div class="panel">
  <div class="ph"><h2>Buyer</h2></div>
  <div class="pb">
    <?php $name = Attendee::fullName($attendee ?? []); ?>
    <table class="table">
      <tr><th style="width:160px">Name</th><td><?= e($name ?: '—') ?></td></tr>
      <tr><th>Email</th><td><?= e($attendee['email'] ?? '') ?></td></tr>
      <tr><th>Phone</th><td><?= e($attendee['phone'] ?? '') ?></td></tr>
      <tr><th>Organization</th><td><?= e($attendee['organization'] ?? '—') ?></td></tr>
      <tr><th>Job title</th><td><?= e($attendee['job_title'] ?? '—') ?></td></tr>
      <tr><th>Sector / State</th><td><?= e(($attendee['sector'] ?? '—') . ' · ' . ($attendee['state'] ?? '—')) ?></td></tr>
    </table>
  </div>
</div>

<div class="panel">
  <div class="ph"><h2>Line items</h2></div>
  <div class="pb" style="padding-top:0">
    <table class="table">
      <thead><tr><th>Pass</th><th>Unit</th><th>Qty</th><th>Subtotal</th></tr></thead>
      <tbody>
      <?php foreach ($items as $it): ?>
        <tr>
          <td><?= e($it['ticket_name']) ?><?= (int) $it['group_size'] > 1 ? ' <span class="muted">(group of ' . (int) $it['group_size'] . ')</span>' : '' ?></td>
          <td class="num">₦<?= e(naira((int) $it['unit_price_kobo'])) ?></td>
          <td class="num"><?= (int) $it['quantity'] ?></td>
          <td class="num">₦<?= e(naira((int) $it['subtotal_kobo'])) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="panel">
  <div class="ph">
    <h2><?= count($tickets) ?> issued pass<?= count($tickets) === 1 ? '' : 'es' ?></h2>
    <?php if ($tickets): ?>
      <button class="btn btn-gold btn-sm" type="button"
              data-ticket-preview="<?= e(url('/admin/orders/' . rawurlencode($order['reference']) . '/tickets')) ?>"
              data-title="<?= e($order['reference']) ?> — passes">Preview tickets</button>
    <?php endif; ?>
  </div>
  <div class="pb" style="padding-top:0">
    <table class="table">
      <thead><tr><th>Code</th><th>Type</th><th>Holder</th><th>Status</th><th>Checked in</th></tr></thead>
      <tbody>
      <?php if (!$tickets): ?><tr><td colspan="5" class="muted" style="padding:20px 12px">No passes issued (order not paid).</td></tr><?php endif; ?>
      <?php foreach ($tickets as $t): ?>
        <tr>
          <td class="num"><?= e($t['ticket_code']) ?></td>
          <td><?= e($t['ticket_name'] ?? '') ?></td>
          <td><?= e($t['holder_name'] ?: 'Delegate') ?></td>
          <td><span class="tag <?= e($t['status']) ?>"><?= e(str_replace('_', ' ', $t['status'])) ?></span></td>
          <td class="num muted"><?= e($t['checked_in_at'] ? date('d M, H:i', strtotime($t['checked_in_at'])) : '—') ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
