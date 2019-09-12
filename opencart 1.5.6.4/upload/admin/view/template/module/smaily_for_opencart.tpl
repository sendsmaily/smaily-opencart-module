<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?>
      <a href="<?php echo $breadcrumb['href']; ?>">
        <?php echo $breadcrumb['text']; ?>
      </a>
    <?php } ?>
  </div>
  <div class="box">
    <div class="heading">
      <h1>
        <img alt="" />
        <?php echo $heading_title; ?>
      </h1>
    </div>
    <div class="container-fluid">
      <?php if ($error_validate) { ?>
      <div class="alert warning">
        <i class="fa fa-exclamation-circle"></i>
        <?php echo $error_validate; ?>
      </div>
      <?php } ?>
      <div class="alert" id="validate-div" hidden>
        <i class="fa fa-exclamation-circle"></i>
        <span id="validate-message"></span>
      </div>
    </div>
    <!-- Generate form content -->
    <div class="content">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-smaily_for_opencart">
        <a id="authFormLink" style="display: <?php echo $validated ? 'block' : 'none'; ?>">
          <?php echo $validation_link; ?>
        </a>
        <div id="authForm" style="display: <?php echo $validated ? 'none' : 'block'; ?>">
          <table class="form">
            <!-- API authentication -->
            <tr>
              <div class="form-group <?php echo $validated ? 'has-success' : ''; ?>">
                <td>
                  <?php echo $subdomain_title; ?>
                </td>
                <td> 
                  <input 
                    type="text"
                    name="smaily_for_opencart_subdomain"
                    placeholder="<?php echo $subdomain_placeholder; ?>"
                    id="subdomain"
                    value="<?php echo $subdomain; ?>"
                    class="form-control" />
                  <span class="help">
                    <?php echo $small_subdomain ?>
                  </span>    
                </td>
              </div>
            </tr>
            <tr>
              <div class="form-group <?php echo $validated ? 'has-success' : ''; ?>">
                <td>
                  <?php echo $username_title; ?>
                </td>
                <td> 
                  <input 
                    type="text"
                    name="smaily_for_opencart_username"
                    placeholder="<?php echo $username_placeholder; ?>"
                    id="username"
                    value="<?php echo $username; ?>"
                    class="form-control" /><br>
                </td>
              </div>
            </tr>
            <tr>
              <div class="form-group <?php echo $validated ? 'has-success' : ''; ?>">
                <td>
                  <?php echo $password_title; ?>
                </td>
                <td> 
                  <input 
                    type="password"
                    name="smaily_for_opencart_password"
                    placeholder="<?php echo $password_placeholder; ?>"
                    id="password"
                    value="<?php echo $password; ?>"
                    class="form-control" />
                  <span class="help">
                    <a 
                      href="http://help.smaily.com/en/support/solutions/articles/16000062943-create-api-user" 
                      target="_blank">
                      <?php echo $small_password ?>
                    </a> 
                  </span>     
                </td>
              </div>
            </tr>
            <tr class="form-group" id="validate-form-group">
              <td>
                <?php echo $validate_title; ?>
              </td>
              <td>
                <button 
                  form="form-smaily_for_opencart" 
                  id="validate" 
                  type="button" 
                  title="<?php echo $button_validate; ?>" 
                  class="btn btn-primary">
                  <?php echo $button_validate; ?>
                  <span id="smaily-validate-loader" hidden>
                    <i class="fa fa-spinner fa-spin" hidden></i>
                  </span>
                </button>
              </td>
            </tr>  
          </table>
        </div>
      </form>
    </div>
  </div>
</div>
<?php echo $footer; ?>
<script type="text/javascript">
(function($) {
  $(window).on("load", function() {
    
    // Toggle API authentication form on link click
    $('#authFormLink').on('click', function(e) {
      $("#authForm").toggle();
    });
    // Validate autoresponders.
    $('#validate').on('click', function(e) {
      // Scroll top.
      $("html, body").animate(
        {
          scrollTop: "0px"
        },
        "slow"
      );
      // API authentication section
      var authenticationForm = $("#authForm");
      // Link to display authentication form
      var authenticationLink = $("#authFormLink");
      // Spinner
      var spinner = $("#smaily-validate-loader");
      // Smaily credentials.
      var subdomain = $("#subdomain").val();
      var username = $("#username").val();
      var password = $("#password").val();
  
      // Display error if empty values.
      if (!subdomain) {
        $('#subdomain').parent().addClass('has-error');
      }
      if (!username) {
        $('#username').parent().addClass('has-error');
      }
      if (!password) {
        $('#password').parent().addClass('has-error');
      }

      // Start spinner.
      spinner.show();
      $.post(
        'index.php?route=module/smaily_for_opencart/ajaxValidateCredentials&token=<?php echo $token ?>', 
        {
          'subdomain' : subdomain,
          'username' : username,
          'password' : password
        }, 
        function(response) {
          // Hide spinner.
          spinner.hide();
          // Error message
          if (response['error']) {
            $('#validate-message').text(response['error']);
            $('#validate-div').addClass('warning').show();
          } else if (!response) {
            $('#validate-message').text('Something went wrong with request to smaily');
            $('#validate-div').addClass('warning').show();
          }
          // Success message.
          if (response['success']) {
            // Remove alert messages.
            $('div.warning, div.warning').hide();
            // Remove form group has-error
            $('div.has-error').removeClass('has-error').addClass('has-success');
            // Add text, remove danger class had errors.
            $('#validate-message').text(response['success']);
            $('#validate-div').removeClass('warning');
            // Show response
            $('#validate-div').addClass('success').show();
            // Hide validation form and display a link to make it visible again 
            authenticationForm.hide();
            authenticationLink.show();
          }
        },
        'json');
    });
  });
})(jQuery);
</script>
