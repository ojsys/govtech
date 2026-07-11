<?php
/** @var \App\Core\View $this */
$this->layout('layouts/admin');
$resolve = function (string $img): string {
    if (preg_match('#^https?://#', $img)) {
        return $img;
    }
    return rtrim((string) \Config::get('app.uploads_url', '/uploads'), '/') . '/' . ltrim($img, '/');
};
?>
<div class="panel" style="max-width:680px">
  <div class="ph"><h2>Add photos</h2></div>
  <div class="pb">
    <p class="muted" style="color:var(--sage);margin-bottom:16px">Upload key pictures from previous events. They appear in the gallery section on the home page (and the About page). You can select multiple images at once.</p>
    <form method="post" action="<?= e(url('/admin/gallery')) ?>" enctype="multipart/form-data">
      <input type="hidden" name="_token" value="<?= e($csrf) ?>">
      <div class="field-row">
        <div class="field">
          <label>Images (JPG/PNG/WEBP, max 4 MB each)</label>
          <input type="file" name="images[]" accept="image/*" multiple required>
        </div>
        <div class="field">
          <label>Caption (optional, applied to all)</label>
          <input type="text" name="caption" placeholder="e.g. Opening keynote, 2024">
        </div>
      </div>
      <button class="btn btn-gold" type="submit">Upload to gallery</button>
    </form>
  </div>
</div>

<div class="panel">
  <div class="ph"><h2><?= count($images) ?> image<?= count($images) === 1 ? '' : 's' ?> in the gallery</h2></div>
  <div class="pb">
    <?php if (!$images): ?>
      <p class="muted" style="color:var(--sage);padding:8px 0">No images yet — the home page is showing placeholder photos until you add some here.</p>
    <?php else: ?>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px">
        <?php foreach ($images as $g): ?>
          <div style="border:1px solid var(--line);border-radius:6px;overflow:hidden;background:var(--ink-3)">
            <img src="<?= e($resolve($g['image'])) ?>" alt="" style="width:100%;height:140px;object-fit:cover;display:block" onerror="this.style.opacity=.3">
            <form method="post" action="<?= e(url('/admin/gallery/' . (int) $g['id'])) ?>" style="padding:12px">
              <input type="hidden" name="_token" value="<?= e($csrf) ?>">
              <div class="field" style="margin-bottom:10px">
                <label>Caption</label>
                <input type="text" name="caption" value="<?= e($g['caption'] ?? '') ?>">
              </div>
              <div style="display:flex;gap:8px;align-items:flex-end">
                <div class="field" style="margin-bottom:0;width:78px">
                  <label>Order</label>
                  <input type="number" name="sort" value="<?= (int) $g['sort'] ?>">
                </div>
                <button class="btn btn-ghost btn-sm" type="submit">Save</button>
              </div>
            </form>
            <form method="post" action="<?= e(url('/admin/gallery/' . (int) $g['id'] . '/delete')) ?>" style="padding:0 12px 12px" onsubmit="return confirm('Remove this image?')">
              <input type="hidden" name="_token" value="<?= e($csrf) ?>">
              <button class="btn btn-danger btn-sm" type="submit" style="width:100%">Delete</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
