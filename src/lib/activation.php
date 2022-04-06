<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
$rownd_settings = array();

$rownd_settings['app_key'] = '';
$rownd_settings['app_secret'] = '';

$rownd_settings['api_url'] = 'https://api.rownd.io';

update_option( ROWND_PLUGIN_SETTINGS, $rownd_settings );
