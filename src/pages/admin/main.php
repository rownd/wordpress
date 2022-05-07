<?php defined('ABSPATH') or die("No script kiddies please!"); ?>
<?php
$options = get_option(ROWND_PLUGIN_SETTINGS);

?>
<div class="wrap rownd-settings">
	<div class="rownd-settings-main">
		<h1>Rownd settings</h1>

		<form method="post" action="<?php echo admin_url() . 'admin-post.php' ?>">
			<input type="hidden" name="action" value="rownd_save_settings" />
			<div class="rownd-field-group">
				<h2>API credentials</h2>
				<div class="rownd-field-group-description">Enter your Rownd app keys obtained from the <a href="https://app.rownd.io" target="_blank" rel="noreferrer">Rownd dashboard.</a></div>
				<div class='rownd-field-wrapper'>
					<label><?php _e('App key:', 'rownd'); ?></label>
					<input type='text' id='rownd-app-key-field' name='rownd_settings[app_key]' value='<?php echo esc_html($options['app_key']); ?>' />
				</div>

				<div class='rownd-field-wrapper'>
					<label><?php _e('App secret:', 'rownd'); ?></label>
					<input type='password' id='rownd-app-secret-field' name='rownd_settings[app_secret]' value='<?php echo esc_html($options['app_secret']); ?>' />
				</div>
			</div>

			<div class="rownd-field-group">
				<h2>Optional settings</h2>
				<div class="rownd-field-group-description">These are optional fields that are only needed in certain circumstances.</div>
				<div class='rownd-field-wrapper'>
					<label><?php _e('Create/update users in WordPress when they sign in with Rownd:', 'rownd'); ?></label>
					<select id='rownd-root-origin-field' name='rownd_settings[add_users_to_wordpress]'>
						<option value='1' <?php echo ($options['add_users_to_wordpress'] ?? '1') == 1 ? 'selected' : ''; ?>><?php _e('Yes', 'rownd'); ?></option>
						<option value='0' <?php echo ($options['add_users_to_wordpress'] ?? '1') == 0 ? 'selected' : ''; ?>><?php _e('No', 'rownd'); ?></option>
					</select>
				</div>
				<div class='rownd-field-wrapper'>
					<label><?php _e('Root origin (leave blank if unsure):', 'rownd'); ?></label>
					<input type='text' id='rownd-root-origin-field' name='rownd_settings[root_origin]' value='<?php echo esc_html($options['root_origin'] ?? ''); ?>' placeholder="https://mysite.com" />
					<p class="rownd-field-help">Use this setting when adding Rownd to multiple subdomains (e.g., <code>company.com</code> and <code>blog.company.com</code>)</p>
				</div>
			</div>

			<?php if (rownd_is_plugin_active('woocommerce/woocommerce.php')): ?>
			<div class="rownd-field-group">
				<h2>WooCommerce customizations</h2>
				<div class="rownd-field-group-description">Since WooCommerce is installed, Rownd can help you simplify customer onboarding.</div>
				<div class="rownd-field-wrapper">
					<label><?php _e('Enable WooCommerce support?', 'rownd'); ?></label>
					<select id='rownd-woo-enabled-field' name='rownd_settings[is_woocommerce_integration_enabled]'>
						<option value='1' <?php echo ($options['is_woocommerce_integration_enabled'] ?? '0') == 1 ? 'selected' : ''; ?>><?php _e('Yes', 'rownd'); ?></option>
						<option value='0' <?php echo ($options['is_woocommerce_integration_enabled'] ?? '0') == 0 ? 'selected' : ''; ?>><?php _e('No', 'rownd'); ?></option>
					</select>
					<p class="rownd-field-help">When enabled, Rownd will authenticate customers and help manage their account data.</p>
				</div>

				<div class="rownd-field-wrapper">
					<label><?php _e('When should Rownd prompt users to sign in (if not already signed-in)?', 'rownd'); ?></label>
					<select id='rownd-woo-enabled-field' name='rownd_settings[woocommerce_checkout_signin_prompt_location]'>
						<option value='before_checkout' <?php echo ($options['woocommerce_checkout_signin_prompt_location'] ?? 'before_checkout') == 'before_checkout' ? 'selected' : ''; ?>><?php _e('Before customer completes order', 'rownd'); ?></option>
						<option value='after_checkout' <?php echo ($options['woocommerce_checkout_signin_prompt_location'] ?? 'before_checkout') == 'after_checkout' ? 'selected' : ''; ?>><?php _e('After customer completes order', 'rownd'); ?></option>
					</select>
				</div>
			</div>
			<?php endif; ?>

			<div class="rownd-field-group">
				<h2>Advanced settings</h2>
				<div class="rownd-field-group-description">Only change these values if instructed by a Rownd support engineer.</div>
				<div class="rownd-field-wrapper">
					<label><?php _e('API URL:', 'rownd'); ?></label>
					<input type='text' id='rownd-api-url-field' name='rownd_settings[api_url]' value='<?php echo esc_url($options['api_url']); ?>' />
				</div>

				<div class="rownd-field-wrapper">
					<label><?php _e('Hub base URL:', 'rownd'); ?></label>
					<input type='text' id='rownd-api-url-field' name='rownd_settings[hub_base_url]' value='<?php echo esc_url($options['hub_base_url'] ?? 'https://hub.rownd.io'); ?>' />
				</div>
			</div>

			<div class="rownd-form-footer">
				<?php wp_nonce_field('rownd_nonce_save_settings', 'rownd_settings_action'); ?>
				<button type="submit" name="rownd_save_settings" class="button button-primary"><?php _e('Save settings', 'rownd'); ?></button>
			</div>
		</form>
	</div>
	<div class="rownd-info-area">
		<div class="logo-wrap">
			<a class="rownd-image-link" href="https://app.rownd.io" target="_blank" rel="noreferrer">
				<img src="<?php echo ROWND_PLUGIN_IMAGE_DIR; ?>/rownd-logo-purple-graded.svg" alt="<?php esc_attr_e('Rownd', 'rownd'); ?>" />
			</a>
		</div>
		<div class="logo-content">
			<div class='plugin-version'><?php _e('Plugin version ' . ROWND_PLUGIN_VERSION, 'rownd'); ?></div>
			<hr />
			<div>Instantly turn visitors into users and increase app onboarding rates by 25%. Get Rownd for radically simple, user-centric authentication that begins the moment a visitor hits your website—all with data privacy built into its core.</div>
			<hr />
			<div class="rownd-info-link"><a href="https://docs.rownd.io" target="_blank" rel="noreferrer"><?php _e('Documentation', 'rownd'); ?></a></div>
			<div class="rownd-info-link"><a href="https://app.rownd.io" target="_blank" rel="noreferrer"><?php _e('Rownd dashboard', 'rownd'); ?></a></div>
			<div class="rownd-info-link"><a href="mailto:support@rownd.io?subject=WordPress help" target="_blank" rel="noreferrer"><?php _e('Support', 'rownd'); ?></a></div>
		</div>
	</div>
</div>
