function rowndSetConfigVar(name, value) {
	if (value) {
		_rphConfig.push([name, value]);
	}
}

(function () {
	var _rphConfig = (window._rphConfig =
		window._rphConfig || []);
	let baseUrl = window.localStorage.getItem('rph_base_url_override') || rownd_config_object?.hub_base_url || 'https://hub.rownd.io';
	_rphConfig.push(['setBaseUrl', baseUrl]);
	var d = document,
		g = d.createElement('script'),
		s = d.getElementsByTagName('script')[0];
	g.type = 'text/javascript';
	g.async = true;
	g.src = baseUrl + '/static/scripts/rph.js';
	if (s && s.parentNode) {
		s.parentNode.insertBefore(g, s);
	} else {
		d.body.appendChild(g);
	}
})();

if (rownd_config_object?.start_wp_session === 'on') {
	_rphConfig.push(['setPostAuthenticationApi', {
		method: 'post',
		url: '/wp-json/rownd/v1/auth',
		extra_headers: {
			'x-wp-nonce': rownd_config_object.nonce
		}
	}]);
}

rowndSetConfigVar('setApiUrl', rownd_config_object?.api_url);
rowndSetConfigVar('setAppKey', rownd_config_object?.app_key);
rowndSetConfigVar('setRootOrigin', rownd_config_object?.root_origin);
