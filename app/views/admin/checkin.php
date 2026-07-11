<?php
/** @var \App\Core\View $this */
$this->layout('layouts/admin');
$adminScript = true; // load admin.js
?>
<div class="stat-grid" style="grid-template-columns:repeat(2,1fr);max-width:420px">
  <div class="scard"><div class="k">Passes issued</div><div class="v" id="statIssued"><?= (int) $stats['issued'] ?></div></div>
  <div class="scard"><div class="k">Checked in</div><div class="v" id="statChecked"><?= (int) $stats['checked_in'] ?></div></div>
</div>

<div class="scan-grid"
     data-scan-url="<?= e(url('/admin/checkin/scan')) ?>"
     data-csrf="<?= e($csrf) ?>"
     data-prefill="<?= e($prefill ?? '') ?>">
  <div class="panel" style="margin:0">
    <div class="ph"><h2>Scan a pass</h2><button class="btn btn-ghost btn-sm" id="camToggle" type="button">Start camera</button></div>
    <div class="pb">
      <video id="scanVideo" playsinline muted></video>
      <p class="muted" id="camHint" style="margin-top:10px">Point the camera at a ticket QR. If your browser can't scan, type the code below.</p>
      <form id="manualForm" style="display:flex;gap:10px;margin-top:14px">
        <input type="text" id="manualCode" placeholder="GT-XXXXXXXXXX" autocomplete="off" spellcheck="false"
               style="flex:1;padding:11px 13px;background:var(--ink);border:1px solid var(--line);border-radius:3px;color:#E8EFE9;font-family:var(--mono);text-transform:uppercase">
        <button class="btn btn-gold" type="submit">Check in</button>
      </form>
    </div>
  </div>

  <div>
    <div class="scan-verdict" id="verdict">
      <div class="big">Ready</div>
      <div class="det">Scan or enter a code to verify a pass.</div>
    </div>
  </div>
</div>
