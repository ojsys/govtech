<?php
/** @var \App\Core\View $this */
$this->layout('layouts/admin');
?>
<div class="panel" style="max-width:560px">
  <div class="ph"><h2>Signed in as</h2></div>
  <div class="pb">
    <div class="field">
      <label>Name</label>
      <input type="text" value="<?= e($authUser['name'] ?? '') ?>" disabled>
    </div>
    <div class="field">
      <label>Email</label>
      <input type="text" value="<?= e($authUser['email'] ?? '') ?>" disabled>
    </div>
    <div class="field">
      <label>Role</label>
      <input type="text" value="<?= e($authRole ?? '') ?>" disabled>
    </div>
  </div>
</div>

<div class="panel" style="max-width:560px;margin-top:1.25rem">
  <div class="ph"><h2>Change password</h2></div>
  <div class="pb">
    <form method="post" action="<?= e(url('/admin/account/password')) ?>" autocomplete="off">
      <input type="hidden" name="_token" value="<?= e($csrf) ?>">
      <div class="field">
        <label>Current password</label>
        <input type="password" name="current_password" autocomplete="current-password" required>
      </div>
      <div class="field">
        <label>New password <small style="color:#9FB3A8">(at least 10 characters)</small></label>
        <input type="password" name="new_password" autocomplete="new-password" minlength="10" required>
      </div>
      <div class="field">
        <label>Confirm new password</label>
        <input type="password" name="confirm_password" autocomplete="new-password" minlength="10" required>
      </div>
      <div class="form-actions">
        <button class="btn btn-gold" type="submit">Update password</button>
      </div>
    </form>
  </div>
</div>
