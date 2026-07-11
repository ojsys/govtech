<?php
/** @var \App\Core\View $this */
$this->layout('layouts/admin');
?>
<div class="panel">
  <div class="ph">
    <h2><?= count($testimonials) ?> testimonial<?= count($testimonials) === 1 ? '' : 's' ?></h2>
    <a class="btn btn-gold btn-sm" href="<?= e(url('/admin/testimonials/create')) ?>">+ Add testimonial</a>
  </div>
  <div class="pb" style="padding-top:0">
    <table class="table">
      <thead><tr><th>Name</th><th>Role</th><th>Quote</th><th>Sort</th><th></th></tr></thead>
      <tbody>
      <?php if (!$testimonials): ?>
        <tr><td colspan="5" class="muted" style="padding:20px 12px">No testimonials yet — the home page is showing placeholder quotes until you add some.</td></tr>
      <?php endif; ?>
      <?php foreach ($testimonials as $t): ?>
        <tr>
          <td><b><?= e($t['name']) ?></b></td>
          <td class="muted"><?= e($t['role'] ?? '') ?></td>
          <td class="muted"><?= e(mb_strimwidth($t['quote'] ?? '', 0, 80, '…')) ?></td>
          <td class="num"><?= (int) $t['sort'] ?></td>
          <td style="text-align:right;white-space:nowrap">
            <a class="btn btn-ghost btn-sm" href="<?= e(url('/admin/testimonials/' . (int) $t['id'] . '/edit')) ?>">Edit</a>
            <form method="post" action="<?= e(url('/admin/testimonials/' . (int) $t['id'] . '/delete')) ?>" style="display:inline" onsubmit="return confirm('Remove this testimonial?')">
              <input type="hidden" name="_token" value="<?= e($csrf) ?>">
              <button class="btn btn-danger btn-sm" type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
