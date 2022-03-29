<?php defined('ABSPATH') or die("No script kiddies please!"); ?>
<?php
$options = get_option(ROWND_PLUGIN_SETTINGS);
//$this->print_array($options);

?>
<div class="wrap">
	<div class="rownd-setting-header clearfix">
		<h1>Rownd settings</h1>
		<div class="rownd-headerlogo">
			<div class="logo-wrap"> <img src="<?php echo ROWND_PLUGIN_IMAGE_DIR; ?>/logo.png" alt="<?php esc_attr_e('Rownd', 'rownd'); ?>" /></div>
			<div class="logo-content"><?php esc_attr_e('Rownd', 'rownd'); ?><br />
				<span class='plugin-version'><?php _e('version ' . ROWND_PLUGIN_VERSION, 'rownd'); ?></span>
			</div>
		</div>
	</div>

	<form method="post" action="<?php echo admin_url() . 'admin-post.php' ?>">
		<input type="hidden" name="action" value="rownd_save_settings" />
		<div class='rownd-field-wrapper'>
			<label><?php _e('App key:', 'rownd'); ?></label>
			<input type='text' id='rownd-app-id-field' name='rownd_settings[app_key]' value='<?php echo $options['app_key']; ?>' />
		</div>

		<div class='rownd-field-wrapper'>
			<label><?php _e('App secret:', 'rownd'); ?></label>
			<input type='password' id='rownd-app-id-field' name='rownd_settings[app_secret]' value='<?php echo $options['app_secret']; ?>' />
		</div>

		<div class="rownd-form-footer">
			<?php wp_nonce_field('rownd_nonce_save_settings', 'rownd_settings_action'); ?>
			<button type="submit" name="rownd_save_settings" class="button button-primary"><?php _e('Save settings', 'rownd'); ?></button>
		</div>
	</form>
</div>
