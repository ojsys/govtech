<?php
/** @var \App\Core\View $this */
use App\Models\Speaker;
$this->layout('layouts/admin');
$s = $speaker ?? [];
$isEdit = !empty($s['id']);
$action = $isEdit ? url('/admin/speakers/' . (int) $s['id']) : url('/admin/speakers');
$err = fn(string $f) => isset($errors[$f]) ? '<div class="err">' . e($errors[$f]) . '</div>' : '';
$photo = Speaker::photoUrl($s['photo'] ?? '');
?>
<div style="margin-bottom:18px"><a class="btn btn-ghost btn-sm" href="<?= e(url('/admin/speakers')) ?>">← Speakers</a></div>

<div class="panel" style="max-width:680px">
  <div class="ph"><h2><?= $isEdit ? 'Edit speaker' : 'Add speaker' ?></h2></div>
  <div class="pb">
    <form method="post" action="<?= e($action) ?>" enctype="multipart/form-data">
      <input type="hidden" name="_token" value="<?= e($csrf) ?>">
      <?php if ($isEdit): ?><input type="hidden" name="_method" value="PUT"><?php endif; ?>

      <div class="field">
        <label>Name *</label>
        <input type="text" name="name" value="<?= e($s['name'] ?? '') ?>" required>
        <?= $err('name') ?>
      </div>
      <div class="field-row">
        <div class="field"><label>Role</label><input type="text" name="role" value="<?= e($s['role'] ?? '') ?>"></div>
        <div class="field"><label>Organization</label><input type="text" name="organization" value="<?= e($s['organization'] ?? '') ?>"></div>
      </div>
      <div class="field">
        <label>Bio</label>
        <textarea name="bio"><?= e($s['bio'] ?? '') ?></textarea>
      </div>
      <div class="field-row">
        <div class="field">
          <label>Photo <?= $isEdit ? '(leave empty to keep current)' : '' ?></label>
          <input type="file" name="photo" accept="image/*">
          <?= $err('photo') ?>
          <?php if ($photo): ?><div style="margin-top:10px"><img src="<?= e($photo) ?>" alt="" style="width:54px;height:54px;border-radius:6px;object-fit:cover"></div><?php endif; ?>
        </div>
        <div class="field"><label>Sort order</label><input type="number" name="sort" value="<?= (int) ($s['sort'] ?? 0) ?>"></div>
      </div>
      <div class="field">
        <label class="check"><input type="checkbox" name="featured" value="1" <?= !empty($s['featured']) ? 'checked' : '' ?>> Featured speaker</label>
      </div>
      <div class="form-actions">
        <button class="btn btn-gold" type="submit"><?= $isEdit ? 'Save changes' : 'Add speaker' ?></button>
        <a class="btn btn-ghost" href="<?= e(url('/admin/speakers')) ?>">Cancel</a>
      </div>
    </form>
  </div>
</div>
