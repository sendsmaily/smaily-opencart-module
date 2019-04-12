<div class="panel">
  <div class="container-fluid">
      <h3>Subscribe to newsletter</h3>
    <div class="panel-body">
      <?php if (isset($success_message)) : ?>
      <div class="alert alert-success" role="alert">
       <?php echo $success_message; ?>
      </div>
      <?php endif; ?>
      <?php if (isset($error_message)) : ?>
      <div class="alert alert-danger" role="alert">
        <?php echo $error_message; ?>
      </div>
      <?php endif; ?>
      <form class="form" action='https://<?php echo $subdomain; ?>.sendsmaily.net/api/opt-in/' method="post" autocomplete="off">
            <input type="hidden" name="success_url" value='<?php echo $current_url ?>' />
            <input type="hidden" name="failure_url" value='<?php echo $current_url ?>' />
            <div class="form-group">
            <input type="email" class="form-control" name="email" placeholder="<?php echo $email_placeholder ?>" required/>
            </div>
            <div class="from-group">
            <div class="input-group">
              <input type="text" class="form-control" name="name" placeholder="<?php echo $name_placeholder ?>" />
              <span class="input-group-btn">
                <button type="submit" class="btn btn-sm btn-primary"><?php echo $subscribe_button ?></button>
              </span>
            </div>
            </div>
        <div style="overflow:hidden;height:0px;">
            <input type="text" name="re-email" value="" />
        </div>
      </form>
  </div>
  </div>
</div>