<?php

/**
 * Plugin Name:     Rownd Accounts and Authentication
 * Plugin URI:      https://github.com/rownd/wordpress
 * Description:     Instantly turn visitors into users with Rownd's radically simple, user-centric authentication.
 * Author:          Rownd, Inc.
 * Author URI:      https://rownd.io
 * Text Domain:     rownd
 * Domain Path:     /languages
 * Version:         1.0.0
 * License:		    Apache 2.0
 * License URI:     http://www.apache.org/licenses/LICENSE-2.0
 *
 * @package         Rownd
 */

// Your code starts here.
/**
 * @global string $plugin_dir_url
 */

if (!defined('ROWND_PLUGIN_VERSION')) {
	define('ROWND_PLUGIN_VERSION', '1.0.0');
}

if (!defined('ROWND_PLUGIN_IMAGE_DIR')) {
	define('ROWND_PLUGIN_IMAGE_DIR', plugin_dir_url(__FILE__) . 'images');
}

if (!defined('ROWND_PLUGIN_JS_DIR')) {
	define('ROWND_PLUGIN_JS_DIR', plugin_dir_url(__FILE__) . 'js');
}

if (!defined('ROWND_PLUGIN_CSS_DIR')) {
	define('ROWND_PLUGIN_CSS_DIR', plugin_dir_url(__FILE__) . 'css');
}

if (!defined('ROWND_PLUGIN_LANG_DIR')) {
	define('ROWND_PLUGIN_LANG_DIR', basename(dirname(__FILE__)) . '/languages/');
}

if (!defined('ROWND_PLUGIN_TEXT_DOMAIN')) {
	define('ROWND_PLUGIN_TEXT_DOMAIN', 'rownd-plugin');
}

if (!defined('ROWND_PLUGIN_SETTINGS')) {
	define('ROWND_PLUGIN_SETTINGS', 'rownd-plugin-settings');
}

if (!defined('ROWND_PLUGIN_PLUGIN_DIR')) {
	define('ROWND_PLUGIN_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

// require all of our src files
require_once(plugin_dir_path(__FILE__) . '/vendor/autoload.php');

register_activation_hook(__FILE__, 'rownd_on_activation');

function rownd_on_activation()
{
	if (!current_user_can('activate_plugins')) {
		return;
	}

	$plugin = isset($_REQUEST['plugin']) ? sanitize_text_field($_REQUEST['plugin']) : '';
	check_admin_referer("activate-plugin_{$plugin}");

	if (!get_option(ROWND_PLUGIN_SETTINGS)) {
		include_once('src/lib/activation.php');
	}

	# Uncomment the following line to see the function in action
	// exit( var_dump( $_GET ) );
}


new Rownd\WordPress\Plugin();
