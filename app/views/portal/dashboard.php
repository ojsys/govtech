<?php
/** @var \App\Core\View $this */
$this->layout('layouts/portal');
$uploadsUrl = rtrim((string) \Config::get('app.uploads_url', '/uploads'), '/');
$statusColor = ['approved' => 'var(--verdant)', 'rejected' => '#f0a3a0', 'pending' => 'var(--gold-soft)'];
$typeLabels = ['logo' => 'Hi-res logo', 'brochure_ad' => 'Brochure advert', 'screen_ad' => 'Screen advert'];
?>
<section class="section" style="padding-top:10px">
  <div class="wrap" style="max-width:980px">
    <div class="page-head reveal in" style="padding-top:0">
      <span class="eyebrow"><?= e(ucfirst($ctx['package_type'])) ?> · <?= e($ctx['package_name']) ?> Partner</span>
      <h1><?= e($ctx['company_name']) ?></h1>
      <p>Welcome to your sponsor portal. Here are your complimentary delegate passes and your branding asset uploads.</p>
    </div>

    <?php if (!empty($flashOk)): ?><div class="alert alert-ok" style="margin-top:20px"><?= e($flashOk) ?></div><?php endif; ?>
    <?php if (!empty($flashErr)): ?><div class="alert alert-error" style="margin-top:20px"><?= e($flashErr) ?></div><?php endif; ?>

    <!-- Complimentary passes -->
    <div class="reveal" style="margin-top:36px">
      <div class="sec-head" style="margin-bottom:20px"><span class="eyebrow">Complimentary passes</span><h2 style="font-size:26px"><?= count($compPasses) ?> delegate pass<?= count($compPasses) === 1 ? '' : 'es' ?></h2></div>
      <?php if (!$compPasses): ?>
        <div class="empty-note">No complimentary passes are attached yet. They appear here once your sponsorship is confirmed.</div>
      <?php else: ?>
        <?php foreach ($compPasses as $t): ?>
          <div class="ticket-card">
            <div class="qr"><img src="<?= e(url('/ticket/' . rawurlencode($t['ticket_code']) . '/qr.png')) ?>" alt="Scan to verify" loading="lazy"></div>
            <div class="meta">
              <div class="tt"><?= e($t['ticket_name'] ?? 'Delegate Pass') ?> · Complimentary</div>
              <h4><?= e($ctx['company_name']) ?></h4>
              <span class="code"><?= e($t['ticket_code']) ?></span>
              <div class="holder">Assign this pass to a team member at the door.</div>
            </div>
          </div>
        <?php endforeach; ?>
        <p class="muted" style="color:var(--sage);font-size:13.5px;margin-top:8px">Tip: each QR admits one delegate. Print or forward them to your team.</p>
      <?php endif; ?>
    </div>

    <!-- Asset uploads -->
    <div class="reveal" style="margin-top:50px">
      <div class="sec-head" style="margin-bottom:20px"><span class="eyebrow">Branding assets</span><h2 style="font-size:26px">Upload for review</h2></div>
      <div class="card">
        <form method="post" action="<?= e(url('/portal/assets')) ?>" enctype="multipart/form-data">
          <input type="hidden" name="_token" value="<?= e($csrf) ?>">
          <div class="field-row">
            <div class="field">
              <label>Asset type</label>
              <select name="type" required>
                <option value="logo">Hi-res logo</option>
                <option value="brochure_ad">Brochure advert</option>
                <option value="screen_ad">Screen advert</option>
              </select>
            </div>
            <div class="field">
              <label>File (JPG/PNG/SVG/PDF, max 8 MB)</label>
              <input type="file" name="file" accept="image/*,application/pdf" required>
            </div>
          </div>
          <button class="btn btn-gold" type="submit">Upload asset <span class="arrow">→</span></button>
        </form>
      </div>

      <?php if ($assets): ?>
      <div class="card" style="margin-top:16px">
        <table style="width:100%;border-collapse:collapse;font-size:14px">
          <tr style="text-align:left;color:var(--sage);font-family:var(--mono);font-size:11px;letter-spacing:.1em;text-transform:uppercase">
            <th style="padding:10px 8px;border-bottom:1px solid var(--line)">Type</th>
            <th style="padding:10px 8px;border-bottom:1px solid var(--line)">File</th>
            <th style="padding:10px 8px;border-bottom:1px solid var(--line)">Status</th>
            <th style="padding:10px 8px;border-bottom:1px solid var(--line)">Notes</th>
          </tr>
          <?php foreach ($assets as $a): ?>
          <tr>
            <td style="padding:12px 8px;border-bottom:1px solid var(--line)"><?= e($typeLabels[$a['type']] ?? $a['type']) ?></td>
            <td style="padding:12px 8px;border-bottom:1px solid var(--line)"><a href="<?= e($uploadsUrl . '/' . rawurlencode($a['file_path'])) ?>" target="_blank" rel="noopener" style="color:var(--gold-soft)">View</a></td>
            <td style="padding:12px 8px;border-bottom:1px solid var(--line);color:<?= $statusColor[$a['status']] ?? '#fff' ?>;font-family:var(--mono);font-size:12px;text-transform:uppercase"><?= e($a['status']) ?></td>
            <td style="padding:12px 8px;border-bottom:1px solid var(--line);color:var(--sage)"><?= e($a['notes'] ?? '') ?></td>
          </tr>
          <?php endforeach; ?>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
