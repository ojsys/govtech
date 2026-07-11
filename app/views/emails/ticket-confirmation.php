<?php
/** Email: ticket confirmation with designed passes. Table layout + inline styles
 *  (email clients ignore <style> and don't render inline SVG, so the QR is a
 *  PNG referenced by URL). */
use App\Models\Attendee;
$base = rtrim((string) Config::get('app.base_url', ''), '/');
$name = Attendee::fullName($attendee ?? []);

// Event date/venue line
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
?>
<div style="background:#07140E;padding:32px 0;font-family:Arial,Helvetica,sans-serif;color:#E8EFE9">
  <div style="max-width:600px;margin:0 auto">
    <div style="background:#0C7A4D;padding:22px 28px;border-radius:10px 10px 0 0">
      <div style="font-size:12px;letter-spacing:2px;text-transform:uppercase;color:#E4C865;font-family:'Courier New',monospace">Nigeria GovTech Conference &amp; Awards</div>
      <div style="font-size:22px;color:#ffffff;margin-top:6px;font-weight:bold">Your passes are confirmed</div>
    </div>
    <div style="background:#0B1E16;border:1px solid rgba(159,179,168,.16);border-top:none;border-radius:0 0 10px 10px;padding:28px">
      <p style="margin:0 0 14px;color:#C2D2C8">Hi <?= e($name) ?>,</p>
      <p style="margin:0 0 8px;color:#C2D2C8">Thank you for registering. Your payment was received and your <?= count($tickets) ?> pass<?= count($tickets) > 1 ? 'es are' : ' is' ?> issued. Present the QR on each ticket at check-in — scanning it also confirms the pass is genuine.</p>
      <table style="width:100%;border-collapse:collapse;margin:14px 0 26px">
        <tr>
          <td style="padding:7px 0;color:#9FB3A8;font-size:12px;font-family:'Courier New',monospace">ORDER</td>
          <td style="padding:7px 0;color:#ffffff;text-align:right;font-weight:bold"><?= e($order['reference']) ?></td>
        </tr>
        <tr>
          <td style="padding:7px 0;color:#9FB3A8;font-size:12px;font-family:'Courier New',monospace;border-top:1px solid rgba(159,179,168,.16)">AMOUNT</td>
          <td style="padding:7px 0;color:#E4C865;text-align:right;font-weight:bold;border-top:1px solid rgba(159,179,168,.16)">&#8358;<?= e(naira((int) $order['total_kobo'])) ?></td>
        </tr>
      </table>

      <?php foreach ($tickets as $t):
        $type = ($t['ticket_name'] ?? 'Delegate Pass') . (($t['source'] ?? '') === 'comp' ? ' · Complimentary' : '');
        $qrUrl = $base . '/ticket/' . rawurlencode($t['ticket_code']) . '/qr.png';
      ?>
      <!-- ticket -->
      <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:separate;border:1px solid rgba(201,162,39,.35);border-radius:10px;overflow:hidden;margin-bottom:16px;background:#102B20">
        <tr>
          <td style="padding:22px 24px;vertical-align:top">
            <div style="font-family:'Courier New',monospace;font-size:9px;letter-spacing:1.5px;text-transform:uppercase;color:#9FB3A8;margin-bottom:12px">Nigeria GovTech · Conference &amp; Awards</div>
            <span style="display:inline-block;font-family:'Courier New',monospace;font-size:10px;letter-spacing:1px;text-transform:uppercase;color:#1a1405;background:#C9A227;padding:5px 11px;border-radius:30px;font-weight:bold"><?= e($type) ?></span>
            <div style="font-size:11px;color:#9FB3A8;font-family:'Courier New',monospace;margin-top:16px;letter-spacing:1px">ATTENDEE</div>
            <div style="font-size:22px;color:#ffffff;font-weight:bold;margin-top:3px"><?= e($t['holder_name'] ?: 'Delegate') ?></div>
            <table style="margin-top:16px"><tr>
              <td style="padding-right:28px"><div style="font-size:9px;color:#9FB3A8;font-family:'Courier New',monospace;letter-spacing:1px">DATES</div><div style="font-size:13px;color:#E8EFE9;margin-top:2px"><?= e($dateStr) ?></div></td>
              <td><div style="font-size:9px;color:#9FB3A8;font-family:'Courier New',monospace;letter-spacing:1px">VENUE</div><div style="font-size:13px;color:#E8EFE9;margin-top:2px"><?= e($venue) ?></div></td>
            </tr></table>
          </td>
          <td width="172" style="width:172px;background:#0a1812;border-left:2px dashed rgba(159,179,168,.4);padding:20px 16px;text-align:center;vertical-align:middle">
            <img src="<?= e($qrUrl) ?>" width="120" height="120" alt="Scan to verify" style="display:block;margin:0 auto;background:#ffffff;border-radius:6px;padding:6px">
            <div style="font-family:'Courier New',monospace;font-size:12px;color:#ffffff;margin-top:12px;letter-spacing:.5px"><?= e($t['ticket_code']) ?></div>
            <div style="font-family:'Courier New',monospace;font-size:9px;letter-spacing:3px;text-transform:uppercase;color:#E4C865;margin-top:10px">Admit One</div>
          </td>
        </tr>
      </table>
      <?php endforeach; ?>

      <p style="margin:22px 0 0;text-align:center">
        <a href="<?= e($base . '/order/' . rawurlencode($order['reference'])) ?>" style="display:inline-block;background:#C9A227;color:#1a1405;text-decoration:none;font-weight:bold;padding:13px 26px;border-radius:3px">View &amp; print your tickets &#8594;</a>
      </p>
      <p style="margin:22px 0 0;color:#9FB3A8;font-size:12px;text-align:center">If the QR codes don't appear, tap “show images”. Banquet Hall, Presidential Villa, Abuja.</p>
    </div>
  </div>
</div>
