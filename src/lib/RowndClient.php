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
		$appConfigResp = $this->httpClient->get('/hub/app-config');
		$this->appConfig = json_decode($appConfigResp->getBody(), false);
		return $this->appConfig;
	}

	function getJWKSet() {
		$oidcResp = $this->httpClient->get('/hub/auth/.well-known/oauth-authorization-server');
		$oidcConfig = json_decode($oidcResp->getBody(), false);

		$jwksResp = $this->httpClient->get($oidcConfig->jwks_uri);

		$this->jwkSet = JWKSet::createFromJson($jwksResp->getBody());

		return $this->jwkSet;
	}

	function validateToken($token) {
		if (!$this->jwkSet) {
			$this->getJWKSet();
		}

		$jwt = Load::jws($token)
			->algs(['EdDSA'])
			->exp()
			->iat()
			->keyset($this->jwkSet)
			->run();

		return $jwt;
	}

	function getRowndUser($rowndUserId) {
		$userUrl = '/applications/' . $this->appConfig->app->id . '/users/' . $rowndUserId;
		$resp = $this->httpClient->get($userUrl . '/data');
		$user = json_decode($resp->getBody());
		$user->url = $user->url ?: $this->settings['api_url'] . $userUrl;
		return $user;
	}
}
