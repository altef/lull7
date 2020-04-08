<?php
	
$dbinfo = $config['system']['database'];
$db = new \altef\lull7\PDO('mysql:dbname='.$dbinfo['database'].';host='.$dbinfo['host'].';charset=utf8', $dbinfo['username'], $dbinfo['password']);


$table = 'test-pdo';

// Try to create the table
p("<strong>Attempting to create table <code>$table</code>...</strong>");
$statement = $db->effect(
			'CREATE TABLE IF NOT EXISTS `'.$table.'` (
				`key` varchar(100) NOT NULL,
				`value` TEXT,
				`expires_at` TIMESTAMP NOT NULL,
				PRIMARY KEY (`key`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;'
);

p("Checking PDO's success value");
affirm($db->success($statement));

p("Verifying $table exists");
$tables = $db->assoc('Show tables', null, \PDO::FETCH_COLUMN );
affirm(in_array($table, $tables));

p("Sanity checking the error code: <code>" . $statement->errorCode()."</code>"); 
affirm($db->success($statement));

p("<strong>Removing table...</strong>");
$statement = $db->effect('DROP TABLE `'.$table.'`');

p("Checking PDO's success value");
affirm($db->success($statement));

p("Verifying <code>$table</code> does not exist");
$tables = $db->assoc('Show tables', null, \PDO::FETCH_COLUMN );
affirm(!in_array($table, $tables));




?>