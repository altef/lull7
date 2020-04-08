<?php
	
$dbinfo = $config['system']['database'];
$db = new \PDO('mysql:dbname='.$dbinfo['database'].';host='.$dbinfo['host'].';charset=utf8', $dbinfo['username'], $dbinfo['password']);

$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$db->setAttribute(PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$db->setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC );


$map = new \altef\keyvaluestore\Database($db, $config['system']['database']['tables']['persistent_global_map']);

$key = 'unit_test';
$value = time();

// Retrieve one that doesn't exist:
$result = $map->get($key);
p("Retrieving $key, which shouldn't exist.");
dump($map->get($key));
affirm($result === null);

// Store one
$result = $map->store($key, $value, 1); // One second
p("Storing $value in $key");
dump($map->get($key));
affirm($result === true);

// Retrieve it
$result = $map->get($key);
p("Retrieving $key which should have a value of $value");
dump($map->get($key));
affirm($result === $value);

// Retrieve it after its expired
sleep(1);
$result = $map->get($key);
p("Retrieving $key, after it should have expired.");
dump($map->get($key));
affirm($result === null);

?>