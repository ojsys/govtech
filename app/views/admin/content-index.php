<?php
/** @var \App\Core\View $this */
$this->layout('layouts/admin');
$cards = [];
foreach ($schema as $slug => $sec) {
    $cards[$slug] = ['title' => $sec['title'], 'intro' => $sec['intro'] ?? ''];
}
$cards['event'] = ['title' => 'Event details', 'intro' => 'Name, edition, theme, dates and venue.'];
?>
<p class="muted" style="color:var(--sage);margin-bottom:20px">Edit the text, images and branding shown across the public site. Speakers, gallery photos, testimonials, tickets and sponsorship packages are managed on their own pages.</p>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px">
  <?php foreach ($cards as $slug => $c): ?>
    <a href="<?= e(url('/admin/content/' . $slug)) ?>" class="scard" style="display:block;text-decoration:none;transition:.2s;border:1px solid var(--line)">
      <div style="font-family:var(--display);font-size:18px;color:#fff;font-weight:600;margin-bottom:6px"><?= e($c['title']) ?></div>
      <div style="color:var(--sage);font-size:13.5px"><?= e($c['intro']) ?></div>
      <div style="color:var(--gold);font-family:var(--mono);font-size:12px;margin-top:14px">Edit →</div>
    </a>
  <?php endforeach; ?>
</div>
