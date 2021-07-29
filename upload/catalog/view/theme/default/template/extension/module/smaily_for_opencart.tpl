<?php if (isset($success_message)): ?>
<div class="alert alert-success" role="alert"><?php echo $success_message; ?></div>
<?php endif; ?>
<?php if (isset($error_message)): ?>
<div class="alert alert-danger" role="alert"><?php echo $error_message; ?></div>
<?php endif; ?>

<form class="form" action='https://<?php echo $subdomain; ?>.sendsmaily.net/api/opt-in/' method="post" autocomplete="off">
  <input type="hidden" name="language" value="<?php echo $language; ?>" />
  <input type="hidden" name="success_url" value="<?php echo $current_url; ?>" />
  <input type="hidden" name="failure_url" value="<?php echo $current_url; ?>" />
  <div class="form-group">
    <input type="email" class="form-control" name="email" placeholder="<?php echo $t['email_placeholder']; ?>" required />
  </div>
  <div class="from-group">
    <div class="input-group">
      <input type="text" class="form-control" name="name" placeholder="<?php echo $t['name_placeholder']; ?>" />
      <span class="input-group-btn">
        <button type="submit" class="btn btn-sm btn-primary"><?php echo $t['subscribe_button']; ?></button>
      </span>
    </div>
  </div>
</form>
