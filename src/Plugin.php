<?php

namespace Rownd\WordPress;

class Plugin
{

	var $rownd_settings;

	function __construct()
	{
		$this->rownd_settings = get_option(ROWND_PLUGIN_SETTINGS);
		// add_action('init', array($this, 'session_init')); //start the session if not started yet.
		register_activation_hook(__FILE__, array($this, 'plugin_activation')); //load the default setting for the plugin while activating
		add_action('init', array($this, 'plugin_text_domain')); //load the plugin text domain
		add_action('rest_api_init', array($this, 'register_plugin_api'));
		add_action('admin_menu', array($this, 'add_rownd_menu')); //register the plugin menu in backend
		add_action('admin_enqueue_scripts', array($this, 'register_admin_assets')); //registers all the assets required for wp-admin
		add_action('wp_enqueue_scripts', array($this, 'register_frontend_assets')); // registers all the assets required for the frontend
		add_action('admin_post_rownd_save_settings', array($this, 'save_settings')); //save settings of a plugin
	}

	function plugin_activation()
	{
		if (!get_option(ROWND_PLUGIN_SETTINGS)) {
			include_once('lib/activation.php');
		}
	}

	function register_plugin_api()
	{
		register_rest_route('rownd/v1', '/auth', array(
			'methods' => 'POST',
			'callback' => array($this, 'handle_authenticate'),
		));
	}

	//loads the text domain for translation
	function plugin_text_domain()
	{
		load_plugin_textdomain('rownd', false, ROWND_PLUGIN_LANG_DIR);
	}

	//register the plugin menu for backend.
	function add_rownd_menu()
	{
		add_menu_page('Rownd', 'Rownd', 'manage_options', 'rownd', array($this, 'main_page'), ROWND_PLUGIN_IMAGE_DIR . '/icon.png');
	}
	//menu page
	function main_page()
	{
		include_once('pages/admin/main.php');
	}

	//save the settings of a plugin
	function save_settings()
	{
		if (isset($_POST['rownd_save_settings']) && $_POST['rownd_settings_action'] && wp_verify_nonce($_POST['rownd_settings_action'], 'rownd_nonce_save_settings')) {
			include_once('lib/save-settings.php');
		} else {
			die('No script kiddies please!');
		}
	}

	//registration of the plugins frontend assets
	function register_frontend_assets()
	{
		//register frontend scripts
		wp_enqueue_script('rownd-hub-js', ROWND_PLUGIN_JS_DIR . '/hub.js', ROWND_PLUGIN_VERSION);
		wp_localize_script('rownd-hub-js', 'rownd_config_object', array(
			'app_key' => $this->rownd_settings['app_key'],
			'nonce' => wp_create_nonce( 'wp_rest' )
		));
		//register frontend css
		// wp_enqueue_style( 'fontawsome-css', '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css', '', APSL_VERSION );
	}

	function register_admin_assets() {
		wp_enqueue_style( 'rowwnd-admin-css', ROWND_PLUGIN_CSS_DIR . '/admin.css', '', ROWND_PLUGIN_VERSION );
	}

	function handle_authenticate($data)
	{
		$respData = new \stdClass();
		$statusCode = 200;
		$token = $data['access_token'];

		$respData->should_refresh_page = false;

		try {
			$rowndClient = lib\RowndClient::getInstance();
			$decodedToken = $rowndClient->validateToken($token);

			// Ensure the WP user session is set
			if (!is_user_logged_in()) {
				$respData->should_refresh_page = true;
				$authenticator = new lib\Authenticator();
				$authenticator->signInUser($decodedToken);
			}

			$respData->message = 'Authentication successful';
		} catch (\Exception $e) {
			$statusCode = 500;
			$respData->message = $e->getMessage();
			$respData->should_refresh_page = false;
		}

		$response = new \WP_REST_Response($respData);
		$response->set_status($statusCode);

		return $response;
	}
}
