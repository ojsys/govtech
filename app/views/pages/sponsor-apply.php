<?php
/** @var \App\Core\View $this */
$this->layout('layouts/app');
$errors = $errors ?? [];
$err = fn(string $f) => isset($errors[$f]) ? '<div class="err">' . e($errors[$f]) . '</div>' : '';
$cls = fn(string $f) => isset($errors[$f]) ? ' invalid' : '';
?>
<section class="section" style="padding-top:0">
  <div class="wrap" style="max-width:760px">
    <div class="page-head reveal in">
      <span class="eyebrow">Partnership &amp; Sponsorship</span>
      <h1>Apply to sponsor or exhibit.</h1>
      <p>Tell us about your organisation and the package you're interested in. Our partnerships team will follow up with the prospectus and next steps. <a style="color:var(--gold-soft)" href="<?= e(url('/sponsor')) ?>">See all packages →</a></p>
    </div>

    <?php if ($m = flash('ok')): ?><div class="alert alert-ok" style="margin-top:24px"><?= e($m) ?></div><?php endif; ?>
    <?php if ($m = flash('error')): ?><div class="alert alert-error" style="margin-top:24px"><?= e($m) ?></div><?php endif; ?>

    <form method="post" action="<?= e(url('/sponsor/apply')) ?>" enctype="multipart/form-data" class="card reveal" style="margin-top:30px">
      <input type="hidden" name="_token" value="<?= e($csrf) ?>">
      <div class="hp" aria-hidden="true"><label>Leave empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

      <div class="field<?= $cls('package_id') ?>">
        <label>Package of interest</label>
        <select name="package_id" required>
          <option value="">— choose a package —</option>
          <?php foreach ($packages as $p): ?>
            <option value="<?= (int) $p['id'] ?>" <?= (int) $selected === (int) $p['id'] || old('package_id') === (string) $p['id'] ? 'selected' : '' ?>>
              <?= e(ucfirst($p['type'])) ?> · <?= e($p['name']) ?> — ₦<?= e(naira((int) $p['price_kobo'])) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?= $err('package_id') ?>
      </div>

      <div class="field<?= $cls('company_name') ?>">
        <label>Company / organisation *</label>
        <input type="text" name="company_name" value="<?= old('company_name') ?>" required>
        <?= $err('company_name') ?>
      </div>

      <div class="field-row">
        <div class="field<?= $cls('contact_name') ?>">
          <label>Contact name *</label>
          <input type="text" name="contact_name" value="<?= old('contact_name') ?>" autocomplete="name" required>
          <?= $err('contact_name') ?>
        </div>
        <div class="field<?= $cls('phone') ?>">
          <label>Phone *</label>
          <input type="tel" name="phone" value="<?= old('phone') ?>" autocomplete="tel" required>
          <?= $err('phone') ?>
        </div>
      </div>

      <div class="field<?= $cls('email') ?>">
        <label>Email *</label>
        <input type="email" name="email" value="<?= old('email') ?>" autocomplete="email" required>
        <?= $err('email') ?>
      </div>

      <div class="field<?= $cls('logo') ?>">
        <label>Company logo (optional — JPG/PNG/SVG, max 4 MB)</label>
        <input type="file" name="logo" accept="image/*">
        <?= $err('logo') ?>
      </div>

      <div class="field">
        <label>Message (optional)</label>
        <textarea name="message" rows="4" placeholder="Anything we should know about your objectives?"><?= old('message') ?></textarea>
      </div>

      <button class="btn btn-gold btn-block" type="submit">Submit application <span class="arrow">→</span></button>
    </form>
  </div>
</section>
