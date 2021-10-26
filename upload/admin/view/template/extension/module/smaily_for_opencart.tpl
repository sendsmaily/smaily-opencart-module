<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-smaily_for_opencart" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" area-label="Close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <?php if ($error_validate) { ?>
      <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i><?php echo $error_validate; ?>
      <button type="button" class="close" area-label="Close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <?php if ($error_autoresponder) { ?>
      <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i><?php echo $error_autoresponder; ?>
      <button type="button" class="close" area-label="Close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <?php if ($error_delay) { ?>
      <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i><?php echo $error_delay; ?>
      <button type="button" class="close" area-label="Close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <?php if ($error_limit) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_limit; ?>
      <button type="button" class="close" area-label="Close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <?php if ($success) { ?>
    <div class="alert alert-success"><i class="fa fa-exclamation-circle"></i> <?php echo $success; ?>
      <button type="button" class="close" area-label="Close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="alert" id="validate-alert" hidden>
      <i class="fa fa-exclamation-circle"></i><span id="validate-message"></span>
      <button type="button" class="close" area-label="Close">&times;</button>
    </div>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-smaily_for_opencart" class="form-horizontal">
          <!-- Generate tab nav for sections -->
          <div class="tab-pane">
            <ul class="nav nav-tabs" id="sections">
              <?php foreach ($sections as $section) { ?>
              <li>
                <a href="#section<?php echo $section['section_id']; ?>" data-toggle="tab"><?php echo $section['name']; ?></a>
              </li>
              <?php } ?>
            </ul>
            <div class="tab-content">
            <!-- Generate form content for each section -->
            <div id="section1" class="tab-pane fade in active">
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_enable_module_title; ?></label>
                <div class="col-sm-10">
                  <select name="smaily_for_opencart_status" id="input-status" class="form-control">
                    <?php if ($module_status) { ?>
                      <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                      <option value="0"><?php echo $text_disabled; ?></option>
                    <?php } else { ?>
                      <option value="1"><?php echo $text_enabled; ?></option>
                      <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="form-group <?php echo $validated ? 'has-success':''; ?>">
                <label class="col-sm-2 control-label" for="smaily_for_opencart_subdomain">
                  <?php echo $subdomain_title ?>
                </label>
                <div class="col-sm-10">
                  <input type="text"
                        name="smaily_for_opencart_subdomain"
                        placeholder="<?php echo $subdomain_placeholder; ?>"
                        id="subdomain"
                        value="<?php echo $subdomain; ?>"
                        class="form-control" />
                  <small><?php echo $small_subdomain ?></small>
                  <?php if ($error_subdomain) { ?>
                    <div class="text-danger"><?php echo $error_subdomain; ?></div>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group <?php echo $validated ? 'has-success':''; ?>">
                <label class="col-sm-2 control-label" for="smaily_for_opencart_username"><?php echo $username_title ?></label>
                <div class="col-sm-10">
                  <input type="text"
                        name="smaily_for_opencart_username"
                        placeholder="<?php echo $username_placeholder; ?>"
                        id="username"
                        value="<?php echo $username; ?>"
                        class="form-control" />
                  <?php if ($error_username) { ?>
                    <div class="text-danger"><?php echo $error_username; ?></div>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group <?php echo $validated ? 'has-success':''; ?>">
                <label class="col-sm-2 control-label" for="smaily_for_opencart_password"><?php echo $password_title ?></label>
                <div class="col-sm-10">
                  <input type="password"
                        name="smaily_for_opencart_password"
                        placeholder="<?php echo $password_placeholder; ?>"
                        id="password"
                        value="<?php echo $password; ?>"
                        class="form-control" />
                  <small>
                    <a href="https://smaily.com/help/api/general/create-api-user/" target="_blank">
                      <?php echo $small_password ?>
                    </a>
                  </small>
                  <?php if ($error_password) { ?>
                    <div class="text-danger"><?php echo $error_password; ?></div>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group">
                <label
                  class="col-sm-2 control-label"
                  id="validate-title"
                  <?php echo $validated ? 'style="display: none;"' : ''; ?>
                >
                  <?php echo $validate_title; ?>
                </label>
                <label
                  class="col-sm-2 control-label"
                  id="reset-title"
                  <?php echo $validated ? '' : 'style="display: none;"' ?>
                >
                  <?php echo $reset_credentials_title; ?>
                </label>
                <div class="col-sm-10">
                  <button
                    type="button"
                    title="<?php echo $button_reset_credentials; ?>"
                    class="btn btn-primary"
                    id="reset-credentials"
                    <?php echo $validated ? '' : 'style="display: none;"' ?>
                  >
                    <?php echo $button_reset_credentials ?>
                    <span id="smaily-reset-loader" hidden>
                      <i class="fa fa-spinner fa-spin" hidden></i>
                    </span>
                  </button>
                  <button
                    type="button"
                    title="<?php $button_validate; ?>"
                    class="btn btn-primary"
                    id="validate"
                    <?php echo $validated ? 'style="display: none;"' : '' ?>
                  >
                    <?php echo $button_validate; ?>
                    <span id="smaily-validate-loader" hidden>
                      <i class="fa fa-spinner fa-spin" hidden></i>
                    </span>
                  </button>
                </div>
            </div>
            </div>
            <!-- Customer sync -->
            <div id="section2" class="tab-pane fade in">
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-subscriber-status"><?php echo $entry_enable_subscriber_title; ?></label>
                <div class="col-sm-10">
                  <select name="smaily_for_opencart_enable_subscribe" id="input-subscriber-status" class="form-control">
                    <?php if ($subscribe_status) { ?>
                    <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                    <option value="0"><?php echo $text_disabled; ?></option>
                    <?php } else { ?>
                    <option value="1"><?php echo $text_enabled; ?></option>
                    <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="customer_sync_fields"><?php echo $entry_customer_sync_fields_title; ?></label>
                <div class="col-sm-10">
                  <select class="form-control" name="smaily_for_opencart_syncronize_additional[]" id="customer_sync_fields" multiple="multiple">
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
                      $selected = is_array($syncronize_additional) && in_array($value, $syncronize_additional) ? 'selected' : ''; ?>
                      <option value="<?php echo $value; ?>" <?php echo $selected; ?>><?php echo $name; ?></option>
                    <?php } ?>
                    </select>
                  <small><?php echo $small_sync_additional; ?></small>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="smaily_for_opencart_sync_token"><?php echo $sync_token_title ?></label>
                <div class="col-sm-10">
                  <input type="text"
                        name="smaily_for_opencart_sync_token"
                        placeholder="<?php echo $sync_token_placeholder; ?>"
                        id="sync-token"
                        value="<?php echo $sync_token; ?>"
                        class="form-control" />
                  <small><?php echo $small_token ?></small>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $sync_customer_url_title ?></label>
                <div class="col-sm-10">
                <p><strong><?php echo $customer_cron_url ?></strong></p>
                <p><?php echo $customer_cron_text ?></p>
                </div>
              </div>
          </div>
          <!-- Abandoned cart -->
            <div id="section3" class="tab-pane fade in">
              <div class="form-group">
                <label class="col-sm-2 control-label" for="input-abandoned-status"><?php echo $entry_enable_abandoned_title; ?></label>
                <div class="col-sm-10">
                  <select name="smaily_for_opencart_enable_abandoned" id="input-abandoned-status" class="form-control">
                    <?php if ($abandoned_status) { ?>
                    <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                    <option value="0"><?php echo $text_disabled; ?></option>
                    <?php } else { ?>
                    <option value="1"><?php echo $text_enabled; ?></option>
                    <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="abandoned-autoresponder"><?php echo $entry_autoresponder_title; ?></label>
                <div class="col-sm-10">
                  <select name="smaily_for_opencart_abandoned_autoresponder" id="abandoned-autoresponder" class="form-control">
                    <?php if($abandoned_autoresponder) { ?>
                      <option value="<?php echo htmlentities(json_encode($abandoned_autoresponder)); ?>">
                        <?php echo $abandoned_autoresponder['name'] ?> - (selected)
                      </option>
                    <?php } else { ?>
                      <option value="">Select autoresponder</option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="abandoned_sync_fields"><?php echo $abandoned_sync_fields_title; ?></label>
                <div class="col-sm-10">
                  <select class="form-control" name="smaily_for_opencart_abandoned_additional[]" id="abandoned_sync_fields" multiple="multiple">
                  <?php
                    // All available options
                    $cart_options = [
                      'first_name'  => $customer_first_name,
                      'last_name'   => $customer_last_name,
                      'name'        => $product_name,
                      'description' => $product_description,
                      'sku'         => $product_sku,
                      'quantity'    => $product_quantity,
                      'price'       => $product_price,
                      'base_price'  => $product_base_price
                    ];
                    // Add options for select.
                    foreach ($cart_options as $value => $name) {
                      $selected = is_array($abandoned_additional) && in_array($value, $abandoned_additional) ? 'selected' : ''; ?>
                      <option value="<?php echo $value; ?>" <?php echo $selected; ?>><?php echo $name; ?></option>
                    <?php } ?>
                    </select>
                    <small><?php echo $small_cart_additional; ?></small>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="smaily_for_opencart_cart_delay"><?php echo $delay_title ?></label>
                <div class="col-sm-10">
                  <div class="input-group">
                    <input type="number"
                          name="smaily_for_opencart_cart_delay"
                          min="15"
                          id="smaily_for_opencart_cart_delay"
                          value="<?php echo $cart_delay; ?>"
                          class="form-control" />
                    <span class="input-group-addon"><?php echo $abandoned_minutes ?></span>
                  </div>
                  <small><?php echo $small_cart_delay ?></small>
                  <?php if ($error_delay) { ?>
                      <div class="text-danger"><?php echo $error_delay; ?></div>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="smaily_for_opencart_cart_token"><?php echo $cart_token_title ?></label>
                <div class="col-sm-10">
                  <input type="text"
                        name="smaily_for_opencart_cart_token"
                        placeholder="<?php echo $cart_token_placeholder; ?>"
                        id="cart-token"
                        value="<?php echo $cart_token; ?>"
                        class="form-control" />
                  <small><?php echo $small_token ?></small>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $sync_cart_url_title ?></label>
                <div class="col-sm-10">
                <p><strong><?php echo $cart_cron_url ?></strong></p>
                <p><?php echo $cart_cron_text ?></p>
                </div>
              </div>
            </div>
            <!-- RSS -->
            <div id="section4" class="tab-pane fade in">
              <div class="form-group">
                <label class="col-sm-2 control-label" for="rss-category"><?php echo $rss_category_title ?></label>
                <div class="col-sm-10">
                  <select name="smaily_for_opencart_rss_category" id="rss-category" class="form-control smaily-rss-options">
                    <option value="">All Products</option>
                    <?php
                    foreach ($rss_categories as $category) {
                      $selected = $rss_category === $category['category_id'] ? 'selected' : ''; ?>
                      <option value="<?php echo $category['category_id'] ?>" <?php echo $selected; ?>><?php echo $category['name']?></option>
                    <?php } ?>
                    </select>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="rss-sort-by"><?php echo $rss_sort_by_title; ?></label>
                <div class="col-sm-10">
                  <select name="smaily_for_opencart_rss_sort_by" id="rss-sort-by" class="form-control smaily-rss-options">
                   <?php
                    // Add options for select.
                    foreach ($sort_options as $sort_code => $sort_name) {
                      $selected = $rss_sort_by === $sort_code ? 'selected' : ''; ?>
                      <option value="<?php echo $sort_code; ?>" <?php echo $selected; ?>><?php echo $sort_name; ?></option>
                    <?php } ?>
                    </select>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="rss-sort-order"><?php echo $rss_sort_order_title; ?></label>
                <div class="col-sm-10">
                  <select name="smaily_for_opencart_rss_sort_order" id="rss-sort-order" class="form-control smaily-rss-options">
                    <?php if ($rss_sort_order == 'ASC') { ?>
                    <option value="ASC" selected="selected"><?php echo $text_ascending; ?></option>
                    <option value="DESC"><?php echo $text_descending; ?></option>
                    <?php } else { ?>
                    <option value="ASC"><?php echo $text_ascending; ?></option>
                    <option value="DESC" selected="selected"><?php echo $text_descending; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label" for="smaily_for_opencart_rss_limit"><?php echo $rss_limit_title; ?></label>
                <div class="col-sm-10">
                  <div class="input-group">
                    <input type="number"
                          name="smaily_for_opencart_rss_limit"
                          min="1"
                          max="250"
                          id="rss-limit"
                          value="<?php echo $rss_limit; ?>"
                          class="form-control smaily-rss-options" />
                    <span class="input-group-addon"><?php echo $rss_limit_products; ?></span>
                  </div>
                  <?php if ($error_limit) { ?>
                      <div class="text-danger"><?php echo $error_limit; ?></div>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $rss_feed_title; ?></label>
                <div class="col-sm-10">
                  <p><strong id="smaily-rss-feed-url"><?php echo $smaily_rss_url; ?></strong></p>
                  <p><?php echo $rss_feed_text; ?></p>
                </div>
              </div>
            </div>
            <div id="section5" class="tab-pane fade in">
              <div class="form-group">
                <table class="table table-bordered table-hover SmailyAbandonedCartsTable">
                  <thead>
                    <tr>
                      <th scope="col"><?php echo $cart_status_table_header_id; ?></th>
                      <th class="text-left"><a href="<?php echo $cart_status_table_sort_name_link; ?>" class="<?php echo $sort === 'lastname' ? 'strtolower($order)' : '' ?>"><?php echo $cart_status_table_header_name; ?></a></th>
                      <th class="text-left"><a href="<?php echo $cart_status_table_sort_email_link; ?>" class="<?php echo $sort === 'email' ? 'strtolower($order)' : '' ?>"><?php echo $cart_status_table_header_email; ?></a></th>
                      <th class="text-left"><?php echo $cart_status_table_header_cart; ?></th>
                      <th class="text-left"><a href="<?php echo $cart_status_table_sort_date_link; ?>" class="<?php echo $sort === 'sent_time' ? 'strtolower($order)' : '' ?>"><?php echo $cart_status_table_header_date; ?></a></th>
                      <th class="text-left"><a href="<?php echo $cart_status_table_sort_status_link; ?>" class="<?php echo $sort === 'is_sent' ? 'strtolower($order)' : '' ?>"><?php echo $cart_status_table_header_status; ?></a></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach($abandoned_cart_list as $abandoned_cart): ?>
                    <tr>
                      <td scope="row"><?php echo $abandoned_cart['customer_id']; ?></td>
                      <td><?php echo $abandoned_cart['firstname'] . " " . $abandoned_cart['lastname']; ?></td>
                      <td><?php echo $abandoned_cart['email']; ?></td>
                      <td>
                        <table class="table table-bordered" >
                          <tbody >
                            <?php foreach($abandoned_cart['products'] as $product) : ?>
                            <tr>
                              <td width="70%"><a href="<?php echo $product_url_without_id . $product['data']['product_id']; ?>"><?php echo $product['data']['name']; ?></a></td>
                              <td width="15%"><?php echo "x " . $product['quantity']; ?></td>
                              <td width="15%"><?php echo "$" . $product['data']['price']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </td>
                      <td><?php echo $abandoned_cart['sent_time'] ?: ''; ?><br></td>
                      <td><b><?php echo $abandoned_cart['is_sent'] == '1' ? 'SENT' : 'PENDING'; ?></b></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
                <div class="row">
                  <div class="col-sm-6 text-left"><?php echo $pagination; ?></div>
                  <div class="col-sm-6 text-right"><?php echo $results; ?></div>
                </div>
              </div>
            </div>
            </div> <!-- .tab-content -->
            </div> <!-- .tab-pane -->
          </form>
        </div> <!-- .panel-body -->
      </div>
    </div>
  </div>
  <link rel="stylesheet" type="text/css" href="view/stylesheet/smailyforopencart/abandoned_carts_table.css" />
<?php echo $footer; ?>
</div>
<script type="text/javascript">
(function($) {
  $(window).on("load", function() {
    // Open first tab or abandoned cart status tab;
    if (isCustomerInAbandonedCartStatusTab() == true) {
      $("#sections a:last").tab("show");
    } else {
      $("#sections a:first").tab("show");
    }
    // Hide validate display messages.
    $('#validate-alert button').on('click', function() {
      $('#validate-alert').hide();
    });
    // Populate autoresponders list
    getAutoresponders();
    function getAutoresponders() {
      // Smaily credentials.
      var subdomain = $("#subdomain").val();
      var username = $("#username").val();
      var password = $("#password").val();
      if (subdomain !='' && username !='' && password != '') {
        $.ajax({
          url:'index.php?route=extension/module/smaily_for_opencart/ajaxGetAutoresponders&token=<?php echo $token ?>',
          dataType: 'json',
          method: 'POST',
          data: {
            subdomain:subdomain,
            username:username,
            password:password
          },
          success: function(response) {
            $.each(response, function(index,value){
              $("#abandoned-autoresponder").append(
                $("<option>", {
                  value : JSON.stringify({'name':value, 'id': index}),
                  text : value
                })
              );
            });
          }
        })
      }
    }
    function switchValidateResetSection(currently_validated=false) {
      if (currently_validated) {
        // Switch reset section to validate section.
        $('#reset-title').hide();
        $('#validate-title').show();
        $('#reset-credentials').hide();
        $('#validate').show();
      } else {
        // Switch validate section to reset section.
        $('#reset-title').show();
        $('#validate-title').hide();
        $('#reset-credentials').show();
        $('#validate').hide();
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
      var spinner = $("#smaily-validate-loader");
      var subdomain = $("#subdomain").val();
      var username = $("#username").val();
      var password = $("#password").val();
      var validateDiv = $('#validate-alert');

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

      spinner.show();
      $.ajax({
        url: 'index.php?route=extension/module/smaily_for_opencart/ajaxValidateCredentials&token=<?php echo $token ?>',
        dataType: 'json',
        method: "POST",
        data: {
          subdomain:subdomain,
          username:username,
          password:password
        },
        success: function(response) {
          spinner.hide();
          // Error message
          if (response['error']) {
            $('#validate-message').text(response['error']);
            validateDiv.addClass('alert-danger').show();
          } else if (!response) {
            $('#validate-message').text('Something went wrong with request to smaily');
            validateDiv.addClass('alert-danger').show();
          }
          // Success message.
          if (response['success']) {
            // Get autoresponders.
            getAutoresponders();
            // Remove alert messages.
            $('div.text-danger').hide();
            // Remove form group has-error
            $('div.has-error').removeClass('has-error').addClass('has-success');
            // Add text, remove danger class had errors.
            $('#validate-message').text(response['success']);
            // Show response message.
            validateDiv.addClass('alert-success');
            validateDiv.removeClass('alert-danger');
            validateDiv.show();
            switchValidateResetSection();
            // Set module status to enabled.
            $('#input-status').val("1");
          }
        },
        error: function(error) {
          // Hide spinner.
          spinner.hide();
          $('#validate-message').text('No connection to smaily');
          validateDiv.addClass('alert-danger').show();
        }
      });
    });
  // Reset credentials.
  $('#reset-credentials').on('click', function(e) {
    // Scroll top.
    $("html, body").animate(
      {
        scrollTop: "0px"
      },
      "slow"
    );
    var spinner = $('#smaily-reset-loader');
    spinner.show();

    $.ajax({
      url: 'index.php?route=extension/module/smaily_for_opencart/ajaxResetCredentials&token=<?php echo $token ?>',
      dataType: 'json',
      method: "POST",
      success: function(response) {
        spinner.hide();
        if (response['success']) {
          // Remove success style from credentials input.
          $('div.has-success').removeClass('has-success');
          // Show response
          $('#validate-message').text(response['success']);
          $('#validate-alert').addClass('alert-success').show();
          // Disable module functions.
          $('#input-status').val('0');
          $('#input-subscriber-status').val('0');
          $('#input-abandoned-status').val('0');
          // Reset Smaily credentials.
          $("#subdomain").val('');
          $("#username").val('');
          $("#password").val('');
          switchValidateResetSection(true);
        }
      },
      error: function(error) {
        spinner.hide();
        $('#validate-message').text('Something went wrong!');
        $('#validate-alert').addClass('alert-danger').show();
      }
    });
   });

   var smaily_rss_url_base = '<?php echo $smaily_rss_url_base; ?>';
   $(".smaily-rss-options").change(function (event) {
      var rss_url_base = smaily_rss_url_base + '&';
      var parameters = {};

      var rss_category = $('#rss-category').val();
      if (rss_category) {
        parameters.category = rss_category;
      }

      var rss_sort_by = $('#rss-sort-by').val();
      if (rss_sort_by != "none") {
        parameters.sort_by = rss_sort_by;
      }

      var rss_sort_order = $('#rss-sort-order').val();
      if (rss_sort_order != "") {
        parameters.sort_order = rss_sort_order;
      }

      var rss_limit = $('#rss-limit').val();
      if (rss_limit != "") {
        parameters.limit = rss_limit;
      }
      $('#smaily-rss-feed-url').html(rss_url_base + $.param(parameters));
    });
    function isCustomerInAbandonedCartStatusTab() {
      var search_params = ['sort', 'order', 'page'];
      var query_parameters = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
      var i;
      for (i = 0; i < query_parameters.length; i++) {
        parameter = query_parameters[i].split('=')[0];
        if ($.inArray(parameter, search_params) !== -1) {
          return true;
        }
      }
      return false;
    }
  });
})(jQuery);
</script>
