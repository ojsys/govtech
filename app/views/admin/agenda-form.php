<?php
/** @var \App\Core\View $this */
$this->layout('layouts/admin');
$s = $session ?? [];
$days = $days ?? [];
$isEdit = !empty($s['id']);
$isNew = !$isEdit && empty($s);   // fresh create (no submitted data)
$action = $isEdit ? url('/admin/agenda/' . (int) $s['id']) : url('/admin/agenda');
$err = fn(string $f) => isset($errors[$f]) ? '<div class="err">' . e($errors[$f]) . '</div>' : '';
// Publish defaults ON for a brand-new session; otherwise reflect stored/submitted value.
$published = $isNew ? true : !empty($s['is_published']);
?>
<div style="margin-bottom:18px"><a class="btn btn-ghost btn-sm" href="<?= e(url('/admin/agenda')) ?>">← Agenda</a></div>

<div class="panel" style="max-width:720px">
  <div class="ph"><h2><?= $isEdit ? 'Edit session' : 'Add session' ?></h2></div>
  <div class="pb">
    <form method="post" action="<?= e($action) ?>">
      <input type="hidden" name="_token" value="<?= e($csrf) ?>">
      <?php if ($isEdit): ?><input type="hidden" name="_method" value="PUT"><?php endif; ?>

      <div class="field">
        <label>Day *</label>
        <input type="text" name="day_label" value="<?= e($s['day_label'] ?? '') ?>" list="dayList" placeholder="Day 1 · Tue, 7 Oct" required>
        <datalist id="dayList"><?php foreach ($days as $d): ?><option value="<?= e($d) ?>"><?php endforeach; ?></datalist>
        <?= $err('day_label') ?>
      </div>

      <div class="field-row">
        <div class="field"><label>Start time</label><input type="text" name="start_time" value="<?= e($s['start_time'] ?? '') ?>" placeholder="09:00"></div>
        <div class="field"><label>End time</label><input type="text" name="end_time" value="<?= e($s['end_time'] ?? '') ?>" placeholder="10:30"></div>
      </div>

      <div class="field">
        <label>Title *</label>
        <input type="text" name="title" value="<?= e($s['title'] ?? '') ?>" required>
        <?= $err('title') ?>
      </div>

      <div class="field">
        <label>Description</label>
        <textarea name="description"><?= e($s['description'] ?? '') ?></textarea>
      </div>

      <div class="field-row">
        <div class="field"><label>Speaker / facilitator</label><input type="text" name="speaker" value="<?= e($s['speaker'] ?? '') ?>"></div>
        <div class="field"><label>Location / room</label><input type="text" name="location" value="<?= e($s['location'] ?? '') ?>"></div>
      </div>

      <div class="field-row">
        <div class="field"><label>Track (optional)</label><input type="text" name="track" value="<?= e($s['track'] ?? '') ?>" placeholder="e.g. Main Stage"></div>
        <div class="field"><label>Sort order</label><input type="number" name="sort" value="<?= (int) ($s['sort'] ?? 0) ?>"></div>
      </div>

      <div class="field">
        <label class="check"><input type="checkbox" name="is_break" value="1" <?= !empty($s['is_break']) ? 'checked' : '' ?>> This is a break / interlude (styled differently)</label>
      </div>
      <div class="field">
        <label class="check"><input type="checkbox" name="is_published" value="1" <?= $published ? 'checked' : '' ?>> Published (visible on the public agenda)</label>
      </div>

      <div class="form-actions">
        <button class="btn btn-gold" type="submit"><?= $isEdit ? 'Save changes' : 'Add session' ?></button>
        <a class="btn btn-ghost" href="<?= e(url('/admin/agenda')) ?>">Cancel</a>
      </div>
    </form>
  </div>
</div>
