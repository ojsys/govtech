<?php
/** @var \App\Core\View $this */
$this->layout('layouts/app');
$s = $settings ?? [];
$ev = $event ?? null;
$errors = $errors ?? [];
$err = fn(string $f) => isset($errors[$f]) ? '<div class="err">' . e($errors[$f]) . '</div>' : '';
$cls = fn(string $f) => isset($errors[$f]) ? ' invalid' : '';
$email = $s['contact_email'] ?? 'info@govtechconference.ng';
$venue = $ev['venue'] ?? 'Banquet Hall, Presidential Villa, Abuja';
$dates = 'Oct 7 – 8, 2026';
if ($ev && !empty($ev['start_date'])) {
    $dates = date('M j', strtotime($ev['start_date'])) . ' – ' . date('j, Y', strtotime($ev['end_date'] ?? $ev['start_date']));
}
?>
<section class="section" style="padding-top:0">
  <div class="wrap">
    <div class="page-head reveal in">
      <span class="eyebrow">Get in touch</span>
      <h1>Contact the organisers.</h1>
      <p>Questions about attending, speaking, the awards or partnership? Send us a message and the team will respond.</p>
    </div>

    <?php if ($m = flash('ok')): ?><div class="alert alert-ok" style="margin-top:24px"><?= e($m) ?></div><?php endif; ?>
    <?php if ($m = flash('error')): ?><div class="alert alert-error" style="margin-top:24px"><?= e($m) ?></div><?php endif; ?>

    <div class="reg-grid" style="margin-top:40px">
      <!-- Form -->
      <div class="card reveal">
        <h3>Send a message</h3>
        <p class="hint">We typically reply within two working days.</p>
        <form method="post" action="<?= e(url('/contact')) ?>">
          <input type="hidden" name="_token" value="<?= e($csrf) ?>">
          <div class="hp" aria-hidden="true"><label>Leave empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

          <div class="field-row">
            <div class="field<?= $cls('name') ?>">
              <label>Your name</label>
              <input type="text" name="name" value="<?= old('name') ?>" autocomplete="name" required>
              <?= $err('name') ?>
            </div>
            <div class="field<?= $cls('email') ?>">
              <label>Email</label>
              <input type="email" name="email" value="<?= old('email') ?>" autocomplete="email" required>
              <?= $err('email') ?>
            </div>
          </div>
          <div class="field<?= $cls('subject') ?>">
            <label>Subject</label>
            <input type="text" name="subject" value="<?= old('subject') ?>" placeholder="What is this about?">
            <?= $err('subject') ?>
          </div>
          <div class="field<?= $cls('message') ?>">
            <label>Message</label>
            <textarea name="message" rows="6" required placeholder="How can we help?"><?= old('message') ?></textarea>
            <?= $err('message') ?>
          </div>
          <button class="btn btn-gold btn-block" type="submit">Send message <span class="arrow">→</span></button>
        </form>
      </div>

      <!-- Info -->
      <div class="card reveal d1">
        <h3>Reach us directly</h3>
        <div class="field">
          <label>Email</label>
          <a href="mailto:<?= e($email) ?>" style="color:var(--gold-soft)"><?= e($email) ?></a>
        </div>
        <div class="field">
          <label>Venue</label>
          <div style="color:#C2D2C8"><?= e($venue) ?></div>
        </div>
        <div class="field">
          <label>Dates</label>
          <div style="color:#C2D2C8"><?= e($dates) ?></div>
        </div>
        <div class="field">
          <label>Organiser</label>
          <div style="color:#C2D2C8"><?= e($s['organizer_name'] ?? 'Bureau of Public Service Reforms') ?><br><span class="muted" style="color:var(--sage);font-size:13px"><?= e($s['organizer_note'] ?? 'The Presidency, Federal Republic of Nigeria') ?></span></div>
        </div>
        <div class="field" style="margin-bottom:0">
          <label>Follow</label>
          <div style="display:flex;gap:14px">
            <a href="#" style="color:#C2D2C8">LinkedIn</a>
            <a href="#" style="color:#C2D2C8">X / Twitter</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
