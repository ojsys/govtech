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
      <span class="eyebrow">The Nigeria GovTech Awards</span>
      <h1>Submit a nomination.</h1>
      <p>Nominate an individual or organisation making a real impact in public-sector technology. Submissions are reviewed before they appear on the shortlist.</p>
    </div>

    <?php if ($m = flash('ok')): ?><div class="alert alert-ok" style="margin-top:24px"><?= e($m) ?></div><?php endif; ?>
    <?php if ($m = flash('error')): ?><div class="alert alert-error" style="margin-top:24px"><?= e($m) ?></div><?php endif; ?>

    <form method="post" action="<?= e(url('/awards/nominate')) ?>" class="card reveal" style="margin-top:30px">
      <input type="hidden" name="_token" value="<?= e($csrf) ?>">
      <div class="hp" aria-hidden="true"><label>Leave empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

      <div class="field<?= $cls('category_id') ?>">
        <label>Award category</label>
        <select name="category_id" required>
          <option value="">— choose a category —</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= (int) $c['id'] ?>" <?= old('category_id') === (string) $c['id'] ? 'selected' : '' ?>><?= e($c['title']) ?></option>
          <?php endforeach; ?>
        </select>
        <?= $err('category_id') ?>
      </div>

      <div class="field-row">
        <div class="field<?= $cls('nominee_name') ?>">
          <label>Nominee name *</label>
          <input type="text" name="nominee_name" value="<?= old('nominee_name') ?>" required>
          <?= $err('nominee_name') ?>
        </div>
        <div class="field">
          <label>Nominee organisation</label>
          <input type="text" name="nominee_org" value="<?= old('nominee_org') ?>">
        </div>
      </div>

      <div class="field<?= $cls('nominee_email') ?>">
        <label>Nominee email (optional)</label>
        <input type="email" name="nominee_email" value="<?= old('nominee_email') ?>">
        <?= $err('nominee_email') ?>
      </div>

      <div class="field<?= $cls('justification') ?>">
        <label>Why are you nominating them? *</label>
        <textarea name="justification" rows="5" placeholder="Describe their impact and achievements over the past 12 months."><?= old('justification') ?></textarea>
        <?= $err('justification') ?>
      </div>

      <div class="field-row">
        <div class="field<?= $cls('nominator_name') ?>">
          <label>Your name *</label>
          <input type="text" name="nominator_name" value="<?= old('nominator_name') ?>" required>
          <?= $err('nominator_name') ?>
        </div>
        <div class="field<?= $cls('nominator_email') ?>">
          <label>Your email *</label>
          <input type="email" name="nominator_email" value="<?= old('nominator_email') ?>" required>
          <?= $err('nominator_email') ?>
        </div>
      </div>

      <button class="btn btn-gold btn-block" type="submit">Submit nomination <span class="arrow">→</span></button>
    </form>
  </div>
</section>
