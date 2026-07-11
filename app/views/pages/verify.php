<?php
/** @var \App\Core\View $this */
$this->layout('layouts/app');

$t = $ticket;
$state = 'invalid';            // invalid | void | ok | used
if ($t && $authentic) {
    $status = $t['status'] ?? 'valid';
    $state = $status === 'void' ? 'void' : ($status === 'checked_in' ? 'used' : 'ok');
}
$map = [
    'ok'      => ['#16B47A', '✓', 'Genuine ticket', 'This pass is authentic and valid for entry.'],
    'used'    => ['#16B47A', '✓', 'Genuine ticket', 'This pass is authentic. It has already been checked in.'],
    'void'    => ['#E4C865', '!', 'Pass voided', 'This pass was issued but has since been cancelled. Not admissible.'],
    'invalid' => ['#d9534f', '✕', 'Not verified', 'We could not verify this ticket. It may be invalid or counterfeit.'],
];
[$color, $glyph, $title, $blurb] = $map[$state];

// Event line
$evLine = 'Nigeria GovTech Conference & Awards';
if (!empty($ticketEvent['name'])) {
    $evLine = $ticketEvent['name'];
}
?>
<section class="section" style="padding-top:120px;min-height:72vh;display:grid;place-items:center">
  <div class="wrap" style="max-width:520px">
    <div class="reveal in" style="background:var(--ink-2);border:1px solid var(--line);border-top:3px solid <?= $color ?>;border-radius:12px;padding:38px 32px;text-align:center">
      <div style="width:74px;height:74px;border-radius:50%;margin:0 auto 20px;display:grid;place-items:center;background:<?= $color ?>1f;border:2px solid <?= $color ?>">
        <span style="font-size:34px;line-height:1;color:<?= $color ?>;font-weight:700"><?= $glyph ?></span>
      </div>
      <div class="eyebrow" style="justify-content:center;display:inline-flex;color:<?= $color ?>"><?= e($evLine) ?></div>
      <h1 class="serif" style="font-size:clamp(28px,5vw,40px);color:#fff;margin:10px 0 10px;font-weight:600"><?= e($title) ?></h1>
      <p style="color:var(--sage);margin-bottom:<?= $t && $authentic ? '26px' : '8px' ?>"><?= e($blurb) ?></p>

      <?php if ($t && $authentic): ?>
        <div style="text-align:left;background:var(--ink);border:1px solid var(--line);border-radius:8px;padding:18px 20px;margin-bottom:8px">
          <?php
          $rows = [
              'Pass'     => ($t['ticket_name'] ?? 'Delegate Pass') . (($t['source'] ?? '') === 'comp' ? ' · Complimentary' : ''),
              'Attendee' => $t['holder_name'] ?: 'Delegate',
              'Code'     => $t['ticket_code'],
          ];
          if (($t['status'] ?? '') === 'checked_in' && !empty($t['checked_in_at'])) {
              $rows['Checked in'] = date('d M Y, H:i', strtotime($t['checked_in_at']));
          }
          foreach ($rows as $k => $v): ?>
            <div style="display:flex;justify-content:space-between;gap:16px;padding:7px 0;border-bottom:1px solid var(--line)">
              <span style="font-family:var(--mono);font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:var(--sage)"><?= e($k) ?></span>
              <span style="color:#fff;text-align:right"><?= e($v) ?></span>
            </div>
          <?php endforeach; ?>
        </div>

        <?php if ($isAdmin && $state === 'ok'): ?>
          <a href="<?= e(url('/admin/checkin?code=' . rawurlencode($t['ticket_code']))) ?>" class="btn btn-gold" style="margin-top:18px">Check in this delegate <span class="arrow">→</span></a>
        <?php elseif ($isAdmin && $state === 'used'): ?>
          <p style="color:var(--gold-soft);font-size:13px;margin-top:14px;font-family:var(--mono)">Already admitted — do not re-admit.</p>
        <?php endif; ?>
      <?php endif; ?>

      <div style="margin-top:24px">
        <a href="<?= e(url('/')) ?>" style="color:var(--sage);font-size:13px">Nigeria GovTech Conference &amp; Awards →</a>
      </div>
    </div>
  </div>
</section>
