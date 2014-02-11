/**
 * This file is part of the DreamFactory DFNA Application
 * Copyright 2013 DreamFactory Software, Inc. {@email support@dreamfactory.com}
 *
 * DreamFactory DFNA Application {@link http://github.com/dreamfactorysoftware/dfna-example}
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

//********************************************************************************
//* The file contains common client-side functions for the app
//********************************************************************************

/**
 * Our global options
 * @var {*}
 */
var _options = {
	/** @var int **/
	alertHideDelay:      5000,
	/** @var int **/
	notifyDiv:           'div#request-message',
	/** @var int **/
	ajaxMessageFadeTime: 6000,
	/** @var {*} **/
	scrollPane:          null,
	/** @var string **/
	defaultCodeTemplate: '<small>Ready</small><pre id="pretty-code" style="display: none;"><code class="language-javascript"></code></pre>',
	/** @var string **/
	defaultUri:          '/users',
	/** @var {*} **/
	currentProvider:     {},
	/** @var bool */
	readOnly:            true,
	/** @var {*} jQuery cache */
	$:                   {request: {}, status: {}, prettyCode: null},

	//    These are set in index.php (ugh)
	/**
	 * @var string
	 */
	APPLICATION_NAME:    null,
	/** @var {*}[] **/
	providers:           {},
	/** @var string **/
	baseUrl:             null
};

/**
 * Check if a var is defined and return default value if not optionally
 *
 * @param variable
 * @param [defaultValue]
 * @returns {*}
 * @private
 */
var _isDefined = function(variable, defaultValue) {
	if (typeof variable != 'undefined') {
		return variable;
	}

	if (typeof defaultValue != 'undefined') {
		return defaultValue;
	}

	//	Nope, not defined
	return false;
};

/**
 * Reset the form all proper-like
 * @private
 */
var _reset = function() {
	_options.$.request.server.html(_options.baseUrl);
	_options.$.request.uri.val(_options.defaultUri);
	_options.$.request.method.val('GET');
	_options.$.request.app.val(_options.APPLICATION_NAME);
	_options.$.results.html(_options.defaultCodeTemplate);

	_loading(false);
};

/**
 * Gets the div to write code in
 * @returns {null}
 * @private
 */
var _prettyCode = function() {
	if (!_options.$.prettyCode || !_options.$.prettyCode.length) {
		_options.$.prettyCode = $('pre#pretty-code');
	}

	return _options.$.prettyCode;
};

/**
 * Turn on/off the indicators
 * @param which
 * @private
 */
var _loading = function(which) {
	if (!which) {
		//	Off
		_options.$.loading.fadeOut().removeClass('fa-spin');
		_options.$.page.css({cursor: 'default'});
		$('#send-request').removeClass('disabled');
		_options._stopTime = Date.now();
		_options.$.results.find('small').html('Ready');

		if (_options._startTime && _options._stopTime) {
			_options._elapsed = _options._stopTime - _options._startTime;
			_options.$.request.elapsed.html('<small>(' + _options._elapsed + 'ms)</small>').show();
			_options._startTime = _options._stopTime = 0;
		}
	}
	else {
		_options.$.loading.fadeIn().addClass('fa-spin');
		_options.$.page.css({cursor: 'wait'});
		$('#send-request').addClass('disabled');
		_options.$.results.find('small').text('Loading...');

		_options._startTime = Date.now();
		_options.$.request.elapsed.empty().hide();
	}
};

/**
 * Shows the results pretty-printed
 * @param data
 * @param [pretty]
 * @returns {boolean}
 * @private
 */
var _showResults = function(data, pretty) {
	if (false !== pretty) {
		data = _convertResults(data);
	}

	$('code', _prettyCode()).html(data);
	_prettyCode().css({display: 'block'}).show();

	//noinspection JSUnresolvedVariable
	Prism.highlightAll(_options.$.prettyCode.find('code')[0]);

	window.location.hash = 'call-results-body';
	return true;
};

/**
 *
 * @param source
 * @private
 * @returns {String}
 */
var _convertResults = function(source) {
	var _string = JSON.stringify(source, null, 4);
	return _string || source;
};

/**
 * Gets the URL to return to after a redirected AJAX call...
 * @returns {string}
 * @private
 */
var _getReferrer = function(encoded) {
	var _run = 'run=' + (_options.$.request.app.val() || _options.APPLICATION_NAME);
	var _referrer = window.parent.location.href;
	if (-1 == _referrer.indexOf(_run)) {
		_referrer += ( -1 == _referrer.indexOf('?') ? '?' : '&') + _run;
	}
	return !_isDefined(encoded, false) ? _referrer : encodeURI(_referrer);
};

/**
 * Runs the API call
 * @private
 */
var _execute = function() {
	var _method = _options.$.request.method.val();
	var _uri = _options.$.request.uri.val();
	var _app = _options.$.request.app.val() || _options.APPLICATION_NAME;
	var _raw = _options.$.request.body.val();
	var _token = _options.$.request.token.val();
	var _server = _options.$.request.server.html();
	var $_code = _prettyCode().find('code');

	if (!_uri || !_uri.length) {
		alert('Invalid Request URI specified.');
		return false;
	}

//	_uri += ( -1 == _uri.indexOf('?') ? '?' : '&') + 'format=json';

	_options.$.results.find('small').html('Loading...');
	_prettyCode().hide().find('code').empty();

	try {
		var _body = null;

		if (_raw.length) {
			_body = JSON.stringify(JSON.parse(_raw));
		}

		$.ajax({
			url: _server + _uri,
			type:        _method,
			dataType:    'json',
			cache:       false,
			processData: false,
			data:        _body,
			beforeSend:  function(xhr) {
				_loading(true);

				if (_app) {
					xhr.setRequestHeader('X-DreamFactory-Application-Name', _app);
					xhr.setRequestHeader('X-DreamFactory-Session-Token', _token);
				}
			},
			success:     function(data) {
				return _showResults(data);
			},
			error:       function(err) {
				if (err && err.responseJSON) {
					return _showResults(err.responseJSON);
				}

				var _json = {};

				try {
					if (err.responseText) {
						_json = JSON.parse(err.responseText);
						if (!_json) {
							_json = err.responseText;
						}
					}
				}
				catch (_ex) {
					//	Ignore
				}

				_showResults('Error: ' + err.status + '<br />' + err.responseText, false);
			},
			complete:    function() {
				_loading(false);
			}
		});
	}
	catch (_ex) {
		$_code.html(' >> ' + _ex);
	}

	return false;
};

/**
 * Initialize the app
 * @private
 */
var _initialize = function() {
	if (!_options.actions) {
		_options.actions = window.parent.Actions;
		_options.config = window.parent.Config;
	}

	//	Cache some selectors
	_options.$.page = $('html');
	_options.$.loading = $('#loading-indicator');
	_options.$.results = $('#example-code');

	_options.$.request.server = $('span#request-server.input-group-addon.muted');
	_options.$.request.app = $('#request-app');
	_options.$.request.method = $('#request-method');
	_options.$.request.uri = $('#request-uri');
	_options.$.request.body = $('#request-body');
	_options.$.request.token = $('#request-token');
	_options.$.request.elapsed = $('#request-elapsed');

	_options.$.status.revoke = $('#revoke-auth-status');
	_options.$.status.provider = $('#dfna-auth-status');
	_options.$.status.check = $('#dfna-auth-check');

	_reset();
};

/**
 * Initialize any buttons and set fieldset menu classes
 */
jQuery(function($) {
	//	Initialize...
	_initialize();

	//	Close the app
	$('#app-close').on('click', function(e) {
		e.preventDefault();
		if (window.parent && window.parent.Actions) {
			window.parent.Actions.showAdmin();
		}
	});

	$('#send-request').on('click', function(e) {
		e.preventDefault();
		_execute();
	});

	$('#reset-request').on('click', function(e) {
		e.preventDefault();
		_reset();
	});

});
