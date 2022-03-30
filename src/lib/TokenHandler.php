<?php

namespace Rownd\WordPress\lib;

defined('ABSPATH') or die("No script kiddies please!");

use GuzzleHttp\Client;
use Jose\Component\Core\JWKSet;
use Jose\Easy\Load;

class TokenHandler
{

	private $httpClient;
	private $jwkSet;

	function __construct()
	{
		$rownd_settings = get_option(ROWND_PLUGIN_SETTINGS);

		$this->httpClient = new Client([
			// Base URI is used with relative requests
			'base_uri' => $rownd_settings['api_url'],
		]);

		$this->getJWKSet();
	}

	function findUser($rownd_id)
	{
		get_users(array(
			'meta_key' => 'rownd_id',
			'meta_value' => $rownd_id
		));
	}

	function getJWKSet() {
		$oidcResp = $this->httpClient->get('/hub/auth/.well-known/oauth-authorization-server');
		$oidcConfig = json_decode($oidcResp->getBody(), false);

		$jwksResp = $this->httpClient->get($oidcConfig->jwks_uri);
		// $jwkSet = json_decode($jwksResp->getBody());

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
}
