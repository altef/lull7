<?php

$dbinfo = $config['system']['database'];
$db = new \altef\lull7\PDO('mysql:dbname='.$dbinfo['database'].';host='.$dbinfo['host'].';charset=utf8', $dbinfo['username'], $dbinfo['password']);

$session = new \altef\keyvaluestore\LocalSoft();
$cache = new \altef\keyvaluestore\Local();
$users = new \altef\lull7\Users($db, $dbinfo['tables']['users'], $cache);


$auth = new \altef\lull7\Auth($users, $session);

p("Creating a user");
$username1 = '__test-auth1';
$password1 = $users->createHash(22);

$id1 = $users->createUser($username1, $password1);
affirm($id1 > 0);


p("Attempting to login");
affirm($auth->login($username1, $password1));

p("Checking user ID");
affirm($auth->userId() == $id1);

p("Checking data");
$d = $auth->data();
json($d);
affirm($d['email'] == $username1);

p("Checking logout");
$auth->logout();
affirm(!$auth->isLoggedIn());
$d = $auth->data();
affirm(count(array_keys($d)) < 2);


p("Attempting to login by ID");
affirm($auth->loginById($id1));
p("Verifying email matches");
$d = $auth->data();
json($d);
affirm($d['email'] == $username1);




p("Deleting user");
$users->del($id1);
$user = $users->byId($id1);
json($user);
affirm($user == null);