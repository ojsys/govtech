<?php
/** @var \App\Core\View $this */
$this->layout('layouts/admin');
$statuses = ['' => 'All statuses', 'pending' => 'Pending', 'approved' => 'Approved', 'shortlisted' => 'Shortlisted', 'rejected' => 'Rejected'];
?>
<div style="margin-bottom:18px"><a class="btn btn-ghost btn-sm" href="<?= e(url('/admin/awards')) ?>">← Awards</a></div>

<div class="panel">
  <div class="ph">
    <h2><?= count($nominations) ?> nomination<?= count($nominations) === 1 ? '' : 's' ?></h2>
    <form method="get" action="<?= e(url('/admin/awards/nominations')) ?>" style="display:flex;gap:8px">
      <select name="category" onchange="this.form.submit()" style="padding:8px 12px;background:var(--ink);border:1px solid var(--line);border-radius:3px;color:#E8EFE9;font-family:var(--body)">
        <option value="0">All categories</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= (int) $c['id'] ?>" <?= $category === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['title']) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="status" onchange="this.form.submit()" style="padding:8px 12px;background:var(--ink);border:1px solid var(--line);border-radius:3px;color:#E8EFE9;font-family:var(--body)">
        <?php foreach ($statuses as $val => $lbl): ?>
          <option value="<?= e($val) ?>" <?= $status === $val ? 'selected' : '' ?>><?= e($lbl) ?></option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>
  <div class="pb" style="padding-top:0">
    <table class="table">
      <thead><tr><th>Nominee</th><th>Category</th><th>Nominator</th><th>Votes</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php if (!$nominations): ?><tr><td colspan="6" class="muted" style="padding:20px 12px">No nominations match.</td></tr><?php endif; ?>
      <?php foreach ($nominations as $n): ?>
        <tr>
          <td>
            <b><?= e($n['nominee_name']) ?></b>
            <?php if (!empty($n['nominee_org'])): ?><br><span class="muted"><?= e($n['nominee_org']) ?></span><?php endif; ?>
            <?php if (!empty($n['justification'])): ?><br><span class="muted" style="font-size:12.5px"><?= e(mb_strimwidth($n['justification'], 0, 90, '…')) ?></span><?php endif; ?>
          </td>
          <td class="muted"><?= e($n['category_title']) ?></td>
          <td class="muted"><?= e($n['nominator_name']) ?><br><span style="font-size:12px"><?= e($n['nominator_email']) ?></span></td>
          <td class="num"><?= (int) $n['votes_count'] ?></td>
          <td><span class="tag <?= $n['status'] === 'shortlisted' || $n['status'] === 'approved' ? 'paid' : ($n['status'] === 'rejected' ? 'failed' : 'pending') ?>"><?= e($n['status']) ?></span></td>
          <td style="white-space:nowrap">
            <?php foreach (['shortlisted' => 'Shortlist', 'approved' => 'Approve', 'rejected' => 'Reject', 'pending' => 'Reset'] as $st => $lbl):
              if ($n['status'] === $st) continue; ?>
              <form method="post" action="<?= e(url('/admin/awards/nominations/' . (int) $n['id'] . '/status')) ?>" style="display:inline">
                <input type="hidden" name="_token" value="<?= e($csrf) ?>">
                <input type="hidden" name="status" value="<?= e($st) ?>">
                <input type="hidden" name="category" value="<?= (int) $category ?>">
                <button class="btn btn-ghost btn-sm" type="submit"><?= e($lbl) ?></button>
              </form>
            <?php endforeach; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
