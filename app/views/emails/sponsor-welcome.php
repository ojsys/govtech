<?php
/** Email: sponsor portal welcome + credentials. Inline styles only. */
?>
<div style="background:#07140E;padding:32px 0;font-family:Arial,Helvetica,sans-serif;color:#E8EFE9">
  <div style="max-width:560px;margin:0 auto;background:#0B1E16;border:1px solid rgba(159,179,168,.16);border-radius:8px;overflow:hidden">
    <div style="background:#0C7A4D;padding:22px 28px">
      <div style="font-size:12px;letter-spacing:2px;text-transform:uppercase;color:#E4C865;font-family:'Courier New',monospace">Nigeria GovTech Conference &amp; Awards</div>
      <div style="font-size:21px;color:#ffffff;margin-top:6px;font-weight:bold">Your sponsorship is confirmed</div>
    </div>
    <div style="padding:28px">
      <p style="margin:0 0 16px;color:#C2D2C8">Hi <?= e($app['contact_name']) ?>,</p>
      <p style="margin:0 0 18px;color:#C2D2C8">Thank you for partnering with us as a <strong style="color:#fff"><?= e($app['package_name']) ?></strong> sponsor. Your portal account is ready — sign in to upload your branding assets and manage your benefits.</p>

      <?php if ($comp > 0): ?>
        <p style="margin:0 0 18px;color:#C2D2C8">Your package includes <strong style="color:#fff"><?= (int) $comp ?> complimentary delegate pass<?= (int) $comp === 1 ? '' : 'es' ?></strong>, now available in your portal.</p>
      <?php endif; ?>

      <table style="width:100%;border-collapse:collapse;background:#102B20;border-radius:6px;margin:8px 0 22px">
        <tr><td style="padding:14px 16px;color:#9FB3A8;font-size:13px;font-family:'Courier New',monospace">EMAIL</td><td style="padding:14px 16px;color:#fff;text-align:right;font-weight:bold"><?= e($app['email']) ?></td></tr>
        <tr><td style="padding:14px 16px;color:#9FB3A8;font-size:13px;font-family:'Courier New',monospace;border-top:1px solid rgba(159,179,168,.16)">PASSWORD</td><td style="padding:14px 16px;color:#E4C865;text-align:right;font-weight:bold;font-family:'Courier New',monospace;border-top:1px solid rgba(159,179,168,.16)"><?= e($password) ?></td></tr>
      </table>

      <p style="margin:0 0 24px">
        <a href="<?= e($portalUrl) ?>" style="display:inline-block;background:#C9A227;color:#1a1405;text-decoration:none;font-weight:bold;padding:14px 26px;border-radius:3px">Open the sponsor portal &#8594;</a>
      </p>
      <p style="margin:0;color:#9FB3A8;font-size:12.5px">For your security, please change your password after first sign-in. If you didn't expect this email, contact the organisers.</p>
    </div>
  </div>
</div>
