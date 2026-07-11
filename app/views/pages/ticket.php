<?php
/** @var \App\Core\View $this */
$this->layout('layouts/app');
?>
<?php include VIEW_PATH . '/partials/ticket-styles.php'; ?>
<section class="section" style="padding-top:120px;min-height:70vh">
  <div class="wrap" style="max-width:700px">
    <div class="page-head reveal in" style="padding-top:0;text-align:center">
      <span class="eyebrow" style="justify-content:center;display:inline-flex">Your pass</span>
      <h1 style="font-size:clamp(28px,4vw,40px)">Event ticket</h1>
      <p>Bring this QR code to check in — on your phone or printed.</p>
    </div>

    <div class="eticket-print" style="margin-top:34px">
      <?php $t = $ticket; include VIEW_PATH . '/partials/ticket-card.php'; ?>
    </div>

    <div style="text-align:center;margin-top:30px" class="reveal">
      <button class="btn btn-gold" type="button" onclick="window.print()">Print / save ticket</button>
      <a class="btn btn-ghost" href="<?= e(url('/order/' . rawurlencode($ticket['order_reference'] ?? ''))) ?>" style="margin-left:8px">All my passes</a>
    </div>
  </div>
</section>
