<?php

$dbinfo = $config['system']['database'];
$db = new \altef\lull7\PDO('mysql:dbname='.$dbinfo['database'].';host='.$dbinfo['host'].';charset=utf8', $dbinfo['username'], $dbinfo['password']);

$cache = new \altef\keyvaluestore\Local();
$users = new \altef\lull7\Users($db, $dbinfo['tables']['users'], $cache);

$username = '__test-user1';

p("Creating table <code>".$dbinfo['tables']['users']."</code> if it does not already exist.");
$users->_createTable();

p("Generating a password");
$password = $users->createHash(22);
affirm(strlen($password) == 22);

p("Attempting to creating a user");
$id = $users->createUser($username, $password);
affirm($id > 0);

p("Verifying that users exist");
affirm($users->exist());

p("Retrieving by ID");
$user = $users->byId($id);
json($user);
affirm($user['email'] == $username);


p("Retrieving by Username");
$user = $users->byUsername($username);
json($user);
affirm($user['id'] == $id);

p("Checking password");
affirm(password_verify($password, $user['password']));

p("Updating last login");
$old = time();
$users->updateLastLogin($id);
$user = $users->byId($id);
json($user);
affirm(strtotime($user['last_login']) >= $old);

p("Updating last seen");
$old = time();
$users->updateLastSeen($id);
$user = $users->byId($id);
json($user);
affirm(strtotime($user['last_seen']) >= $old);



//   forgotPassword() byKey() resetPassword()
p("Checking forgot password sequence...");
p("Generating key");
$key = $users->forgotPassword($username);
affirm(strlen($key) == 64);

p("Resetting password");

$password2 = $users->createHash(22);
affirm($password != $password2);

affirm($users->resetPassword($key, $password2));

p("Verifying new password");
$user = $users->byId($id);
affirm(password_verify($password2, $user['password']));





p("Deleting user");
$users->del($id);
$user = $users->byId($id);
json($user);
affirm($user == null);
