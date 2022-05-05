<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
$rownd_settings = array();

$rownd_settings['app_key'] = '';
$rownd_settings['app_secret'] = '';

$rownd_settings['api_url'] = 'https://api.rownd.io';
$rownd_settings['hub_base_url'] = 'https://hub.rownd.io';

$rownd_settings['is_woocommerce_integration_enabled'] = '0';
$rownd_settings['woocommerce_checkout_signin_prompt_location'] = 'before_checkout';

update_option( ROWND_PLUGIN_SETTINGS, $rownd_settings );
