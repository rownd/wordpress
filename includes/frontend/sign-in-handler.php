<?php

defined( 'ABSPATH' ) or die( "No script kiddies please!" );

if( !class_exists( 'APSL_Lite_Login_Check_Class' ) ) {

    class Rownd_Signin_Handler_Class {

		function __construct() {

		}

		function findUser( $rownd_id ) {
			get_users(array(
				'meta_key' => 'rownd_id',
				'meta_value' => $rownd_id
			));
		}

	}

}

$rownd_signin_handler = new Rownd_Signin_Handler_Class();
