<?php
	ini_set("display_errors", "on");
	require_once('lib/setup.inc.php');

function requiredInput($fields, $description = "This test requires some input on your part" ) {
	if (!array_reduce($fields, function($carry, $item) { return $carry && array_key_exists($item, $_POST) && strlen($_POST[$item]) > 0; }, true)) {
		p($description);
		echo "<form method=\"post\" action=\"".$_SERVER['REQUEST_URI']."\">";
		foreach($fields as $f) {
			echo "<p><label>$f:</label> <input type=\"text\" name=\"$f\" /></p>";
		}
		echo "<input type=\"submit\" value=\"Proceed\" />";
		echo "</form>";
		return false;
	}
	foreach($fields as $f)
		$GLOBALS[$f] = $_POST[$f];
	return true;
}

function request($method='GET', $endpoint, $params=[], $headers = []) {
	$ch = curl_init();

	$url =  str_replace(basename(__file__),'', $_SERVER['SCRIPT_URI']) . $endpoint;

	array_walk($headers, function(&$v, $k) { $v = "$k: $v"; });
	$headers = array_values($headers);

	switch($method) {
		case 'GET':
			$url .= '?'.http_build_query($params);
			break;
		case 'POST':
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
			break;
		case 'PUT':
		case 'DELETE':
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
			break;
		default:
			throw new \Exception("Invalid method  Please use: GET, POST, PUT, or DELETE.");
	}

	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	curl_close ($ch);

	return $response;
}

	
// Create a handler function
function my_assert_handler($file, $line, $code) {
	$obj = debug_backtrace();
	$obj = $obj[2];
	$file = $obj['file'];
	$line = $obj['line'];
	$file = str_replace(__dir__.'/', '', $file);
	echo "<div class=\"error\"><h4>Failed.</h4><p><small>$file:$line</small></p></div>";
}
assert_options(ASSERT_CALLBACK, 'my_assert_handler');
assert_options(ASSERT_WARNING, 0);

function affirm($condition) {
	$ret = assert($condition);
	if ($ret)
		echo "<div class=\"success\"><h4>Succeeded.</h4></div>";
	return $ret;
}

	$tests = [];
	
	$list = listTests('tests/');
	
	if (array_key_exists('test', $_REQUEST)) {
		$test = $_REQUEST['test'];
		if (array_key_exists($test, $tests) && file_exists($tests[$test]) && is_file($tests[$test])) {
			ob_start();
			echo "<h1>Running test: $test</h1>\n";
			include($tests[$test]);
			$contents = ob_get_contents();
			ob_end_clean();
		} else {
			$contents = "<h1>Test not found.</h1>\n";
		}
	} else {
		$contents = $list;
	}

	function listTests($dir) {
		global $tests;
		$files = scandir($dir);
		$out = "<ul>\n";
		foreach($files as $f) {
			if ($f == '..' or $f == '.')
				continue;
			if (is_dir($dir . $f)) {
				$out .= "<li>$f\n" . listTests($dir . $f . "/") . "</li>\n";
			} else {
				$chunks = explode('.', $f);
				$ext = array_pop($chunks);
				$name = implode('.', $chunks);
				if ($ext != 'php')
					continue;
				$test = str_replace(['tests/', '/'], ['', '.'], $dir) . $name;
				$out .= "<li><a href=\"test.php?test=$test\">$name</a></li>\n";
				$tests[$test] = $dir . $f;
			}
		}
		$out .= "</ul>\n";
		return $out;
	}
	
	
	function p($str) {
		echo "<p>$str</p>\n";
	}
	
	function dump($var) {
		echo "<pre>";
		var_dump($var);
		echo "</pre>\n";
	}
	
	function json($obj) {
		echo "<pre>".json_encode($obj, JSON_PRETTY_PRINT)."</pre>\n";
	}
?>

<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="x-ua-compatible" content="ie=edge">
		<title>Unit tester</title>
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link href='https://fonts.googleapis.com/css?family=Roboto:400,300,500,700' rel='stylesheet' type='text/css'>
		<style>
			html {
				height: 100%;
				margin: 0px;
				padding: 0px;
				padding-bottom: 40px;
				margin-bottom: 40px;
			}
			body {
				margin: 40px;
				font-family: 'Roboto', sans-serif;
				background-color: #E5EFF5;
				transition: background 1s;
				font-size: 10pt;
				background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAI0lEQVQIW2NkwATGjGhixkD+WWRBsABIEUwQLgATRBEACQIAr8MEOZtA/yIAAAAASUVORK5CYII=);
				height: calc(100% - 80px);
			}
			
			pre {
				background-color: #333;
				padding: 3px;
				border: solid 1px #111;
				color: #ddd;
				margin-left: 10px;
				border-left: solid 5px #333;
			}
			
			.error, .success {
				background-color: #aa0000;
				padding: 3px;
				border: solid 1px #990000;
				color: #fff;
				margin-left: 10px;
				border-left: solid 5px #990000;
			}
			
			.error h4, .error p, .success h4 {
				margin: 0px;
				padding: 0;
			}
			
			.success {
				background-color: #00aa00;
				border-color: #009900;
			}
			
			input[type=text] {
				border: solid 1px #333;
			}
			
			input {
				padding: 5px;
				padding-left: 10px;
				padding-right: 10px;
			}
			
			label {
				min-width: 5rem;
				display: inline-block;
			}
			
			
		</style>
	</head>
	<body>
	<article><?php echo $contents; ?></article>
	</body>
</html>