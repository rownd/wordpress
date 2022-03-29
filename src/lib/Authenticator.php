<?php

namespace Rownd\WordPress\lib;

defined('ABSPATH') or die("No script kiddies please!");

class Authenticator
{

	function __construct()
	{
	}

	function findUser($rownd_id)
	{
		get_users(array(
			'meta_key' => 'rownd_id',
			'meta_value' => $rownd_id
		));
	}
}
