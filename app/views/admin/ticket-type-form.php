<?php
/** @var \App\Core\View $this */
$this->layout('layouts/admin');
$t = $ticket ?? [];
$raw = $raw ?? [];
$isEdit = !empty($t['id']);
$action = $isEdit ? url('/admin/ticket-types/' . (int) $t['id']) : url('/admin/ticket-types');
$err = fn(string $f) => isset($errors[$f]) ? '<div class="err">' . e($errors[$f]) . '</div>' : '';
// Resolve a field from re-submitted raw, else the DB row.
$v = fn(string $k, $d = '') => $raw[$k] ?? $t[$k] ?? $d;
$price = $raw['price'] ?? ($t['price'] ?? naira_input($t['price_kobo'] ?? 0));
$perks = $raw['perks'] ?? ($t['perks'] ?? perks_to_lines($t['perks_json'] ?? '[]'));
$featured = (int) ($raw['featured'] ?? $t['featured'] ?? 0);
$active = (int) ($raw['is_active'] ?? $t['is_active'] ?? 1);
?>
<div style="margin-bottom:18px"><a class="btn btn-ghost btn-sm" href="<?= e(url('/admin/ticket-types')) ?>">← Ticket types</a></div>
<div class="panel" style="max-width:720px">
  <div class="ph"><h2><?= $isEdit ? 'Edit ticket type' : 'New ticket type' ?></h2></div>
  <div class="pb">
    <form method="post" action="<?= e($action) ?>">
      <input type="hidden" name="_token" value="<?= e($csrf) ?>">
      <?php if ($isEdit): ?><input type="hidden" name="_method" value="PUT"><?php endif; ?>

      <div class="field-row">
        <div class="field"><label>Name *</label><input type="text" name="name" value="<?= e($v('name')) ?>" required><?= $err('name') ?></div>
        <div class="field"><label>Slug (auto if blank)</label><input type="text" name="slug" value="<?= e($v('slug')) ?>" placeholder="e.g. standard"></div>
      </div>
      <div class="field-row">
        <div class="field"><label>Price (₦) *</label><input type="text" name="price" value="<?= e($price) ?>" inputmode="numeric" placeholder="75000" required><?= $err('price') ?></div>
        <div class="field"><label>Short description</label><input type="text" name="description" value="<?= e($v('description')) ?>" placeholder="per delegate"></div>
      </div>
      <div class="field">
        <label>Perks (one per line)</label>
        <textarea name="perks" rows="4" placeholder="In-person, both days&#10;Lunch &amp; networking breaks&#10;Awards gala access"><?= e($perks) ?></textarea>
      </div>
      <div class="field-row">
        <div class="field"><label>Group size (seats per pass)</label><input type="number" name="group_size" value="<?= e($v('group_size', 1)) ?>" min="1"></div>
        <div class="field"><label>Quota (blank = unlimited)</label><input type="number" name="quota" value="<?= e($raw['quota'] ?? ($t['quota'] ?? '')) ?>" min="0"></div>
      </div>
      <div class="field-row">
        <div class="field" style="display:flex;align-items:flex-end"><label class="check"><input type="checkbox" name="featured" value="1" <?= $featured ? 'checked' : '' ?>> Featured (“Most popular”)</label></div>
        <div class="field" style="display:flex;align-items:flex-end"><label class="check"><input type="checkbox" name="is_active" value="1" <?= $active ? 'checked' : '' ?>> Active (shown on site)</label></div>
      </div>
      <div class="field" style="width:120px"><label>Sort order</label><input type="number" name="sort" value="<?= e($v('sort', 0)) ?>"></div>

      <div class="form-actions">
        <button class="btn btn-gold" type="submit"><?= $isEdit ? 'Save changes' : 'Create ticket type' ?></button>
        <a class="btn btn-ghost" href="<?= e(url('/admin/ticket-types')) ?>">Cancel</a>
      </div>
    </form>
  </div>
</div>
