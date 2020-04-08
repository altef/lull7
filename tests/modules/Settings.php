<?php


$dbinfo = $config['system']['database'];
$db = new \altef\lull7\PDO('mysql:dbname='.$dbinfo['database'].';host='.$dbinfo['host'].';charset=utf8', $dbinfo['username'], $dbinfo['password']);

$table = 'settings';
$settings = new \altef\lull7\Settings($db, $table);

p("Creating table <code>".$table."</code> if it does not already exist.");
$settings->_createTable();

$name = 'test-setting';
$global_value = ['scope'=>'global'];
$user_value = ['scope'=>'user'];
$user_id = 3;

p("Adding a company setting");
$settings->store($name, $global_value);
$v = $settings->get($name);
json($v);
affirm($v['scope'] == $global_value['scope']);


p("Adding a user setting");
$settings->store($name, $user_value, $user_id);
$v = $settings->get($name, $user_id);
json($v);
affirm($v['scope'] == $user_value['scope']);

p("Retrieving all settings for the user");
$v = $settings->getAll($user_id);
json($v);
affirm($v[$name]['scope'] == $user_value['scope']);

p("Retrieving all global settings");
$v = $settings->getAll();
json($v);
affirm($v[$name]['scope'] == $global_value['scope']);



p("Verifying another users get the global version");
$v = $settings->get($name, 1);
json($v);
affirm($v['scope'] == $global_value['scope']);

p("Removing user setting");
$settings->del($name, $user_id);
$v = $settings->get($name, $user_id);
affirm($v['scope'] == $global_value['scope']);



p("Removing global setting");
$settings->del($name);
$v = $settings->get($name);
affirm($v === null);
