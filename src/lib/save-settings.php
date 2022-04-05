<?php
defined( 'ABSPATH' ) or die( "No script kiddies please!" );

$rownd_settings = array();

foreach( $_POST['rownd_settings'] as $key => $value ) {
	$rownd_settings[$key] = sanitize_text_field($value);
}

update_option( ROWND_PLUGIN_SETTINGS, $rownd_settings );
$_SESSION['rownd_message'] = __( 'Settings Saved Successfully.', 'rownd' );
wp_redirect( admin_url() . 'admin.php?page=' . 'rownd' );
exit;
