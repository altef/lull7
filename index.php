<?php
/**
 * This is the point of entry.
 */
// Show any errors when initializing the system
error_reporting( E_ALL | E_NOTICE );
ini_set('display_errors', '1');

$start_time = microtime(true);

// Sometimes I want to access this via command-line
if (isset($argv)) {
	$defaults = [
		'user' => 0, // Not logged in
		'method' => 'get',
		'environment' => 'none', // Default
		'path' => ''
	];

	// CLI Options
	$options = getopt("", ["user:", "method:", "environment:", "endpoint:", "path:"]);
	foreach($options as $k=>$v)
		$defaults[$k] = $v;

	if ($defaults['path'] == '') {
		die("Usage: index.php --user=USER_ID --method=REQUEST_METHOD --environment=production --path=REQUEST_URI\nExample:php index.php --method=GET --user=1 --path=test?name=hi\n\n");
	}

	// Spoof the request method and URL so everything works as usual
	$_SERVER = array(
		'SERVER_NAME' => $config['environments'][$defaults['environment']] ?? 'command-line',
		'REQUEST_METHOD' => $defaults['method'],
		'REQUEST_URI' => $defaults['path'],
		'SCRIPT_NAME' => '',
	);

	// Doesn't support payloads yet because I haven't needed to
	$q = explode("?", $_SERVER['REQUEST_URI']);
	if (count($q) > 1) {
		parse_str($q[1], $_GET);
		$_REQUEST = $_POST = $_GET;
	}
}




require_once("lib/setup.inc.php");
$api = Api::getInstance();


// Sometimes I want to access this via command-line
if (isset($argv)) {
	// Now that the API is setup, we can set the user by the passed ID
	//$api->setDebug(true); // So it doesn't show us every backtrace
	if ($defaults['user'] > 0) 
		$api->auth->loginById($defaults['user']);
} else {
	// Pass through the headers from the config
	foreach($api->config->get('headers')->get() as $h)
		call_user_func_array('header', $h->get());
}


/**
 * Broadly handle errors.
 */
if ($api->isDebug() || $environment == 'development') { // Let's see all the errors we can.
	$api->debug->v("Setting error reporting to all.");
	error_reporting( E_ALL | E_NOTICE );
	ini_set('display_errors', '1');

} else {	// HIDE ALL ERRORS OR ELSE
	$api->debug->v("Setting error reporting to none.");
	error_reporting( E_ERROR );
	ini_set('display_errors', 0);
	
	set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext) use ($api) {
		error_log( "Error $errno: $errstr in $errfile:$errline" );
		//error_log(print_r(debug_backtrace(), TRUE));
		$api->error( 501, $api->config->get('system')->get('messages')->get('unexpected_error'));
	});
	
	set_exception_handler(function($exception) use ($api) {
		error_log( "Exception: $exception" );
		//error_log(print_r(debug_backtrace(), TRUE));
		$api->error( 501, $api->config->get('system')->get('messages')->get('unexpected_error'));
	});	
}


/**
 * Pass off to the endpoint
*/

$request =  str_replace( str_replace(basename(__file__),'', $_SERVER['SCRIPT_NAME']), '', $_SERVER['REQUEST_URI'] );
@list($path) = explode('?', strtolower($request));
$path = explode('/', trim($path, '/'));

$endpoint = $path[0];
$api->debug->v("Endpoint: $endpoint");
$http_verb = strtolower($_SERVER['REQUEST_METHOD']);
$most_specific = null;

if ($endpoint != '') {
	// Figure out which file to load, and function to call
	$possible = array();
	$current = 'endpoints';
	foreach($path as $p) {
		// If we hit any that aren't valid endpoint names, quit because no more endpoints can be valid than are already listed
		if (preg_match('/[^0-9a-z_-]/i', $p))
			break;
		
		// If it's numeric, assume it's not part of the path (it's probably an ID)
		if (!is_numeric($p))
			$current = $current . DIRECTORY_SEPARATOR . $p;
		$possible[] = $current .'.php';
		$possible[] = $current . DIRECTORY_SEPARATOR . 'index.php';
	}

	// Include any on the way
	foreach($possible as $p) {
		if (file_exists($p)) {
			$api->debug->v("Including $p");
			include_once($p);
			$most_specific = $p;
		}
	}

	if ($most_specific !== null) {
		$classes = get_declared_classes(); // Might need to change this later if it doesn't work.  Oh well, there are a bunch of ways to do it.
		$className = end($classes);
		$e = new $className($api);
		if (method_exists($e, $http_verb)) {
			Api::json_out($e->$http_verb($path, \altef\lull7\Endpoint::prepareData($http_verb)));
		} else {
			Api::error(405, "No matching method.");
		}
	}
}

if ($most_specific === null)
	Api::error(404, $_SERVER['REQUEST_URI'] . ' is not a valid endpoint.');


// ?>