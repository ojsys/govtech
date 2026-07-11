<?php
/** @var \App\Core\View $this */
use App\Models\Speaker;
$this->layout('layouts/admin');
?>
<div class="panel">
  <div class="ph">
    <h2><?= count($speakers) ?> speaker<?= count($speakers) === 1 ? '' : 's' ?></h2>
    <a class="btn btn-gold btn-sm" href="<?= e(url('/admin/speakers/create')) ?>">+ Add speaker</a>
  </div>
  <div class="pb" style="padding-top:0">
    <table class="table">
      <thead><tr><th>Photo</th><th>Name</th><th>Role</th><th>Featured</th><th>Sort</th><th></th></tr></thead>
      <tbody>
      <?php if (!$speakers): ?><tr><td colspan="6" class="muted" style="padding:20px 12px">No speakers yet.</td></tr><?php endif; ?>
      <?php foreach ($speakers as $s):
        $photo = Speaker::photoUrl($s['photo'] ?? '');
      ?>
        <tr>
          <td>
            <?php if ($photo): ?>
              <img src="<?= e($photo) ?>" alt="" style="width:40px;height:40px;border-radius:50%;object-fit:cover" onerror="this.style.display='none'">
            <?php else: ?>
              <span class="mono muted"><?= e(Speaker::initials($s['name'])) ?></span>
            <?php endif; ?>
          </td>
          <td><?= e($s['name']) ?></td>
          <td class="muted"><?= e(trim(($s['role'] ?? '') . (!empty($s['organization']) ? ', ' . $s['organization'] : ''), ', ')) ?></td>
          <td><?= !empty($s['featured']) ? '<span class="tag paid">Yes</span>' : '<span class="tag">No</span>' ?></td>
          <td class="num"><?= (int) $s['sort'] ?></td>
          <td style="text-align:right;white-space:nowrap">
            <a class="btn btn-ghost btn-sm" href="<?= e(url('/admin/speakers/' . (int) $s['id'] . '/edit')) ?>">Edit</a>
            <form method="post" action="<?= e(url('/admin/speakers/' . (int) $s['id'] . '/delete')) ?>" style="display:inline" onsubmit="return confirm('Remove this speaker?')">
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
