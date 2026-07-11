<?php
/** @var \App\Core\View $this */
$this->layout('layouts/admin');
$ev = $event ?? [];
?>
<div style="margin-bottom:18px"><a class="btn btn-ghost btn-sm" href="<?= e(url('/admin/content')) ?>">← Content</a></div>

<?php
// Live preview string for the ticket date line, mirroring how tickets render it.
$dateStr = 'Set the dates below';
if (!empty($ev['start_date'])) {
    $sd = strtotime($ev['start_date']);
    $ed = strtotime($ev['end_date'] ?? $ev['start_date']);
    $dateStr = date('M j', $sd) . ' – ' . (date('M', $sd) === date('M', $ed) ? date('j', $ed) : date('M j', $ed)) . ', ' . date('Y', $ed);
}
$venuePreview = $ev['venue'] ?? ''; // shown on the ticket exactly as entered
?>
<form method="post" action="<?= e(url('/admin/content/event')) ?>">
  <input type="hidden" name="_token" value="<?= e($csrf) ?>">

  <!-- Date & Venue: what appears on every ticket -->
  <div class="panel" style="max-width:760px">
    <div class="ph"><h2>Ticket date &amp; venue</h2></div>
    <div class="pb">
      <p class="muted" style="color:var(--sage);margin-bottom:18px">These appear on every issued ticket, in the confirmation email, and across the site (hero, countdown, footer).</p>
      <div class="field-row">
        <div class="field"><label>Start date</label><input type="date" name="start_date" value="<?= e($ev['start_date'] ?? '') ?>"></div>
        <div class="field"><label>End date</label><input type="date" name="end_date" value="<?= e($ev['end_date'] ?? '') ?>"></div>
      </div>
      <div class="field"><label>Venue</label><input type="text" name="venue" value="<?= e($ev['venue'] ?? '') ?>" placeholder="e.g. Banquet Hall, Presidential Villa, Abuja"></div>
      <div style="background:var(--ink);border:1px solid var(--line);border-radius:6px;padding:14px 16px;margin-top:6px">
        <div style="font-family:var(--mono);font-size:10px;letter-spacing:.14em;text-transform:uppercase;color:var(--sage);margin-bottom:8px">On the ticket</div>
        <div style="display:flex;gap:28px;flex-wrap:wrap">
          <div><div style="font-family:var(--mono);font-size:9px;color:var(--sage);text-transform:uppercase;letter-spacing:.1em">Dates</div><div style="font-family:var(--display);color:#fff;margin-top:2px"><?= e($dateStr) ?></div></div>
          <div><div style="font-family:var(--mono);font-size:9px;color:var(--sage);text-transform:uppercase;letter-spacing:.1em">Venue</div><div style="font-family:var(--display);color:#fff;margin-top:2px"><?= e($venuePreview ?: '—') ?></div></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Other event details -->
  <div class="panel" style="max-width:760px">
    <div class="ph"><h2>Event details</h2></div>
    <div class="pb">
      <div class="field"><label>Event name</label><input type="text" name="name" value="<?= e($ev['name'] ?? '') ?>"></div>
      <div class="field"><label>Edition</label><input type="text" name="edition" value="<?= e($ev['edition'] ?? '') ?>" placeholder="e.g. 3rd" style="max-width:200px"></div>
      <div class="field"><label>Theme</label><textarea name="theme" rows="2"><?= e($ev['theme'] ?? '') ?></textarea></div>
      <div class="form-actions">
        <button class="btn btn-gold" type="submit">Save event details</button>
      </div>
    </div>
  </div>
</form>
