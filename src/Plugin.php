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
		add_action('profile_update', array($this, 'update_user_profile'), 10, 3); //update user profile

		add_filter('plugin_action_links_rownd-accounts-and-authentication/index.php', array($this, 'plugin_action_links'), 10, 2);
		add_filter('plugin_row_meta', array($this, 'plugin_row_meta'), 10, 4);
		add_filter('determine_current_user', array($this, 'determine_current_user'));
		add_filter('rest_user_query', array($this, 'search_users_by_rownd_id'), 10, 2 );

		if (rownd_is_plugin_active('woocommerce/woocommerce.php')) {
			$this->setup_woocommerce();
		}
	}

	function register_plugin_api()
	{
		register_rest_route('rownd/v1', '/auth', array(
			'methods' => 'POST',
			'callback' => array($this, 'handle_authenticate'),
			'permission_callback' => '__return_true',
		));

		register_rest_route('rownd/v1', '/auth/signout', array(
			'methods' => 'POST',
			'callback' => array($this, 'handle_signout'),
			'permission_callback' => function () {
				return is_user_logged_in();
			  }
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

	function setup_woocommerce() {
		// WooCommerce integration
		if (($this->rownd_settings['is_woocommerce_integration_enabled'] ?? '0') == 0) {
			return;
		}

		if (($this->rownd_settings['woocommerce_checkout_signin_prompt_location'] ?? 'before_checkout') == 'before_checkout') {
			add_action('woocommerce_before_checkout_form', array($this, 'trigger_rownd_signin'), 10, 3);
		} else {
			add_action('woocommerce_after_order_details', array($this, 'trigger_rownd_signin'), 10, 3);
		}

		// Rownd creates WordPress users, not WooCommerce customers, so we want to run this for everyone.
		add_action( 'user_register', array($this, 'link_wc_orders_at_registration' ));

		// Replace WooCommerce login pages
		add_filter( 'wc_get_template', array($this, 'replace_woocommerce_login_page'), 1, 2 );

		// Enable searching for customers by Rownd ID
		add_filter('woocommerce_rest_customer_query', array($this, 'search_users_by_rownd_id'), 10, 2);
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
		wp_enqueue_script('rownd-hub-js', ROWND_PLUGIN_JS_DIR . '/hub.js', [], ROWND_PLUGIN_VERSION, false);

		$scriptVars = array(
			'app_key' => $this->rownd_settings['app_key'],
			'api_url' => $this->rownd_settings['api_url'] ?? 'https://api.rownd.io',
			'hub_base_url' => $this->rownd_settings['hub_base_url'] ?? 'https://hub.rownd.io',
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
				$respData->message = 'Authentication successful';
			} else {
				$respData->message = 'Already authenticated';

				// Make sure we get the Rownd user ID attached to the WP user.
				// This especially covers the case where WooCommerce signs the user
				// in as part of the checkout flow.
				$wp_user_id = get_current_user_id();
				$rownd_user_id = $decodedToken->claims->get('https://auth.rownd.io/app_user_id');

				$meta_rownd_id = get_user_meta($wp_user_id, 'rownd_id', true);
				if (empty($meta_rownd_id)) {
					update_user_meta($wp_user_id, 'rownd_id', $rownd_user_id);
				}
			}

		} catch (\Exception $e) {
			$statusCode = 500;
			$respData->message = $e->getMessage();
			$respData->should_refresh_page = false;
		}

		try {
			if (rownd_is_plugin_active('woocommerce/woocommerce.php') && ($this->rownd_settings['is_woocommerce_integration_enabled'] ?? '0') == 1 && $respData->should_refresh_page == true) {
				$this->link_wc_orders_at_registration(get_current_user_id());
			}
		} catch (\Exception $e) {
			// No-op -- just log
			rownd_write_log($e->getMessage());
		}

		$response = new \WP_REST_Response($respData);
		$response->set_status($statusCode);

		return $response;
	}

	function handle_signout() {
		wp_logout();
		$respData = new \stdClass();
		$respData->message = 'Sign out successful';
		$respData->return_to = $this->rownd_settings['redirect_to_after_sign_out'] ?? '/';

		$response = new \WP_REST_Response($respData);
		$response->set_status(200);

		return $response;
	}

	function determine_current_user($user_id) {
		/**
		 * This hook only should run on the REST API requests to determine
		 * if the user in the Token (if any) is valid, for any other
		 * normal call ex. wp-admin/.* return the user.
		 */
		$this->rest_api_slug = get_option( 'permalink_structure' ) ? rest_get_url_prefix() : '?rest_route=/';

		// Make sure this is a REST API request and that it's not to our post-authentication endpoint for browser access
		$valid_api_uri = stripos( $_SERVER['REQUEST_URI'], $this->rest_api_slug ) && !stripos( $_SERVER['REQUEST_URI'], $this->rest_api_slug . '/rownd/v1/auth');

		if ( ! $valid_api_uri ) {
			return $user_id;
		}

		$token = isset( $_SERVER['HTTP_AUTHORIZATION'] ) ? $_SERVER['HTTP_AUTHORIZATION'] : false;
		$startsWithBearer = strpos( strtolower($token), 'bearer ' ) === 0;

		// If no token or doesn't start with "bearer", this won't be something Rownd can handle
		if (!$token || !$startsWithBearer) {
			return $user_id;
		}

		$token = preg_replace('/^Bearer\s/i', '', $token);

		try {
			$rowndClient = lib\RowndClient::getInstance();
			$decodedToken = $rowndClient->validateToken($token);

			$authenticator = new lib\Authenticator();
			$user_id = $authenticator->authenticateUser($decodedToken);

			return $user_id;
		} catch (\Exception $e) {
			error_log('Failed to authenticate Rownd token: ' . $e->getMessage() . '\n' . $e->getTraceAsString());
			return $user_id;
		}
	}

	function trigger_rownd_signin($order = null) {
		$userIdentifierAttr = "";

		if ($order != null) {
			$email = $order->get_billing_email();
			$userIdentifierAttr = "data-rownd-default-user-identifier=\"{$email}\"";
		}

		echo "<div data-rownd-require-sign-in=\"auto-submit\" {$userIdentifierAttr}></div>";
	}

	function replace_woocommerce_login_page($template, $template_path) {
		$templateReplacements = [
			'myaccount/form-login.php' => 'templates/woocommerce/login.php',
			'myaccount/form-edit-account.php' => 'templates/woocommerce/form-edit-account.php',
		];

		if (empty($templateReplacements[$template_path])) {
			return $template;
		}

		$new_path = plugin_dir_path( __FILE__ ) . $templateReplacements[$template_path];

		return $new_path;
	}

	function update_user_profile($user_id, $old_user_data, $userdata) {
		try {
			$rowndUserId = get_user_meta($user_id, 'rownd_id', true);

			// If we don't know the Rownd user's ID, then we can't update anything
			if (empty($rowndUserId)) {
				return;
			}

			$filteredUserData = array();
			$filteredUserData['first_name'] = $userdata["first_name"] ?? '';
			$filteredUserData['last_name'] = $userdata["last_name"] ?? '';
			$filteredUserData['email'] = $userdata["user_email"];

			$rowndClient = lib\RowndClient::getInstance();
			$rowndClient->createOrUpdateRowndUser($rowndUserId, $filteredUserData);
		} catch (\Exception $e) {
			rownd_write_log('Error updating Rownd user profile: ' . $e->getMessage());
		}
	}

	function link_wc_orders_at_registration($user_id) {
		wc_update_new_customer_past_orders( $user_id );
	}

	function search_users_by_rownd_id($args, $request) {
		$rownd_id = sanitize_text_field( $request['rownd_id'] );

		if (empty($rownd_id)) {
			return $args;
		}

		$source_meta_query = array(
			'key' => 'rownd_id',
			'value' => $rownd_id
		);

		if ( isset( $args['meta_query'] ) ) {
			$args['meta_query']['relation'] = 'AND';
			$args['meta_query'][] = $source_meta_query;
		} else {
			$args['meta_query'] = array();
			$args['meta_query'][] = $source_meta_query;
		}

		return $args;
	}
}
