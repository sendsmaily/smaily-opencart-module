<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
    <?php if ($error_warning) { ?>
  <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>

<div class="box">
  <div class="heading">
      <h1><img alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
        <a onclick="$('#form-smaily_for_opencart').submit();" class="button">
          <span><?php echo $button_save; ?></span>
        </a>
        <a onclick="location = '<?php echo $cancel; ?>';" class="button">
          <span><?php echo $button_cancel; ?></span>
        </a>
      </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert warning"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" area-label="Close" data-dismiss="alert warning">&times;</button>
    </div>
    <?php } ?>
    <?php if ($error_validate) { ?>
      <div class="alert warning"><i class="fa fa-exclamation-circle"></i><?php echo $error_validate; ?>
      <button type="button" class="close" area-label="Close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <?php if ($error_autoresponder) { ?>
      <div class="alert warning"><i class="fa fa-exclamation-circle"></i><?php echo $error_autoresponder; ?>
      <button type="button" class="close" area-label="Close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <?php if ($error_delay) { ?>
      <div class="alert warning"><i class="fa fa-exclamation-circle"></i><?php echo $error_delay; ?>
      <button type="button" class="close" area-label="Close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <?php if ($success) { ?>
    <div class="alert"><?php echo $success; ?>
      <button type="button" class="close" area-label="Close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="alert" id="validate-div" hidden>
      <i class="fa fa-exclamation-circle"></i><span id="validate-message"></span>
      <button type="button" class="close" area-label="Close" data-dismiss="alert">&times;</button>
    </div>
</div>
  <!-- Generate form content -->
  <div class="content">
    <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-smaily_for_opencart">
      <table class="form">
        <tr>
          <td><?php echo $entry_enable_module_title; ?></td>
          <td><select name="smaily_for_opencart_status" id="input-status" class="form-control">
                <?php if ($module_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
          </td>
        </tr>
        <!-- API authentication -->
        <tr>
          <div class="form-group <?php echo $validated ? 'has-success':''; ?>">
            <td> <?php echo $subdomain_title; ?></td>
            <td> 
              <input 
                type="text"
                name="smaily_for_opencart_subdomain"
                placeholder="<?php echo $subdomain_placeholder; ?>"
                id="subdomain"
                value="<?php echo $subdomain; ?>"
                class="form-control" />
              <span class="help"><?php echo $small_subdomain ?></span>
                  <?php if ($error_subdomain) { ?>
                      <div class="warning"><?php echo $error_subdomain; ?></div>
                  <?php } ?>           
            </td>
          </div>
        </tr>

        <tr>
          <div class="form-group <?php echo $validated ? 'has-success':''; ?>">
            <td> <?php echo $username_title; ?></td>
            <td> 
              <input 
                type="text"
                name="smaily_for_opencart_username"
                placeholder="<?php echo $username_placeholder; ?>"
                id="username"
                value="<?php echo $username; ?>"
                class="form-control" /><br>
                  <?php if ($error_username) { ?>
                      <div class="warning"><?php echo $error_username; ?></div>
                  <?php } ?>
            </td>
          </div>
        </tr>

        <tr>
          <div class="form-group <?php echo $validated ? 'has-success':''; ?>">
            <td><?php echo $password_title; ?></td>
            <td> 
              <input 
                type="password"
                name="smaily_for_opencart_password"
                placeholder="<?php echo $password_placeholder; ?>"
                id="password"
                value="<?php echo $password; ?>"
                class="form-control" />
              <span class="help">
                <a href="http://help.smaily.com/en/support/solutions/articles/16000062943-create-api-user" target="_blank"><?php echo $small_password ?>
                </a> 
              </span>
                  <?php if ($error_password) { ?>
                      <div class="warning"><?php echo $error_password; ?></div>
                  <?php } ?>             
            </td>
          </div>
        </tr>

        <tr>
          <td><?php echo $rss_feed_title; ?></td>
          <td><?php echo $smaily_rss_url; ?>
            <span class="help"><?php echo $rss_feed_text; ?></span>
          </td>
        </tr>
        
          <tr class="form-group" id="validate-form-group">
            <td><?php echo $validate_title; ?></td>
            <td><button form="form-smaily_for_opencart" id="validate" type="button" title="<?php echo $button_validate; ?>" class="btn btn-primary">
                  <?php echo $button_validate; ?>
                    <span id="smaily-validate-loader" hidden>
                      <i class="fa fa-spinner fa-spin" hidden></i>
                    </span>
                </button>
            </td>
          </tr>
        <!-- Customer sync -->
        <tr>
          <td><?php echo $entry_enable_subscriber_title; ?></td>
          <td><select name="smaily_for_opencart_enable_subscribe" id="input-subscriber-status" class="form-control">
                    <?php if ($subscribe_status) { ?>
                    <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                    <option value="0"><?php echo $text_disabled; ?></option>
                    <?php } else { ?>
                    <option value="1"><?php echo $text_enabled; ?></option>
                    <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                    <?php } ?>
              </select>
          </td>
        </tr>

        <tr>
          <td><?php echo $entry_customer_sync_fields_title; ?></td>
          <td><select class="form-control" name="smaily_for_opencart_syncronize_additional[]" id="customer_sync_fields" multiple="multiple">
                  <?php
                    // All available options
                    $sync_options = [
                      'firstname'  => $firstname,
                      'lastname'  => $lastname,
                      'telephone'  => $telephone,
                      'date_added' => $date_added
                    ];
                    // Add options for select.
                    foreach ($sync_options as $value => $name) {
                      $selected = is_array($syncronize_additional) && in_array($value, $syncronize_additional) ? 'selected' : '';
                      echo("<option value='$value' $selected>$name</option>");
                    }
                    ?>
              </select>
              <span class="help"><?php echo $small_sync_additional; ?></span>
          </td>
        </tr>

        <tr>
          <td><?php echo $sync_token_title; ?></td>
            <td><input 
                  type="text"
                  name="smaily_for_opencart_sync_token"
                  placeholder="<?php echo $sync_token_placeholder; ?>"
                  id="sync-token"
                  value="<?php echo $sync_token; ?>"
                  class="form-control" />
                  <span class="help"><?php echo $small_token ?></span>
            </td>
        </tr>

        <tr>
          <td><?php echo $sync_customer_url_title; ?></td>
          <td>
            <p><strong><?php echo $customer_cron_url ?></strong></p>
            <span class="help"><?php echo $customer_cron_text ?></span>
          </td>
        </tr>
      </table>
      <table id="module" class="list">
          <thead>
            <tr>
              <td class="left"><?php echo $entry_layout; ?></td>
              <td class="left"><?php echo $entry_position; ?></td>
              <td class="left"><?php echo $entry_status; ?></td>
              <td class="right"><?php echo $entry_sort_order; ?></td>
              <td></td>
            </tr>
          </thead>
          <?php $module_row = 0; ?>
          <?php foreach ($modules as $module) { ?>
          <tbody id="module-row<?php echo $module_row; ?>">
            <tr>
              <td class="left">
                <select name="smaily_for_opencart_module[<?php echo $module_row; ?>][layout_id]">
                  <?php foreach ($layouts as $layout) { ?>
                  <?php if ($layout['layout_id'] == $module['layout_id']) { ?>
                    <option value="<?php echo $layout['layout_id']; ?>" selected="selected"><?php echo $layout['name']; ?></option>
                  <?php } else { ?>
                    <option value="<?php echo $layout['layout_id']; ?>"><?php echo $layout['name']; ?></option>
                  <?php } ?>
                  <?php } ?>
                </select>
              </td>
              <td class="left">
                <select name="smaily_for_opencart_module[<?php echo $module_row; ?>][position]">
                  <?php if ($module['position'] == 'content_top') { ?>
                    <option value="content_top" selected="selected"><?php echo $text_content_top; ?></option>
                  <?php } else { ?>
                    <option value="content_top"><?php echo $text_content_top; ?></option>
                  <?php } ?>
                  <?php if ($module['position'] == 'content_bottom') { ?>
                    <option value="content_bottom" selected="selected"><?php echo $text_content_bottom; ?></option>
                  <?php } else { ?>
                    <option value="content_bottom"><?php echo $text_content_bottom; ?></option>
                  <?php } ?>
                  <?php if ($module['position'] == 'column_left') { ?>
                    <option value="column_left" selected="selected"><?php echo $text_column_left; ?></option>
                  <?php } else { ?>
                    <option value="column_left"><?php echo $text_column_left; ?></option>
                  <?php } ?>
                  <?php if ($module['position'] == 'column_right') { ?>
                    <option value="column_right" selected="selected"><?php echo $text_column_right; ?></option>
                  <?php } else { ?>
                    <option value="column_right"><?php echo $text_column_right; ?></option>
                  <?php } ?>
                </select>
              </td>
              <td class="left">
                <select name="smaily_for_opencart_module[<?php echo $module_row; ?>][status]">
                  <?php if ($module['status']) { ?>
                    <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                    <option value="0"><?php echo $text_disabled; ?></option>
                  <?php } else { ?>
                    <option value="1"><?php echo $text_enabled; ?></option>
                    <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                  <?php } ?>
                </select>
              </td>
              <td class="right">
                <input
                  type="text"
                  name="smaily_for_opencart_module[<?php echo $module_row; ?>][sort_order]"
                  value="<?php echo $module['sort_order']; ?>"
                  size="3" />
              </td>
              <td class="left">
                <a onclick="$('#module-row<?php echo $module_row; ?>').remove();" class="button"><?php echo $button_remove; ?></a>
              </td>
            </tr>
          </tbody>
          <?php $module_row++; ?>
          <?php } ?>
          <tfoot>
            <tr>
              <td colspan="4"></td>
              <td class="left">
                <a onclick="addModule();" class="button"><?php echo $button_module; ?></a>
              </td>
            </tr>
          </tfoot>
        </table>
    </form>
  </div>
</div>
<?php echo $footer; ?>
<script type="text/javascript">
    //Layout module at the bottom of admin view
    var module_row = <?php echo $module_row; ?>;

    function addModule() {  
      html  = '<tbody id="module-row' + module_row + '">';
      html += '  <tr>';
      html += '    <td class="left"><select name="smaily_for_opencart_module[' + module_row + '][layout_id]">';
      <?php foreach ($layouts as $layout) { ?>
      html += '      <option value="<?php echo $layout['layout_id']; ?>"><?php echo addslashes($layout['name']); ?></option>';
      <?php } ?>
      html += '    </select></td>';
      html += '    <td class="left"><select name="smaily_for_opencart_module[' + module_row + '][position]">';
      html += '      <option value="content_top"><?php echo $text_content_top; ?></option>';
      html += '      <option value="content_bottom"><?php echo $text_content_bottom; ?></option>';
      html += '      <option value="column_left"><?php echo $text_column_left; ?></option>';
      html += '      <option value="column_right"><?php echo $text_column_right; ?></option>';
      html += '    </select></td>';
      html += '    <td class="left"><select name="smaily_for_opencart_module[' + module_row + '][status]">';
      html += '      <option value="1" selected="selected"><?php echo $text_enabled; ?></option>';
      html += '      <option value="0"><?php echo $text_disabled; ?></option>';
      html += '    </select></td>';
      html += '    <td class="right"><input type="text" name="smaily_for_opencart_module[' + module_row + '][sort_order]" value="" size="3" /></td>';
      html += '    <td class="left"><a onclick="$(\'#module-row' + module_row + '\').remove();" class="button"><?php echo $button_remove; ?></a></td>';
      html += '  </tr>';
      html += '</tbody>';
      
      $('#module tfoot').before(html);
      module_row++;
    }
</script>
<script type="text/javascript">
(function($) {
   $(window).on("load", function() {
  
    getAutoresponders();
    function getAutoresponders() {
        // Smaily credentials.
        var subdomain = $("#subdomain").val();
        var username = $("#username").val();
        var password = $("#password").val();
        if (subdomain != '' && username != '' && password != '') {
          $.post('index.php?route=module/smaily_for_opencart/ajaxGetAutoresponders&token=<?php echo $token ?>',{
              'subdomain' : subdomain,
              'username'  : username,
              'password'  : password
          }, function(response) {
              $.each(response, function(index,value){
                $("#abandoned-autoresponder").append(
                  $("<option>", {
                    value : JSON.stringify({'name':value, 'id': index}),
                    text : value
                  })
                );
              });
            },'json');
        }
      }
    
    //Button close
    var buttons = document.getElementsByClassName('close');
    for(var i=0; i< buttons.length; i++) {
      var button = buttons[i];
        button.onclick = function() {
          code = this.getAttribute('whenClicked');
          eval($('#validate-div').hide());
          eval($('#alert').hide());
        }
    }
    // Validate autoresponders.
    $('#validate').on('click', function(e) {
      // Scroll top.
      $("html, body").animate(
        {
          scrollTop: "0px"
        },
        "slow"
      );
      // Validate form button section.
      var validateSection = $('#validate-form-group');
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
      $.post( "index.php?route=module/smaily_for_opencart/ajaxValidateCredentials&token=<?php echo $token ?>",{
          'subdomain' : subdomain,
          'username'  : username,
          'password'  : password
        }, function(response) {
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
            // Get autoresponders.
            getAutoresponders();
            // Remove alert messages.
            $('div.warning, div.warning').hide();
            // Remove form group has-error
            $('div.has-error').removeClass('has-error').addClass('has-success');
            // Add text, remove danger class had errors.
            $('#validate-message').text(response['success']);
            $('#validate-div').removeClass('warning');
            // Show response
            $('#validate-div').addClass('success').show();
            // Hide validate button section.
            validateSection.hide();
          }
        },'json');
   });
  });
})(jQuery);
</script>
