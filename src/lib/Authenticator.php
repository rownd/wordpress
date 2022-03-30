<?php

namespace Rownd\WordPress\lib;

defined('ABSPATH') or die("No script kiddies please!");

class Authenticator
{

	function signInUser($decodedRowndToken)
	{
		$wpUserDetails = new \stdClass();

		// Configure user details
		$row = $this->findUser($decodedRowndToken);
		$options = get_option(ROWND_PLUGIN_SETTINGS);

		if (!$row) {
			$row = $this->createUser($wpUserDetails);

			// update_user_meta($row->ID, 'email', $result->email);
			// update_user_meta($row->ID, 'first_name', $result->first_name);
			// update_user_meta($row->ID, 'last_name', $result->last_name);
			// update_user_meta($row->ID, 'deuid', $result->deuid);
			// update_user_meta($row->ID, 'deutype', $result->deutype);
			// update_user_meta($row->ID, 'deuimage', $result->deuimage);
			// update_user_meta($row->ID, 'description', $result->about);
			// update_user_meta($row->ID, 'sex', $result->gender);
			// wp_update_user(array('ID' => $row->ID, 'display_name' => $result->first_name . ' ' . $result->last_name, 'role' => $options['apsl_user_role'], 'user_url' => $result->url));
		}
		$this->loginUser($row->ID);
	}

	function findUser($rownd_id)
	{
		get_users(array(
			'meta_key' => 'rownd_id',
			'meta_value' => $rownd_id
		));
	}

	function createUser() {

	}
}
