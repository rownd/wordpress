<?php

namespace Rownd\WordPress\lib;

defined('ABSPATH') or die("No script kiddies please!");

class Authenticator
{
	var $rownd_settings;

	function __construct()
	{
		$this->rownd_settings = get_option(ROWND_PLUGIN_SETTINGS);
	}

	function signInUser($decodedRowndToken)
	{
		$userId = $this->authenticateUser($decodedRowndToken);

		// Sign-in the user
		if (!isset($secure_cookie) && is_ssl() && force_ssl_login() && !force_ssl_admin()) {
			$secure_cookie = false;
		}
		if (isset($_POST['testcookie']) && empty($_COOKIE[TEST_COOKIE])) {
			throw new \WP_Error('test_cookie', __("<strong>ERROR</strong>: Cookies are blocked or not supported by your browser. You must <a href='http://www.google.com/cookies.html'>enable cookies</a> to use WordPress."));
		}

		$user = wp_signon('', isset($secure_cookie));

		if (!$this->set_cookies($userId)) {
			return false;
		}
	}

	function authenticateUser($decodedRowndToken) {
		$rowndUserId = $decodedRowndToken->claims->get('https://auth.rownd.io/app_user_id');

		$row = $this->findOrCreateUser($rowndUserId);

		if (isset($row->errors) && count($row->errors) > 0) {
			rownd_write_log($row->errors);
			throw new \WP_Error('authentication_error', 'There was an error authenticating your account.');
		}

		$userId = $row->ID;

		return $userId;
	}

	function findOrCreateUser($rowndUserId) {
		// Try to find the user by their ID
		$row = $this->findUserByRowndId($rowndUserId);

		if (!$row) {
			// Find user by their email
			$rowndClient = RowndClient::getInstance();

			$start = microtime(true);
			$rowndUser = $rowndClient->getRowndUser($rowndUserId);
			$end = microtime(true);
			$time = number_format(($end - $start), 2);

			error_log('Rownd API call to get user took ' . $time . ' seconds');

			$row = $this->findUserByEmail($rowndUser);
		}

		// If we still didn't find the user, create one
		if (!$row) {
			$row = $this->createUser($rowndUser);
		}

		return $row;
	}

	// Find user by Rownd ID first, otherwise try email
	function findUserByRowndId($rowndUserId)
	{
		$users = get_users(array(
			'meta_key' => 'rownd_id',
			'meta_value' => $rowndUserId,
		));

		if (count($users) > 0) {
			return $users[0];
		}

		return false;
	}

	function findUserByEmail($rowndUser) {
		return get_user_by('email', $rowndUser->data->email);
	}

	function createUser($rowndUser)
	{
		$rowndUserData = $rowndUser->data;

		$random_password = wp_generate_password(12, false);
		$userId = null;
		if (rownd_is_plugin_active('woocommerce/woocommerce.php') && $this->rownd_settings['is_woocommerce_integration_enabled'] ?? 'no' == 'yes') {
			$userId = wc_create_new_customer($rowndUserData->email, $rowndUserData->email, $random_password);
		} else {
			$userId = wp_create_user($rowndUserData->email, $random_password, $rowndUserData->email);
		}

		update_user_meta($userId, 'email', $rowndUserData->email);
		update_user_meta($userId, 'first_name', $rowndUserData->first_name ?? '');
		update_user_meta($userId, 'last_name', $rowndUserData->last_name ?? '');
		update_user_meta($userId, 'rownd_id', $rowndUserData->user_id);
		wp_update_user(
			array(
				'ID' => $userId,
				'display_name' => $rowndUserData->first_name . ' ' . $rowndUserData->last_name,
				'user_url' => $rowndUser->url
			)
		);

		do_action('rownd_create_user', $userId); // hookable function to perform additional actions on user creation
		return $userId;
	}

	function set_cookies($user_id = 0, $remember = true)
	{
		if (!function_exists('wp_set_auth_cookie')) {
			return false;
		}

		if (!$user_id) {
			return false;
		}

		wp_clear_auth_cookie();
		wp_set_auth_cookie($user_id, $remember);
		wp_set_current_user($user_id);
		return true;
	}
}
