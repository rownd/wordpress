<?php

namespace Rownd\WordPress\lib;

defined('ABSPATH') or die("No script kiddies please!");

use GuzzleHttp\Client;
use Jose\Component\Core\JWKSet;
use Jose\Easy\Load;

class RowndClient
{
	private static $instance = null;

	private $httpClient;
	private $jwkSet;
	private $settings;
	private $appConfig;

	private function __construct()
	{
		$this->settings = $rownd_settings = get_option(ROWND_PLUGIN_SETTINGS);

		$this->httpClient = new Client([
			// Base URI is used with relative requests
			'base_uri' => $rownd_settings['api_url'],
			'headers' => [
				'x-rownd-app-key' => $rownd_settings['app_key'],
				'x-rownd-app-secret' => $rownd_settings['app_secret'],
			]
		]);

		$this->getAppConfig();
		$this->getJWKSet();
	}

	public static function getInstance()
	{
		if (self::$instance == null) {
			self::$instance = new RowndClient();
		}

		return self::$instance;
	}

	function getAppConfig() {
		if ( false !== ( $appConfig = get_site_transient( 'rownd_app_config' ) ) ) {
			$this->appConfig = $appConfig;
			return $this->appConfig;
		}

		$appConfigResp = $this->httpClient->get('/hub/app-config');
		$this->appConfig = json_decode($appConfigResp->getBody(), false);

		set_site_transient( 'rownd_app_config', $this->appConfig, HOUR_IN_SECONDS );

		return $this->appConfig;
	}

	function getJWKSet() {
		if ( false !== ( $jwkSet = get_transient( 'rownd_jwkset' ) ) ) {
			$this->jwkSet = $jwkSet;
			return $this->jwkSet;
		}

		$oidcResp = $this->httpClient->get('/hub/auth/.well-known/oauth-authorization-server');
		$oidcConfig = json_decode($oidcResp->getBody(), false);

		$jwksResp = $this->httpClient->get($oidcConfig->jwks_uri);

		$this->jwkSet = JWKSet::createFromJson($jwksResp->getBody());

		set_transient( 'rownd_jwkset', $this->jwkSet, DAY_IN_SECONDS );

		return $this->jwkSet;
	}

	function validateToken($token) {
		// Check if this token has been previously validated and is not expired
		$tokenHash = wp_hash($token);
		$transientName = 'rownd_token_' . $tokenHash;
		$existingJwt = get_transient($transientName);

		if ($existingJwt !== false) {
			return $existingJwt;
		}

		if (!$this->jwkSet) {
			$this->getJWKSet();
		}

		$jwt = Load::jws($token)
			->algs(['EdDSA'])
			->exp()
			->iat()
			->keyset($this->jwkSet)
			->run();

		// Cache the token as its hash until it expires
		set_transient($transientName, $jwt, $jwt->claims->exp() - time());

		return $jwt;
	}

	function getRowndUser($rowndUserId) {
		if ( false !== ( $user = wp_cache_get( $rowndUserId, 'rownd_users' ) ) ) {
			return $user;
		}

		$userUrl = '/applications/' . $this->appConfig->app->id . '/users/' . $rowndUserId;
		$resp = $this->httpClient->get($userUrl . '/data');
		$user = json_decode($resp->getBody());
		$user->url = isset($user->url) ?? $this->settings['api_url'] . $userUrl;

		wp_cache_add($rowndUserId, $user, 'rownd_users', 0.2 * MINUTE_IN_SECONDS);

		return $user;
	}

	function createOrUpdateRowndUser($rowndUserId, $data) {
		$userUrl = '/applications/' . $this->appConfig->app->id . '/users/' . $rowndUserId;
		$resp = $this->httpClient->patch($userUrl . '/data', [
			'json' => [
				'data' => $data
			]
		]);

		$user = json_decode($resp->getBody());

		return $user;
	}
}
