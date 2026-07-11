<?php
/** @var \App\Core\View $this */
$this->layout('layouts/app');
$errors = $errors ?? [];
$cart = $cart ?? [];
$err = fn(string $f) => isset($errors[$f]) ? '<div class="err">' . e($errors[$f]) . '</div>' : '';
$cls = fn(string $f) => isset($errors[$f]) ? ' invalid' : '';
?>
<section class="section" style="padding-top:0">
  <div class="wrap">
    <div class="page-head reveal in">
      <span class="eyebrow">Registration &amp; Passes</span>
      <h1>Secure your place.</h1>
      <p>Select your passes, tell us who's attending, and pay securely with Paystack. Group and virtual options are available.</p>
    </div>

    <?php if ($msg = flash('error')): ?>
      <div class="alert alert-error" style="margin-top:30px"><?= e($msg) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= e(url('/register')) ?>" id="regForm">
      <input type="hidden" name="_token" value="<?= e($csrf) ?>">
      <div class="hp" aria-hidden="true"><label>Leave this empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

      <div class="reg-grid">
        <!-- Attendee details -->
        <div class="card reveal">
          <h3>Your details</h3>
          <p class="hint">We'll send your passes and QR codes to this email.</p>

          <div class="field-row">
            <div class="field<?= $cls('first_name') ?>">
              <label>First name</label>
              <input type="text" name="first_name" value="<?= old('first_name') ?>" autocomplete="given-name" required>
              <?= $err('first_name') ?>
            </div>
            <div class="field<?= $cls('last_name') ?>">
              <label>Last name</label>
              <input type="text" name="last_name" value="<?= old('last_name') ?>" autocomplete="family-name" required>
              <?= $err('last_name') ?>
            </div>
          </div>

          <div class="field-row">
            <div class="field<?= $cls('email') ?>">
              <label>Email</label>
              <input type="email" name="email" value="<?= old('email') ?>" autocomplete="email" required>
              <?= $err('email') ?>
            </div>
            <div class="field<?= $cls('phone') ?>">
              <label>Phone</label>
              <input type="tel" name="phone" value="<?= old('phone') ?>" autocomplete="tel" required>
              <?= $err('phone') ?>
            </div>
          </div>

          <div class="field-row">
            <div class="field">
              <label>Organization</label>
              <input type="text" name="organization" value="<?= old('organization') ?>" autocomplete="organization">
            </div>
            <div class="field">
              <label>Job title</label>
              <input type="text" name="job_title" value="<?= old('job_title') ?>" autocomplete="organization-title">
            </div>
          </div>

          <div class="field-row">
            <div class="field">
              <label>Sector</label>
              <select name="sector">
                <?php foreach (['public' => 'Public sector / MDA', 'private' => 'Private sector', 'academia' => 'Academia', 'other' => 'Other'] as $val => $lbl): ?>
                  <option value="<?= e($val) ?>" <?= old('sector', 'public') === $val ? 'selected' : '' ?>><?= e($lbl) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="field">
              <label>State</label>
              <input type="text" name="state" value="<?= old('state') ?>" autocomplete="address-level1">
            </div>
          </div>
        </div>

        <!-- Pass selector + summary -->
        <div class="card reveal d1">
          <h3>Your passes</h3>
          <p class="hint">Adjust quantities below.<?= isset($errors['cart']) ? ' <span style="color:#f0a3a0">' . e($errors['cart']) . '</span>' : '' ?></p>

          <div id="cartRows">
            <?php foreach ($tickets as $t):
              $naira = (int) ((int) $t['price_kobo'] / 100);
              $qty = (int) ($cart[(int) $t['id']] ?? 0);
            ?>
            <div class="cart-row" data-price="<?= $naira ?>">
              <div class="ci">
                <b><?= e($t['name']) ?></b>
                <span>₦<?= e(naira((int) $t['price_kobo'])) ?> · <?= e($t['description'] ?? '') ?></span>
              </div>
              <div class="stepper">
                <button type="button" data-op="-1" aria-label="Remove one">−</button>
                <input type="number" name="qty[<?= (int) $t['id'] ?>]" value="<?= $qty ?>" min="0" max="50" inputmode="numeric" readonly>
                <button type="button" data-op="1" aria-label="Add one">+</button>
              </div>
            </div>
            <?php endforeach; ?>
          </div>

          <div class="summary-line">
            <span class="lab">Total</span>
            <span class="val">₦<span id="regTotal">0</span></span>
          </div>

          <button type="submit" class="btn btn-gold btn-block">Pay with Paystack <span class="arrow">→</span></button>
          <div class="secure-note">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Secured by Paystack · Cards, transfer &amp; USSD
          </div>
        </div>
      </div>
    </form>
  </div>
</section>

<script>
(function () {
  var rows = document.querySelectorAll('#cartRows .cart-row');
  var totalEl = document.getElementById('regTotal');
  function recalc() {
    var total = 0;
    rows.forEach(function (row) {
      var price = +row.getAttribute('data-price') || 0;
      var input = row.querySelector('input');
      total += (+input.value || 0) * price;
    });
    totalEl.textContent = total.toLocaleString('en-NG');
  }
  rows.forEach(function (row) {
    var input = row.querySelector('input');
    row.querySelectorAll('.stepper button').forEach(function (b) {
      b.onclick = function () {
        var q = (+input.value || 0) + (+b.getAttribute('data-op'));
        input.value = Math.max(0, Math.min(50, q));
        recalc();
      };
    });
  });
  recalc();
})();
</script>
