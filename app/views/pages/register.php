<?php
/** @var \App\Core\View $this */
$this->layout('layouts/app');
$errors = $errors ?? [];
$qty = max(1, (int) ($qty ?? 1));
$err = fn(string $f) => isset($errors[$f]) ? '<div class="err">' . e($errors[$f]) . '</div>' : '';
$cls = fn(string $f) => isset($errors[$f]) ? ' invalid' : '';
?>
<section class="section" style="padding-top:0">
  <div class="wrap">
    <div class="page-head reveal in">
      <span class="eyebrow">Registration &amp; Passes</span>
      <h1>Secure your place.</h1>
      <p>Tell us who's attending. Admission is free for all — register one pass for yourself, or several for your team.</p>
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

        <!-- Single free pass + quantity -->
        <div class="card reveal d1">
          <h3><?= e($pass['name'] ?? 'General Admission') ?></h3>
          <p class="hint"><?= e($pass['description'] ?? 'Full access to all sessions, the exhibition and the awards gala.') ?></p>

          <div class="cart-row">
            <div class="ci">
              <b>Free admission</b>
              <span>How many passes do you need?</span>
            </div>
            <div class="stepper">
              <button type="button" data-op="-1" aria-label="Remove one">−</button>
              <input type="number" name="quantity" id="regQty" value="<?= $qty ?>" min="1" max="20" inputmode="numeric" readonly>
              <button type="button" data-op="1" aria-label="Add one">+</button>
            </div>
          </div>

          <div class="summary-line">
            <span class="lab">Admission</span>
            <span class="val">Free</span>
          </div>

          <button type="submit" class="btn btn-gold btn-block">Complete registration <span class="arrow">→</span></button>
          <div class="secure-note">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Free admission · Your passes are emailed instantly
          </div>
        </div>
      </div>
    </form>
  </div>
</section>

<script>
(function () {
  var input = document.getElementById('regQty');
  if (!input) return;
  document.querySelectorAll('.stepper button').forEach(function (b) {
    b.onclick = function () {
      var q = (+input.value || 1) + (+b.getAttribute('data-op'));
      input.value = Math.max(1, Math.min(20, q));
    };
  });
})();
</script>
