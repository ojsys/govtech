<?php
/** @var \App\Core\View $this */
$this->layout('layouts/admin');
$p = $package ?? [];
$raw = $raw ?? [];
$isEdit = !empty($p['id']);
$action = $isEdit ? url('/admin/packages/' . (int) $p['id']) : url('/admin/packages');
$err = fn(string $f) => isset($errors[$f]) ? '<div class="err">' . e($errors[$f]) . '</div>' : '';
$v = fn(string $k, $d = '') => $raw[$k] ?? $p[$k] ?? $d;
$price = $raw['price'] ?? ($p['price'] ?? naira_input($p['price_kobo'] ?? 0));
$perks = $raw['perks'] ?? ($p['perks'] ?? perks_to_lines($p['perks_json'] ?? '[]'));
$type = $raw['type'] ?? $p['type'] ?? 'sponsor';
$active = (int) ($raw['is_active'] ?? $p['is_active'] ?? 1);
?>
<div style="margin-bottom:18px"><a class="btn btn-ghost btn-sm" href="<?= e(url('/admin/packages')) ?>">← Packages</a></div>
<div class="panel" style="max-width:720px">
  <div class="ph"><h2><?= $isEdit ? 'Edit package' : 'New package' ?></h2></div>
  <div class="pb">
    <form method="post" action="<?= e($action) ?>">
      <input type="hidden" name="_token" value="<?= e($csrf) ?>">
      <?php if ($isEdit): ?><input type="hidden" name="_method" value="PUT"><?php endif; ?>

      <div class="field-row">
        <div class="field">
          <label>Type *</label>
          <select name="type">
            <option value="sponsor" <?= $type === 'sponsor' ? 'selected' : '' ?>>Sponsor tier</option>
            <option value="exhibition" <?= $type === 'exhibition' ? 'selected' : '' ?>>Exhibition booth</option>
          </select>
          <?= $err('type') ?>
        </div>
        <div class="field"><label>Name *</label><input type="text" name="name" value="<?= e($v('name')) ?>" required><?= $err('name') ?></div>
      </div>
      <div class="field-row">
        <div class="field"><label>Price (₦) *</label><input type="text" name="price" value="<?= e($price) ?>" inputmode="numeric" placeholder="2000000" required><?= $err('price') ?></div>
        <div class="field"><label>Comp delegate passes</label><input type="number" name="comp_passes" value="<?= e($v('comp_passes', 0)) ?>" min="0"></div>
      </div>
      <div class="field-row">
        <div class="field"><label>Booth size (exhibition only)</label><input type="text" name="booth_size" value="<?= e($v('booth_size')) ?>" placeholder="e.g. 6sqm"></div>
        <div class="field" style="display:flex;align-items:flex-end"><label class="check"><input type="checkbox" name="is_active" value="1" <?= $active ? 'checked' : '' ?>> Active (shown on site)</label></div>
      </div>
      <div class="field">
        <label>Perks (one per line)</label>
        <textarea name="perks" rows="4" placeholder="2-page brochure advert&#10;Ad &amp; logo on digital screens&#10;Premier exhibition placement"><?= e($perks) ?></textarea>
      </div>
      <div class="field" style="width:120px"><label>Sort order</label><input type="number" name="sort" value="<?= e($v('sort', 0)) ?>"></div>

      <div class="form-actions">
        <button class="btn btn-gold" type="submit"><?= $isEdit ? 'Save changes' : 'Create package' ?></button>
        <a class="btn btn-ghost" href="<?= e(url('/admin/packages')) ?>">Cancel</a>
      </div>
    </form>
  </div>
</div>
