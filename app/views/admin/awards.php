<?php
/** @var \App\Core\View $this */
$this->layout('layouts/admin');
?>
<div class="stat-grid" style="grid-template-columns:repeat(4,1fr)">
  <div class="scard"><div class="k">Pending review</div><div class="v"><?= (int) $counts['pending'] ?></div></div>
  <div class="scard"><div class="k">Shortlisted</div><div class="v"><?= (int) $counts['shortlisted'] ?></div></div>
  <div class="scard"><div class="k">Votes cast</div><div class="v"><?= (int) $voteStats['cast'] ?></div></div>
  <div class="scard"><div class="k">Votes verified</div><div class="v"><?= (int) $voteStats['verified'] ?></div></div>
</div>

<div class="panel">
  <div class="ph">
    <h2>Categories</h2>
    <a class="btn btn-ghost btn-sm" href="<?= e(url('/admin/awards/nominations')) ?>">Moderate nominations</a>
  </div>
  <div class="pb" style="padding-top:0">
    <table class="table">
      <thead><tr><th>Category</th><th>Nominations</th><th>Shortlisted</th><th>Active</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($categories as $c): ?>
        <tr>
          <td><?= e($c['title']) ?></td>
          <td class="num"><a style="color:var(--gold-soft)" href="<?= e(url('/admin/awards/nominations?category=' . (int) $c['id'])) ?>"><?= (int) $c['nominations'] ?></a></td>
          <td class="num"><?= (int) $c['shortlisted'] ?></td>
          <td><?= (int) $c['is_active'] ? '<span class="tag paid">Active</span>' : '<span class="tag">Hidden</span>' ?></td>
          <td style="text-align:right">
            <form method="post" action="<?= e(url('/admin/awards/categories/' . (int) $c['id'] . '/toggle')) ?>" style="display:inline">
              <input type="hidden" name="_token" value="<?= e($csrf) ?>">
              <button class="btn btn-ghost btn-sm" type="submit"><?= (int) $c['is_active'] ? 'Hide' : 'Show' ?></button>
            </form>
            <a class="btn btn-ghost btn-sm" href="<?= e(url('/admin/awards/categories/' . (int) $c['id'] . '/edit')) ?>">Edit</a>
            <form method="post" action="<?= e(url('/admin/awards/categories/' . (int) $c['id'] . '/delete')) ?>" style="display:inline" onsubmit="return confirm('Delete this category?')">
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

<div class="panel" style="max-width:680px">
  <div class="ph"><h2>Add a category</h2></div>
  <div class="pb">
    <form method="post" action="<?= e(url('/admin/awards/categories')) ?>">
      <input type="hidden" name="_token" value="<?= e($csrf) ?>">
      <div class="field"><label>Title *</label><input type="text" name="title" required></div>
      <div class="field"><label>Description</label><textarea name="description"></textarea></div>
      <div class="field-row">
        <div class="field"><label>Sort</label><input type="number" name="sort" value="0"></div>
        <div class="field" style="display:flex;align-items:flex-end"><label class="check"><input type="checkbox" name="is_active" value="1" checked> Active</label></div>
      </div>
      <button class="btn btn-gold" type="submit">Add category</button>
    </form>
  </div>
</div>
