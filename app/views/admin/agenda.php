<?php
/** @var \App\Core\View $this */
$this->layout('layouts/admin');
$sessions = $sessions ?? [];
?>
<div class="panel">
  <div class="ph">
    <h2><?= count($sessions) ?> session<?= count($sessions) === 1 ? '' : 's' ?></h2>
    <a class="btn btn-gold btn-sm" href="<?= e(url('/admin/agenda/create')) ?>">+ Add session</a>
  </div>
  <div class="pb" style="padding-top:0">
    <table class="table">
      <thead><tr><th>Day</th><th>Time</th><th>Session</th><th>Published</th><th>Sort</th><th></th></tr></thead>
      <tbody>
      <?php if (!$sessions): ?><tr><td colspan="6" class="muted" style="padding:20px 12px">No sessions yet. Add the programme running order here.</td></tr><?php endif; ?>
      <?php foreach ($sessions as $s):
        $time = trim(($s['start_time'] ?? '') . (!empty($s['end_time']) ? ' – ' . $s['end_time'] : ''));
      ?>
        <tr>
          <td class="muted"><?= e($s['day_label'] ?? '') ?></td>
          <td class="mono" style="white-space:nowrap"><?= e($time) ?></td>
          <td>
            <?= e($s['title']) ?>
            <?= !empty($s['is_break']) ? ' <span class="tag">Break</span>' : '' ?>
            <?php if (!empty($s['speaker'])): ?><div class="muted" style="font-size:12px"><?= e($s['speaker']) ?></div><?php endif; ?>
          </td>
          <td><?= !empty($s['is_published']) ? '<span class="tag paid">Yes</span>' : '<span class="tag">Hidden</span>' ?></td>
          <td class="num"><?= (int) $s['sort'] ?></td>
          <td style="text-align:right;white-space:nowrap">
            <a class="btn btn-ghost btn-sm" href="<?= e(url('/admin/agenda/' . (int) $s['id'] . '/edit')) ?>">Edit</a>
            <form method="post" action="<?= e(url('/admin/agenda/' . (int) $s['id'] . '/delete')) ?>" style="display:inline" onsubmit="return confirm('Remove this session?')">
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
