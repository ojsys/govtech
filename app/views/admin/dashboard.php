<?php
/** @var \App\Core\View $this */
$this->layout('layouts/admin');
?>
<div class="stat-grid">
  <div class="scard"><div class="k">Revenue (paid)</div><div class="v"><span class="cur">₦</span><?= e(naira((int) $orderStats['revenue_kobo'])) ?></div></div>
  <div class="scard"><div class="k">Paid orders</div><div class="v"><?= (int) $orderStats['orders_paid'] ?></div></div>
  <div class="scard"><div class="k">Pending</div><div class="v"><?= (int) $orderStats['orders_pending'] ?></div></div>
  <div class="scard"><div class="k">Passes issued</div><div class="v"><?= (int) $ticketStats['issued'] ?></div></div>
  <div class="scard"><div class="k">Checked in</div><div class="v"><?= (int) $ticketStats['checked_in'] ?></div></div>
</div>

<div class="panel">
  <div class="ph"><h2>Recent orders</h2><a class="btn btn-ghost btn-sm" href="<?= e(url('/admin/orders')) ?>">View all</a></div>
  <div class="pb" style="padding-top:0">
    <table class="table">
      <thead><tr><th>Reference</th><th>Buyer</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
      <tbody>
      <?php if (!$recentOrders): ?>
        <tr><td colspan="5" class="muted" style="padding:20px 12px">No orders yet.</td></tr>
      <?php endif; ?>
      <?php foreach ($recentOrders as $o): ?>
        <tr>
          <td class="num"><a style="color:var(--gold-soft)" href="<?= e(url('/admin/orders/' . $o['reference'])) ?>"><?= e($o['reference']) ?></a></td>
          <td><?= e(trim(($o['first_name'] ?? '') . ' ' . ($o['last_name'] ?? '')) ?: '—') ?><br><span class="muted"><?= e($o['email'] ?? '') ?></span></td>
          <td class="num">₦<?= e(naira((int) $o['total_kobo'])) ?></td>
          <td><span class="tag <?= e($o['status']) ?>"><?= e($o['status']) ?></span></td>
          <td class="num muted"><?= e($o['created_at'] ? date('d M, H:i', strtotime($o['created_at'])) : '') ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="panel">
  <div class="ph"><h2>Recent activity</h2></div>
  <div class="pb" style="padding-top:0">
    <table class="table">
      <thead><tr><th>When</th><th>User</th><th>Action</th><th>Entity</th></tr></thead>
      <tbody>
      <?php if (!$audit): ?><tr><td colspan="4" class="muted" style="padding:20px 12px">No activity yet.</td></tr><?php endif; ?>
      <?php foreach ($audit as $a): ?>
        <tr>
          <td class="num muted"><?= e($a['created_at'] ? date('d M, H:i', strtotime($a['created_at'])) : '') ?></td>
          <td><?= e($a['user_name'] ?? '—') ?></td>
          <td class="num"><?= e($a['action']) ?></td>
          <td class="num muted"><?= e($a['entity']) ?><?= $a['entity_id'] ? ' #' . (int) $a['entity_id'] : '' ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
