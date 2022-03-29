<?php

/**
 * Plugin Name:     Rownd Accounts and Authentication
 * Plugin URI:      https://github.com/rownd/wordpress
 * Description:     Simplify and streamline your WordPress registration and sign-in process using Rownd.
 * Author:          Rownd, Inc.
 * Author URI:      https://rownd.io
 * Text Domain:     rownd-wp-auth
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Rownd_Wp_Auth
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

new Rownd\WordPress\Plugin();
