<?php
/** @var \App\Core\View $this */
$this->layout('layouts/admin');
?>
<div class="panel" style="max-width:680px">
  <div class="ph"><h2>Site settings</h2></div>
  <div class="pb">
    <form method="post" action="<?= e(url('/admin/settings')) ?>">
      <input type="hidden" name="_token" value="<?= e($csrf) ?>">
      <?php foreach ($keys as $key => $label): ?>
        <div class="field">
          <label><?= e($label) ?></label>
          <input type="text" name="<?= e($key) ?>" value="<?= e($values[$key] ?? '') ?>">
        </div>
      <?php endforeach; ?>
      <div class="form-actions">
        <button class="btn btn-gold" type="submit">Save settings</button>
      </div>
    </form>
  </div>
</div>
