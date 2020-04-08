<?php

use \altef\output\Debug;

list($scriptPath) = get_included_files();

// This is the default config.  We can override any of these properties in config.[environment].php
$config = [

	// There should be a matching config.[key].php file for each of these, whose contents can override any of the values here
	// Those values will be used if the domain matches the [value] associated with that key
	'environments' => [
		'production' => 'domain.com', 
		'development' => 'dev.domain.com',
	],


	'headers' => [
		["Access-Control-Allow-Origin: *"],
		['Content-type: application/json'],
		['Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS'],
		['Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Cookie, Authorization, X-Experience-API-Version'],
		["Cache-Control: no-store, no-cache, must-revalidate, max-age=0"],
		["Cache-Control: post-check=0, pre-check=0", false],
		["Pragma: no-cache"]
	],


	// System info - used for authentication, etc.
	'system' => [

		'messages' => [
			'unexpected_error' => 'An unexpected error has occurred.',
			'unexpected_exception' => 'An unexpected exception has occurred.'
		],

		'database' => [
			'host' => '',
			'username' => '',
			'password' => '',
			'database' => '',

			// These tables will be present in the system database
			'tables' => [
				'users' => 'sys_users',
				'persistent_global_map' => 'sys_value_map'
			],
		],

		// Permissions that are set when creating a user
		'default_permissions' => [],
	],

	'client_url' => '',

	'email' => [
		'from' => '',
		'subjects' => [
			'welcome' => 'Hey new user!',
			'forgot' => 'Password reset'
		]
	],

	'verbosity' => [
		'default' => Debug::Silent,
		'debug' => Debug::Debug
	],

	'email_template_directory' => dirname($scriptPath) . DIRECTORY_SEPARATOR . 'email_templates' . DIRECTORY_SEPARATOR,
];


// You can specify extra stuff as well, including more databases - in case you want to keep your data separate from your api system.
// ...



