<?php
	
$dbinfo = $config['system']['database'];
$db = new \altef\lull7\PDO('mysql:dbname='.$dbinfo['database'].';host='.$dbinfo['host'].';charset=utf8', $dbinfo['username'], $dbinfo['password']);

$table = 'test-crud';

// Try to create the table
p("<strong>Attempting to create table <code>$table</code>...</strong>");
$statement = $db->effect(
			'CREATE TABLE IF NOT EXISTS `'.$table.'` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`value` varchar(100) NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;'
);

$tables = $db->assoc('Show tables', null, \PDO::FETCH_COLUMN );
affirm(in_array($table, $tables));


p("Instantiating SimpleCRUD");
$crud = new \altef\lull7\SimpleCRUD($db, $table);
affirm($crud instanceof \altef\lull7\SimpleCRUD);

// Create
p("Testing <code>create</code>");
$id = $crud->create(['value'=>'test1']);
affirm($id == 1);

// Read
p("Testing <code>read</code>");
$entry = $crud->read(['id'=>$id]);
json($entry);
affirm($entry[0]['value'] == 'test1');

// Update
p("Testing <code>update</code>");
$crud->update(['id'=>$id, 'value'=>'test1-updated']);
$entry = $crud->read(['id'=>$id]);
json($entry);
affirm($entry[0]['value'] == 'test1-updated');

// SoftCreate
// Create one
p("Testing <code>softCreate</code>");
$id = $crud->softCreate(['value'=>'test2']);
$entry = $crud->read(['id'=>$id]);
json($entry);
affirm($entry[0]['value'] == 'test2');

// Update it
$id2 = $crud->softCreate(['id'=>$id, 'value'=>'test2-updated']);
affirm($id2 == $id);
$entry = $crud->read(['id'=>$id]);
json($entry);
affirm($entry[0]['value'] == 'test2-updated');

// Del
p("Testing <code>delete</code>");
$crud->del(['id'=>$id]);
$entry = $crud->read(['id'=>$id]);
affirm(count($entry) == 0);

p("Removing table...");
$statement = $db->effect('DROP TABLE `'.$table.'`');
$tables = $db->assoc('Show tables', null, \PDO::FETCH_COLUMN );
affirm(!in_array($table, $tables));




?>