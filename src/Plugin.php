<?php

namespace Rownd\WordPress;

class Plugin
{

	var $rownd_settings;

	function __construct()
	{
		$this->rownd_settings = get_option(ROWND_PLUGIN_SETTINGS);
		add_action('init', array($this, 'plugin_text_domain')); //load the plugin text domain
		add_action('rest_api_init', array($this, 'register_plugin_api'));
		add_action('admin_menu', array($this, 'add_rownd_menu')); //register the plugin menu in backend
		add_action('admin_enqueue_scripts', array($this, 'register_admin_assets')); //registers all the assets required for wp-admin
		add_action('wp_enqueue_scripts', array($this, 'register_frontend_assets')); // registers all the assets required for the frontend
		add_action('admin_post_rownd_save_settings', array($this, 'save_settings')); //save settings of a plugin
		add_filter('plugin_action_links_rownd-accounts-and-authentication/index.php', array($this, 'plugin_action_links'), 10, 2);
		add_filter('plugin_row_meta', array($this, 'plugin_row_meta'), 10, 4);
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
		add_menu_page('Rownd', 'Rownd', 'manage_options', 'rownd', array($this, 'main_page'), 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 128"> <g> <path fill="black" d="M92.64,82.58c-5.63,9.61-13.51,17.5-24,24l.24.13.91.48.4.21.84.43q.85.45,1.71.87l.53.25,1.2.58.67.3,1.08.49.72.3,1,.45.76.3,1,.41.79.3,1,.38.8.28,1,.35c.27.1.54.18.82.27l1,.33L86,114l1,.3.85.24,1,.28.88.23,1,.25.89.22,1,.23.9.19,1,.21.92.18,1,.19.92.16,1,.16.94.15.58.08A63.88,63.88,0,0,0,128,64a64.93,64.93,0,0,0-.53-8.25c-7-1.71-14-3.7-20.83-5.93L102,48.31c0,1.27-.09,2.54-.19,3.79A69.75,69.75,0,0,1,92.64,82.58Z"/> <path fill="black" d="M126.59,50.58A64.08,64.08,0,0,0,44.38,3.06a88.78,88.78,0,0,0-7.6,14.81A82.87,82.87,0,0,0,31.24,43.3c0,.15,0,.3,0,.45,5.45-2.06,10.85-4.37,16.08-6.91a193.42,193.42,0,0,0,18-9.93l1.22-.76,1.21.79A149.63,149.63,0,0,0,89.4,38.69c2.87,1.26,5.49,2.29,8,3.23,3.53,1.3,6.91,2.4,10.58,3.59,6.17,2,12.42,3.81,18.65,5.39C126.64,50.79,126.61,50.68,126.59,50.58Z"/> <path fill="black" d="M36,82.72a67.75,67.75,0,0,1-9.09-30.47,78.38,78.38,0,0,1-.17-9.36c0-.5.08-1.73.21-3.18a82.35,82.35,0,0,1,5.61-23.42l.12-.31A86.25,86.25,0,0,1,37.5,5.73,64.47,64.47,0,0,0,5.86,37.21a64,64,0,0,0,88,83.39l-.11,0c-.94-.19-1.87-.39-2.81-.61l-.58-.14c-.94-.22-1.88-.46-2.81-.71l-.5-.14q-1.35-.37-2.67-.78l-.27-.08c-.93-.29-1.86-.6-2.79-.92l-.56-.2c-.92-.33-1.84-.66-2.74-1l-.31-.12c-.85-.33-1.7-.68-2.54-1l-.45-.19c-.88-.39-1.76-.79-2.63-1.2l-.54-.25c-.89-.43-1.78-.87-2.66-1.32-1.66-.87-3.74-2-6-3.23l-3.47-2,.09-.05A67.85,67.85,0,0,1,36,82.72Z"/> </g></svg>'));
	}

	// show extra links in the plugins meta list
	function plugin_action_links($links_array, $plugin_file_name)
	{
		array_unshift($links_array, '<a href="/wp-admin/admin.php?page=rownd">Settings</a>');
		return $links_array;
	}

	// show extra links in the plugins list
	function plugin_row_meta($links_array, $plugin_file_name, $plugin_data, $status)
	{
		if ($plugin_file_name == 'rownd/index.php') {
			$links_array[] = '<a href="https://docs.rownd.io/rownd/sdk-reference/web/wordpress" target="_blank" rel="noreferrer">Docs</a>';
			$links_array[] = '<a href="mailto:support@rownd.io">Support</a>';
		}

		return $links_array;
	}

	// admin page
	function main_page()
	{
		include_once('pages/admin/main.php');
	}

	// save the settings from the admin page
	function save_settings()
	{
		if (isset($_POST['rownd_save_settings']) && $_POST['rownd_settings_action'] && wp_verify_nonce($_POST['rownd_settings_action'], 'rownd_nonce_save_settings')) {
			include_once('lib/save-settings.php');
		} else {
			die('No script kiddies please!');
		}
	}

	// registration of the plugins frontend assets
	function register_frontend_assets()
	{
		//register frontend scripts
		wp_enqueue_script('rownd-hub-js', ROWND_PLUGIN_JS_DIR . '/hub.js', ROWND_PLUGIN_VERSION);

		$scriptVars = array(
			'app_key' => $this->rownd_settings['app_key'],
			'nonce' => wp_create_nonce('wp_rest'),
			'start_wp_session' => 'on',
		);

		if (!empty($this->rownd_settings['root_origin'])) {
			$scriptVars['root_origin'] = $this->rownd_settings['root_origin'];
		}

		// Disables starting a WP session / creating WP users if the option is explicitly disabled
		if (($this->rownd_settings['add_users_to_wordpress'] ?? '1') == '0') {
			$scriptVars['start_wp_session'] = 'off';
		}

		wp_localize_script('rownd-hub-js', 'rownd_config_object', $scriptVars);
	}

	function register_admin_assets()
	{
		wp_enqueue_style('rowwnd-admin-css', ROWND_PLUGIN_CSS_DIR . '/admin.css', '', ROWND_PLUGIN_VERSION);
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
