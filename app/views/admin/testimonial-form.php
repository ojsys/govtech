<?php
/** @var \App\Core\View $this */
$this->layout('layouts/admin');
$t = $testimonial ?? [];
$isEdit = !empty($t['id']);
$action = $isEdit ? url('/admin/testimonials/' . (int) $t['id']) : url('/admin/testimonials');
$err = fn(string $f) => isset($errors[$f]) ? '<div class="err">' . e($errors[$f]) . '</div>' : '';
?>
<div style="margin-bottom:18px"><a class="btn btn-ghost btn-sm" href="<?= e(url('/admin/testimonials')) ?>">← Testimonials</a></div>
<div class="panel" style="max-width:680px">
  <div class="ph"><h2><?= $isEdit ? 'Edit testimonial' : 'Add testimonial' ?></h2></div>
  <div class="pb">
    <form method="post" action="<?= e($action) ?>">
      <input type="hidden" name="_token" value="<?= e($csrf) ?>">
      <?php if ($isEdit): ?><input type="hidden" name="_method" value="PUT"><?php endif; ?>
      <div class="field-row">
        <div class="field"><label>Name *</label><input type="text" name="name" value="<?= e($t['name'] ?? '') ?>" required><?= $err('name') ?></div>
        <div class="field"><label>Role / organisation</label><input type="text" name="role" value="<?= e($t['role'] ?? '') ?>"></div>
      </div>
      <div class="field"><label>Quote *</label><textarea name="quote" rows="4" required><?= e($t['quote'] ?? '') ?></textarea><?= $err('quote') ?></div>
      <div class="field" style="width:120px"><label>Sort order</label><input type="number" name="sort" value="<?= (int) ($t['sort'] ?? 0) ?>"></div>
      <div class="form-actions">
        <button class="btn btn-gold" type="submit"><?= $isEdit ? 'Save changes' : 'Add testimonial' ?></button>
        <a class="btn btn-ghost" href="<?= e(url('/admin/testimonials')) ?>">Cancel</a>
      </div>
    </form>
  </div>
</div>
