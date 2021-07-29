<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <script type="text/javascript">var smailyforopencart_settings = {
    'rss_base_url': '<?php echo $rss["feed_base_url"]; ?>'
  };</script>
  <link rel="stylesheet" type="text/css" href="view/stylesheet/smaily_for_opencart/admin.css"/>

	<div class="page-header">
		<div class="container-fluid">
			<div class="pull-right">
				<button type="submit" form="form-smaily_for_opencart" data-toggle="tooltip" title="<?php echo $t['button_save']; ?>" class="btn btn-primary">
					<i class="fa fa-save"></i>
				</button>
				<a href="<?php echo $cancel_url; ?>" data-toggle="tooltip" title="<?php echo $t['button_cancel']; ?>" class="btn btn-default">
					<i class="fa fa-reply"></i>
				</a>
			</div>
			<h1><?php echo $t['heading_title']; ?></h1>
			<ul class="breadcrumb">
				<?php foreach ($breadcrumbs as $breadcrumb): ?>
					<li>
						<a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
	<div class="container-fluid">
    <?php if ($success): ?>
      <div class="alert alert-success">
        <i class="fa fa-exclamation-circle"></i>
        <?php echo $success; ?>
        <button type="button" class="close" area-label="Close" data-dismiss="alert">&times;</button>
      </div>
	  <?php endif; ?>
    <?php if (!empty($errors)): ?>
    <?php foreach ($errors as $error): ?>
      <div class="alert alert-danger">
        <i class="fa fa-exclamation-circle"></i>
        <?php echo $error; ?>
        <button type="button" class="close" area-label="Close" data-dismiss="alert">&times;</button>
      </div>
    <?php endforeach; ?>
		<?php endif; ?>

    <form action="<?php echo $action_url; ?>" autocomplete="off" method="post" enctype="multipart/form-data" id="form-module" class="form-horizontal">
      <?php if ($settings['validated']): ?>
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title">
            <i class="fa fa-plug"></i>
            <?php echo $t['heading_connection_status']; ?></h3>
        </div>
        <div class="panel-body">
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-status">&nbsp;</label>
            <div class="col-sm-7">
              <strong class="text-success text-uppercase"><?php echo $t['text_connected']; ?></strong><br/>
              <?php echo str_replace(array('%subdomain%', '%username%'), array($settings['api_subdomain'], $settings['api_username']), $t['help_connected']); ?>
            </div>
            <div class="col-sm-3">
              <a href="<?php echo $reset_credentials_url; ?>" title="<?php echo $t['button_reset_credentials']; ?>" class="btn btn-danger pull-right">
                <?php echo $t['button_reset_credentials']; ?>
              </a>
            </div>
          </div>
        </div>
      </div>
      <?php else: ?>
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title">
            <i class="fa fa-pencil"></i>
            <?php echo $t['heading_connection_status']; ?></h3>
        </div>
        <div class="panel-body">
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-status">&nbsp;</label>
            <div class="col-sm-10">
              <p>
                <strong class="text-danger text-uppercase"><?php echo $t['text_disconnected']; ?></strong><br/>
                <?php echo $t['help_disconnected']; ?>
                <a href="https://smaily.com/help/api/general/create-api-user/" target="_blank"><?php echo $t['help_api_password']; ?></a>
              </p>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label" for="api-subdomain">
              <?php echo $t['label_api_subdomain']; ?>
            </label>
            <div class="col-sm-10">
              <input class="form-control" id="api-subdomain" name="api_subdomain" placeholder="<?php echo $t['placeholder_api_subdomain']; ?>" type="text" value="<?php echo $settings['api_subdomain']; ?>"/>

              <?php if (isset($errors['api_subdomain'])): ?>
              <div class="text-danger"><?php echo $errors['api_subdomain']; ?></div>
              <?php endif; ?>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="api-username">
              <?php echo $t['label_api_username']; ?>
            </label>
            <div class="col-sm-10">
              <input class="form-control" id="api-username" name="api_username" placeholder="<?php echo $t['placeholder_api_username']; ?>" type="text" value="<?php echo $settings['api_username']; ?>"/>

              <?php if (isset($errors['api_username'])): ?>
              <div class="text-danger"><?php echo $errors['api_username']; ?></div>
              <?php endif; ?>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="api-password">
              <?php echo $t['label_api_password']; ?>
            </label>
            <div class="col-sm-10">
              <input class="form-control" id="api-password" name="api_password" placeholder="<?php echo $t['placeholder_api_password']; ?>" type="password" value="<?php echo $settings['api_password']; ?>"/>

              <?php if (isset($errors['api_password'])): ?>
              <div class="text-danger"><?php echo $errors['api_password']; ?></div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">
						<i class="fa fa-pencil"></i>
						<?php echo $t['heading_edit']; ?></h3>
				</div>
				<div class="panel-body">
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-status"><?php echo $t['label_enabled']; ?></label>
            <div class="col-sm-10">
              <select name="status" id="input-status" class="form-control">
                <option value="1"<?php if ($settings['status'] == true): ?> selected<?php endif; ?>><?php echo $t['text_yes']; ?></option>
                <option value="0"<?php if ($settings['status'] == false): ?> selected<?php endif; ?>><?php echo $t['text_no']; ?></option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">
						<i class="fa fa-pencil"></i>
						<?php echo $t['heading_customer_sync']; ?></h3>
				</div>
				<div class="panel-body">
          <div class="form-group">
            <label class="col-sm-2 control-label" for="customer-sync-enabled"><?php echo $t['label_enabled']; ?></label>
            <div class="col-sm-10">
              <select name="customer_sync_enabled" id="customer-sync-enabled" class="form-control">
                <option value="1"<?php if ($settings['customer_sync_enabled'] == true): ?> selected<?php endif; ?>><?php echo $t['text_yes']; ?></option>
                <option value="0"<?php if ($settings['customer_sync_enabled'] == false): ?> selected<?php endif; ?>><?php echo $t['text_no']; ?></option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="customer-sync-fields"><?php echo $t['label_customer_sync_fields']; ?></label>
            <div class="col-sm-10">
              <select class="form-control" name="customer_sync_fields[]" id="customer-sync-fields" multiple="multiple" size="4">
                <?php foreach ($customer_sync['field_options'] as $option): ?>
                  <option value="<?php echo $option['value']; ?>"<?php if ($option['selected']): ?> selected<?php endif; ?>><?php echo $option['label']; ?></option>
                <?php endforeach; ?>
              </select>

              <div class="help-block"><?php echo $t['help_customer_sync_fields']; ?></div>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $t['label_customer_sync_cron_url']; ?></label>
            <div class="col-sm-8">
              <strong><?php echo $customer_sync['cron_url']; ?></strong><br>
              <span class="text-muted"><?php echo $t['help_customer_sync_cron_url']; ?></span>
            </div>
            <div class="col-sm-2">
              <a class="btn btn-danger pull-right" href="<?php echo $customer_sync['reset_token_url']; ?>"><?php echo $t['text_reset_customer_sync_cron_token']; ?></a>
            </div>
          </div>
        </div>
      </div>

      <div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">
						<i class="fa fa-pencil"></i>
						<?php echo $t['heading_abandoned_cart']; ?></h3>
				</div>
				<div class="panel-body">
          <div class="form-group">
            <label class="col-sm-2 control-label" for="abandoned-cart-enabled"><?php echo $t['label_enabled']; ?></label>
            <div class="col-sm-10">
              <select name="abandoned_cart_enabled" id="abandoned-cart-enabled" class="form-control">
                <option value="1"<?php if ($settings['abandoned_cart_enabled'] == true): ?> selected<?php endif; ?>><?php echo $t['text_yes']; ?></option>
                <option value="0"<?php if ($settings['abandoned_cart_enabled'] == false): ?> selected<?php endif; ?>><?php echo $t['text_no']; ?></option>
              </select>
            </div>
          </div>
          <div class="form-group<?php if (isset($errors['abandoned_cart_autoresponder'])): ?> has-error<?php endif; ?>">
            <label class="col-sm-2 control-label" for="abandoned-cart-autoresponder"><?php echo $t['label_abandoned_cart_autoresponder']; ?></label>
            <div class="col-sm-10">
              <select name="abandoned_cart_autoresponder" id="abandoned-cart-autoresponder" class="form-control">
                <?php if (abandoned_cart['automation_options']): ?>
                  <?php foreach ($abandoned_cart['automation_options'] as $option): ?>
                  <option value="<?php echo $option['value']; ?>"<?php if ($option['selected']): ?> selected<?php endif; ?>><?php echo $option['label']; ?></option>
                  <?php endforeach; ?>
                <?php else: ?>
                  <option value=""><?php echo $t['text_missing_automation_workflows']; ?></option>
                <?php endif; ?>
              </select>

              <div class="help-block"><?php echo $t['help_abandoned_cart_autoresponder']; ?></div>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="abandoned-cart-fields"><?php echo $t['label_abandoned_cart_fields']; ?></label>
            <div class="col-sm-10">
              <select class="form-control" name="abandoned_cart_fields[]" id="abandoned-cart-fields" multiple="multiple" size="8">
                <?php foreach ($abandoned_cart['field_options'] as $option): ?>
                  <option value="<?php echo $option['value']; ?>"<?php if ($option['selected']): ?> selected<?php endif; ?>><?php echo $option['label']; ?></option>
                <?php endforeach; ?>
              </select>
              <div class="help-block"><?php echo $t['help_abandoned_cart_fields']; ?></div>
            </div>
          </div>
          <div class="form-group<?php if (isset($errors['abandoned_cart_delay'])): ?> has-error<?php endif; ?>">
            <label class="col-sm-2 control-label" for="abandoned-cart-delay"><?php echo $t['label_abandoned_cart_delay']; ?></label>
            <div class="col-sm-10">
              <div class="input-group">
                <input class="form-control" id="abandoned-cart-delay" min="15" name="abandoned_cart_delay" type="number" value="<?php echo $settings['abandoned_cart_delay']; ?>"/>
                <span class="input-group-addon"><?php echo $t['text_minutes']; ?></span>
              </div>

              <div class="help-block"><?php echo $t['help_abandoned_cart_delay']; ?></div>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label"><?php echo $t['label_abandoned_cart_cron_url']; ?></label>
            <div class="col-sm-8">
              <strong><?php echo $abandoned_cart['cron_url']; ?></strong><br/>
              <span class="text-muted"><?php echo $t['help_abandoned_cart_cron_url']; ?></span>
            </div>
            <div class="col-sm-2">
              <a class="btn btn-danger pull-right" href="<?php echo $abandoned_cart['reset_token_url']; ?>"><?php echo $t['text_reset_abandoned_cart_cron_token']; ?></a>
            </div>
          </div>
        </div>
      </div>

      <div id="smaily-rss-options" class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">
						<i class="fa fa-pencil"></i>
						<?php echo $t['heading_rss']; ?></h3>
				</div>
				<div class="panel-body">
          <div class="form-group">
            <label class="col-sm-2 control-label" for="rss-category"><?php echo $t['label_rss_category']; ?></label>
            <div class="col-sm-10">
              <select name="rss_category" id="rss-category" class="form-control smaily-rss-options">
                <option value=""><?php echo $t['text_all_products']; ?></option>
                <?php foreach ($rss['categories'] as $category): ?>
                  <option value="<?php echo $category['value']; ?>"<?php if ($category['selected']): ?> selected<?php endif; ?>><?php echo $category['label']; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="rss-sort-by"><?php echo $t['label_rss_sort_by']; ?></label>
            <div class="col-sm-10">
              <select name="rss_sort_by" id="rss-sort-by" class="form-control smaily-rss-options">
                <?php foreach ($rss['sort_by_options'] as $option): ?>
                  <option value="<?php echo $option['value']; ?>"<?php if ($option['selected']): ?> selected<?php endif; ?>><?php echo $option['label']; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="rss-sort-order"><?php echo $t['label_rss_sort_order']; ?></label>
            <div class="col-sm-10">
              <select name="rss_sort_order" id="rss-sort-order" class="form-control smaily-rss-options">
                <?php foreach ($rss['sort_order_options'] as $option): ?>
                  <option value="<?php echo $option['value']; ?>"<?php if ($option['selected']): ?> selected<?php endif; ?>><?php echo $option['label']; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="form-group<?php if (isset($errors['rss_limit'])): ?> has-error<?php endif; ?>">
            <label class="col-sm-2 control-label" for="rss-limit"><?php echo $t['label_rss_limit']; ?></label>
            <div class="col-sm-10">
              <div class="input-group">
                <input class="form-control smaily-rss-options" id="rss-limit" max="250" min="1" name="rss_limit" type="number" value="<?php echo $settings['rss_limit']; ?>"/>
                <span class="input-group-addon"><?php echo $t['text_products']; ?></span>
              </div>

              <div class="help-block"><?php echo $t['help_rss_limit']; ?></div>
            </div>
          </div>
          <div class="form-group">
            <label for="rss-url" class="col-sm-2 control-label"><?php echo $t['label_rss_feed_url']; ?></label>
            <div class="col-sm-10">
              <strong id="rss-url"><?php echo $rss['feed_url']; ?></strong><br/>
              <span class="text-muted"><?php echo $t['help_rss_feed_url']; ?></span>
            </div>
          </div>
        </div>
      </div>
    </form>

    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 id="abandoned-carts" class="panel-title">
          <i class="fa fa-list"></i>
          <?php echo $t['heading_abandoned_carts']; ?></h3>
      </div>
      <div class="panel-body">
        <table class="table table-bordered table-hover smaily-abandoned-carts-table">
          <thead>
            <tr>
              <th scope="col"><?php echo $t['label_abandoned_cart_table_id']; ?></th>
              <th class="text-left">
                <a href="<?php echo $abandoned_carts_table['sort_name_url']; ?>"<?php if ($abandoned_carts_table['current_sort_by'] == 'lastname'): ?> class="<?php echo $abandoned_carts_table['current_sort_order']; ?>"<?php endif; ?>><?php echo $t['label_abandoned_cart_table_name']; ?></a>
              </th>
              <th class="text-left">
                <a href="<?php echo $abandoned_carts_table['sort_email_url']; ?>"<?php if ($abandoned_carts_table['current_sort_by'] == 'email'): ?> class="<?php echo $abandoned_carts_table['current_sort_order']; ?>"<?php endif; ?>><?php echo $t['label_abandoned_cart_table_email']; ?></a>
              </th>
              <th class="text-left"><?php echo $t['label_abandoned_cart_table_cart']; ?></th>
              <th class="text-left">
                <a href="<?php echo $abandoned_carts_table['sort_date_url']; ?>"<?php if ($abandoned_carts_table['current_sort_by'] == 'sent_time'): ?> class="<?php echo $abandoned_carts_table['current_sort_order']; ?>"<?php endif; ?>><?php echo $t['label_abandoned_cart_table_date']; ?></a>
              </th>
              <th class="text-left">
                <a href="<?php echo $abandoned_carts_table['sort_status_url']; ?>"<?php if ($abandoned_carts_table['current_sort_by'] == 'is_sent'): ?> class="<?php echo $abandoned_carts_table['current_sort_order']; ?>"<?php endif; ?>><?php echo $t['label_abandoned_cart_table_status']; ?></a>
              </th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($abandoned_carts_table['collection'] as $abandoned_cart): ?>
              <tr>
                <td scope="row"><?php echo $abandoned_cart['customer_id']; ?></td>
                <td><?php echo $abandoned_cart['firstname'] . " " . $abandoned_cart['lastname']; ?></td>
                <td><?php echo $abandoned_cart['email']; ?></td>
                <td>
                  <table class="table table-bordered">
                    <tbody>
                      <?php foreach ($abandoned_cart['products'] as $product): ?>
                        <tr>
                          <td width="70%"><a href="<?php echo $abandoned_carts_table['product_base_url'] . $product['product_id']; ?>" target="_blank" rel="noopener"><?php echo $product['name']; ?></a></td>
                          <td width="15%"><?php echo "x " . $product['quantity']; ?></td>
                          <td width="15%"><?php echo "$" . $product['price']; ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                  <td><?php echo $abandoned_cart['sent_time'] ?: ''; ?></td>
                  <td><strong class="text-uppercase"><?php echo $abandoned_cart['is_sent'] == '1' ? $t['text_sent'] : $t['text_pending']; ?></strong></td>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <div class="row">
          <div class="col-sm-6 text-left"><?php echo $abandoned_carts_table['pagination']; ?></div>
          <div class="col-sm-6 text-muted text-right"><?php echo $abandoned_carts_table['results']; ?></div>
        </div>
      </div>
    </div>

    <div class="col-sm-12 help-block text-right">
      <?php echo $t['heading_title']; ?>
      <strong>v<?php echo $settings['db_version']; ?></strong>
    </div>
  </div><!-- /.container-fluid -->
</div>
<?php echo $footer; ?>
