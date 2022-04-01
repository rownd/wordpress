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
					<input type='text' id='rownd-app-id-field' name='rownd_settings[app_key]' value='<?php echo $options['app_key']; ?>' />
				</div>

				<div class='rownd-field-wrapper'>
					<label><?php _e('App secret:', 'rownd'); ?></label>
					<input type='password' id='rownd-app-id-field' name='rownd_settings[app_secret]' value='<?php echo $options['app_secret']; ?>' />
				</div>
			</div>

			<div class="rownd-field-group">
				<h2>Advanced settings</h2>
				<div class="rownd-field-group-description">Only change these values if instructed by a Rownd support engineer.</div>
				<div class="rownd-field-wrapper">
					<label><?php _e('API URL:', 'rownd'); ?></label>
					<input type='text' id='rownd-api-url-field' name='rownd_settings[api_url]' value='<?php echo $options['api_url']; ?>' />
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
