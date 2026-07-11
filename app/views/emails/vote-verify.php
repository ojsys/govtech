<?php
/** Email: confirm-your-vote. Inline styles only. */
?>
<div style="background:#07140E;padding:32px 0;font-family:Arial,Helvetica,sans-serif;color:#E8EFE9">
  <div style="max-width:560px;margin:0 auto;background:#0B1E16;border:1px solid rgba(159,179,168,.16);border-radius:8px;overflow:hidden">
    <div style="background:linear-gradient(160deg,#120E03,#1d1606);padding:22px 28px">
      <div style="font-size:12px;letter-spacing:2px;text-transform:uppercase;color:#E4C865;font-family:'Courier New',monospace">Nigeria GovTech Awards</div>
      <div style="font-size:21px;color:#ffffff;margin-top:6px;font-weight:bold">Confirm your vote</div>
    </div>
    <div style="padding:28px">
      <p style="margin:0 0 16px;color:#C2D2C8">You're voting for <b style="color:#fff"><?= e($nominee['nominee_name']) ?></b><?= !empty($nominee['nominee_org']) ? ' (' . e($nominee['nominee_org']) . ')' : '' ?>.</p>
      <p style="margin:0 0 22px;color:#C2D2C8">Your vote only counts once you confirm it. Click the button below — if you didn't request this, you can ignore this email.</p>
      <p style="margin:0 0 24px">
        <a href="<?= e($link) ?>" style="display:inline-block;background:#C9A227;color:#1a1405;text-decoration:none;font-weight:bold;padding:14px 26px;border-radius:3px">Confirm my vote &#8594;</a>
      </p>
      <p style="margin:0;color:#9FB3A8;font-size:12.5px;word-break:break-all">Or paste this link into your browser:<br><?= e($link) ?></p>
    </div>
  </div>
</div>
