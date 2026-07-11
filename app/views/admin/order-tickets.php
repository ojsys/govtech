<?php
/** Fragment: the designed passes for an order, loaded into the admin modal. */
?>
<?php include VIEW_PATH . '/partials/ticket-styles.php'; ?>
<?php if (!$tickets): ?>
  <p style="color:var(--sage);padding:10px 0">No passes issued for this order yet (it may be unpaid).</p>
<?php else: ?>
  <div style="display:flex;flex-direction:column;gap:18px">
    <?php foreach ($tickets as $t): ?>
      <div>
        <?php include VIEW_PATH . '/partials/ticket-card.php'; ?>
        <div style="text-align:center;margin-top:8px">
          <a href="<?= e(url('/ticket/' . rawurlencode($t['ticket_code']))) ?>" target="_blank" rel="noopener" style="color:var(--gold-soft);font-size:13px">Open full ticket ↗</a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
