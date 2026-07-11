<?php
/**
 * One designed event ticket. Expects: $t (ticket row), $event (or null).
 * Include partials/ticket-styles.php once on the page before using this.
 */
$ev = $ticketEvent ?? null;
$dateStr = 'Oct 7 – 8, 2026';
$venue = 'Presidential Villa, Abuja';
if ($ev) {
    if (!empty($ev['start_date'])) {
        $sd = strtotime($ev['start_date']);
        $ed = strtotime($ev['end_date'] ?? $ev['start_date']);
        $dateStr = date('M j', $sd) . ' – ' . (date('M', $sd) === date('M', $ed) ? date('j', $ed) : date('M j', $ed)) . ', ' . date('Y', $ed);
    }
    if (!empty($ev['venue'])) {
        $venue = $ev['venue']; // exactly as entered in admin
    }
}
$status = $t['status'] ?? 'valid';
$statusCls = $status === 'checked_in' ? 'in' : ($status === 'void' ? 'void' : '');
$statusLabel = $status === 'checked_in' ? 'Checked in' : ($status === 'void' ? 'Void' : 'Valid');
$brand = content('brand_name', 'GovTech');
?>
<div class="eticket">
  <div class="eticket-glow"></div>
  <div class="eticket-main">
    <div class="eticket-brand">
      <svg viewBox="0 0 100 100" fill="none"><circle cx="50" cy="50" r="46" stroke="#C9A227" stroke-width="1.5"/><circle cx="50" cy="50" r="38" stroke="#0C7A4D" stroke-width="1"/><path d="M50 20 L57 44 L82 44 L62 59 L70 83 L50 68 L30 83 L38 59 L18 44 L43 44 Z" fill="#C9A227"/></svg>
      <span><b><?= e($brand) ?> Conference &amp; Awards</b><span>Bureau of Public Service Reforms · NG</span></span>
    </div>
    <div class="eticket-type"><?= e($t['ticket_name'] ?? 'Delegate Pass') ?><?= ($t['source'] ?? '') === 'comp' ? ' · Complimentary' : '' ?></div>
    <div class="eticket-holder">
      <div class="lab">Attendee</div>
      <div class="name"><?= e($t['holder_name'] ?: 'Delegate') ?></div>
    </div>
    <div class="eticket-meta">
      <div><div class="lab">Dates</div><div class="val"><?= e($dateStr) ?></div></div>
      <div><div class="lab">Venue</div><div class="val"><?= e($venue) ?></div></div>
    </div>
  </div>
  <div class="eticket-stub">
    <div class="eticket-qr"><img src="<?= e(url('/ticket/' . rawurlencode($t['ticket_code']) . '/qr.png')) ?>" alt="Scan to verify this ticket" loading="lazy"></div>
    <div class="eticket-code"><?= e($t['ticket_code']) ?></div>
    <div class="eticket-admit">Admit One</div>
    <div class="eticket-status <?= $statusCls ?>"><?= e($statusLabel) ?></div>
  </div>
</div>
