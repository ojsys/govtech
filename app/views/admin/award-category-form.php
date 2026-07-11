<?php
/** @var \App\Core\View $this */
$this->layout('layouts/admin');
$c = $category ?? [];
$err = fn(string $f) => isset($errors[$f]) ? '<div class="err">' . e($errors[$f]) . '</div>' : '';
?>
<div style="margin-bottom:18px"><a class="btn btn-ghost btn-sm" href="<?= e(url('/admin/awards')) ?>">← Awards</a></div>
<div class="panel" style="max-width:680px">
  <div class="ph"><h2>Edit category</h2></div>
  <div class="pb">
    <form method="post" action="<?= e(url('/admin/awards/categories/' . (int) $c['id'])) ?>">
      <input type="hidden" name="_token" value="<?= e($csrf) ?>">
      <input type="hidden" name="_method" value="PUT">
      <div class="field"><label>Title *</label><input type="text" name="title" value="<?= e($c['title'] ?? '') ?>" required><?= $err('title') ?></div>
      <div class="field"><label>Description</label><textarea name="description" rows="3"><?= e($c['description'] ?? '') ?></textarea></div>
      <div class="field" style="width:120px"><label>Sort order</label><input type="number" name="sort" value="<?= (int) ($c['sort'] ?? 0) ?>"></div>
      <div class="form-actions">
        <button class="btn btn-gold" type="submit">Save changes</button>
        <a class="btn btn-ghost" href="<?= e(url('/admin/awards')) ?>">Cancel</a>
      </div>
    </form>
  </div>
</div>
