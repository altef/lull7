<?php

// Autoload classes
spl_autoload_register( function($class)  {
	$dir = 'lib/';
	if (strpos($class, '\\') !== false) { // It's a namespace
		$path = $dir.str_replace('\\', '/', $class) . '.php';
		require_once($path);
		return;
	}
	if ( file_exists( $dir.$class.'.class.php' ) ) {
		include_once( $dir.$class.'.class.php' );
		return;
	}
});


$environment = null;

// Load the config
require_once('config.default.php');
if (array_key_exists('environments', $config) and is_array($config['environments'])) {
	foreach($config['environments'] as $env => $domain) {
		if (@$_SERVER['SERVER_NAME'] == $domain) {
			require_once('config.'.$env.'.php');
			$environment = $env;
			break;	// Only load one
		}
	}
}
?>