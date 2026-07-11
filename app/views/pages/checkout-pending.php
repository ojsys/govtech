<?php
/** @var \App\Core\View $this */
$this->layout('layouts/app');
?>
<section class="section" style="min-height:60vh;display:grid;place-items:center;text-align:center;padding-top:120px">
  <div class="wrap reveal in" style="max-width:560px">
    <span class="eyebrow" style="justify-content:center;display:inline-flex">Payment processing</span>
    <h1 class="serif" style="font-size:clamp(30px,5vw,48px);color:#fff;margin:14px 0 12px;font-weight:500">Almost there.</h1>
    <p style="color:var(--sage);margin-bottom:28px">We're confirming your payment with Paystack. This usually takes a few seconds. If it doesn't update automatically, refresh this page — once confirmed, your passes appear and an email is sent.</p>
    <?php if (!empty($order['reference'])): ?>
      <a href="<?= e(url('/order/' . $order['reference'])) ?>" class="btn btn-gold">Refresh order status <span class="arrow">→</span></a>
    <?php endif; ?>
  </div>
</section>
<?php if (!empty($order['reference'])): ?>
<script>
  // Auto-refresh a few times while the webhook confirms the charge.
  var tries = 0;
  var iv = setInterval(function () {
    if (++tries > 6) { clearInterval(iv); return; }
    location.href = <?= json_encode(url('/order/' . $order['reference'])) ?>;
  }, 5000);
</script>
<?php endif; ?>
