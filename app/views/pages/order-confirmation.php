<?php
/** @var \App\Core\View $this */
use App\Core\Qr;
use App\Models\Attendee;
$this->layout('layouts/app');
$isPaid = ($order['status'] ?? '') === 'paid';
?>
<section class="section" style="padding-top:0">
  <div class="wrap" style="max-width:880px">
    <div class="page-head reveal in">
      <span class="eyebrow"><?= $isPaid ? 'Confirmed' : 'Order ' . e($order['status']) ?></span>
      <h1><?= $isPaid ? 'You\'re in. See you in Abuja.' : 'Order received.' ?></h1>
      <p>
        <?php if ($isPaid): ?>
          Your passes are issued. We've also emailed them to <b><?= e($attendee['email'] ?? '') ?></b>. Bring each QR code (on your phone or printed) to check in.
        <?php else: ?>
          We're still confirming your payment. This page will reflect your passes once it clears — you'll also get an email.
        <?php endif; ?>
      </p>
    </div>

    <div class="conf-meta reveal">
      <div><div class="k">Reference</div><div class="v"><?= e($order['reference']) ?></div></div>
      <div><div class="k">Amount</div><div class="v">₦<?= e(naira((int) $order['total_kobo'])) ?></div></div>
      <div><div class="k">Name</div><div class="v"><?= e(Attendee::fullName($attendee ?? [])) ?></div></div>
      <div><div class="k">Passes</div><div class="v"><?= count($tickets) ?></div></div>
    </div>

    <?php if ($tickets): ?>
      <?php include VIEW_PATH . '/partials/ticket-styles.php'; ?>
      <div class="reveal" style="display:flex;flex-direction:column;gap:20px">
      <?php foreach ($tickets as $t): ?>
        <div>
          <?php include VIEW_PATH . '/partials/ticket-card.php'; ?>
          <div style="text-align:center;margin-top:8px"><a href="<?= e(url('/ticket/' . rawurlencode($t['ticket_code']))) ?>" style="color:var(--gold-soft);font-size:13px">Open &amp; print this ticket →</a></div>
        </div>
      <?php endforeach; ?>
      </div>
      <div style="margin-top:26px" class="reveal">
        <a href="<?= e(url('/')) ?>" class="btn btn-ghost">Back to home</a>
        <button class="btn btn-gold" type="button" onclick="window.print()">Print passes</button>
      </div>
    <?php else: ?>
      <div class="alert alert-error reveal">No passes are attached to this order yet. If you've paid, give it a moment and refresh.</div>
    <?php endif; ?>
  </div>
</section>
