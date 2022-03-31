<?php

namespace Rownd\WordPress\lib;

defined('ABSPATH') or die("No script kiddies please!");

class Authenticator
{
	function signInUser($decodedRowndToken)
	{
		$rowndUserId = $decodedRowndToken->claims->get('https://auth.rownd.io/app_user_id');

		$rowndClient = RowndClient::getInstance();
		$rowndUser = $rowndClient->getRowndUser($rowndUserId);

		// Configure user details
		$row = $this->findUser($rowndUser);
		// $options = get_option(ROWND_PLUGIN_SETTINGS);

		if (!$row) {
			$row = $this->createUser($rowndUser);
		}

		$userId = $row->ID;

		// Sign-in the user
		if (!isset($secure_cookie) && is_ssl() && force_ssl_login() && !force_ssl_admin()) {
			$secure_cookie = false;
		}
		if (isset($_POST['testcookie']) && empty($_COOKIE[TEST_COOKIE])) {
			throw new WP_Error('test_cookie', __("<strong>ERROR</strong>: Cookies are blocked or not supported by your browser. You must <a href='http://www.google.com/cookies.html'>enable cookies</a> to use WordPress."));
		}

		$user = wp_signon('', isset($secure_cookie));

		if (!$this->set_cookies($userId)) {
			return false;
		}
	}

	// Find user by Rownd ID first, otherwise try email
	function findUser($rowndUser)
	{
		$users = get_users(array(
			'meta_key' => 'rownd_id',
			'meta_value' => $rowndUser->data->user_id
		));

		if (count($users) > 0) {
			return $users[0];
		}

		return get_user_by('email', $rowndUser->data->email);
	}

	function createUser($rowndUser)
	{
		$rowndUserData = $rowndUser->data;

		$random_password = wp_generate_password(12, false);
		$userId = wp_create_user($rowndUser->data->email, $random_password, $rowndUserData->email);

		update_user_meta($userId, 'email', $rowndUserData->email);
		update_user_meta($userId, 'first_name', $rowndUserData->first_name);
		update_user_meta($userId, 'last_name', $rowndUserData->last_name);
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
