<?php

$map = new \altef\keyvaluestore\Local();

$key = 'unit_test';
$value = time();

// Retrieve one that doesn't exist:
p("Retrieving $key, which shouldn't exist.");
try {
	$result = $map->get($key);
	affirm(false);
} catch (Exception $e) {
	affirm(true);
}

// Store one
p("Storing $value in $key");
$result = $map->store($key, $value, 1); // One second
dump($map->get($key));
affirm($result === true);

// Retrieve it
$result = $map->get($key);
p("Retrieving $key which should have a value of $value");
dump($map->get($key));
affirm($result === $value);

// Recursively load a map
$data = [
	'one' => [
		'two' => 'three'
	]
];

$map = \altef\keyvaluestore\Local::recursive($data);
$result = $map->get('one')->get('two');
p("Retrieving \$map->one->two, which should be three.");
dump($result);
affirm($result === 'three');
dump($map);

?>