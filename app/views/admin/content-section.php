<?php
/** @var \App\Core\View $this */
use App\Models\Content;
$this->layout('layouts/admin');
?>
<div style="margin-bottom:18px"><a class="btn btn-ghost btn-sm" href="<?= e(url('/admin/content')) ?>">← Content</a></div>

<div class="panel" style="max-width:760px">
  <div class="ph"><h2><?= e($section['title']) ?></h2></div>
  <div class="pb">
    <?php if (!empty($section['intro'])): ?><p class="muted" style="color:var(--sage);margin-bottom:18px"><?= e($section['intro']) ?></p><?php endif; ?>
    <form method="post" action="<?= e(url('/admin/content/' . $slug)) ?>" enctype="multipart/form-data">
      <input type="hidden" name="_token" value="<?= e($csrf) ?>">
      <?php foreach ($section['fields'] as $f):
        $key = $f['key'];
        $type = $f['type'] ?? 'text';
        $val = Content::get($key);
      ?>
        <div class="field">
          <label><?= e($f['label']) ?></label>
          <?php if ($type === 'textarea'): ?>
            <textarea name="<?= e($key) ?>" rows="3"><?= e($val) ?></textarea>
          <?php elseif ($type === 'image'):
            $img = Content::image($key);
          ?>
            <?php if ($img): ?>
              <div style="display:flex;align-items:center;gap:14px;margin-bottom:8px">
                <img src="<?= e($img) ?>" alt="" style="height:48px;background:#fff;padding:5px;border-radius:5px">
                <label class="check" style="color:var(--sage);font-size:13px"><input type="checkbox" name="remove_<?= e($key) ?>" value="1"> Remove</label>
              </div>
            <?php endif; ?>
            <input type="file" name="<?= e($key) ?>" accept="image/*">
          <?php else: ?>
            <input type="text" name="<?= e($key) ?>" value="<?= e($val) ?>">
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
      <div class="form-actions">
        <button class="btn btn-gold" type="submit">Save changes</button>
        <a class="btn btn-ghost" href="<?= e(url('/')) ?>" target="_blank" rel="noopener">View site ↗</a>
      </div>
    </form>
  </div>
</div>
