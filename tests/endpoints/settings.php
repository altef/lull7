<?php

if (requiredInput(['email', 'password'], "You must supply the email address and password of a valid user for this test.")) {

//login
	p("Attempting to login");
	$data = request('POST', 'auth', ['u'=>$email, 'p'=>$password]);
	$data = json_decode($data, true);
	json($data);
	affirm($data['email'] == $email);
	$sid = $data['sid'];
	$headers = ['Authorization' => 'Bearer '.$sid];
	
	
	p("Adding a setting <code>$name1</code>");
	$name1 = 'test-endpoint-settings-1';
	$value1 = 'one';
	$data = request('POST', 'settings/'.$name1, ['value'=>$value1], $headers);
	
	$data = request('GET', 'settings/'.$name1, [], $headers);
	$data = json_decode($data, true);
	json($data);
	affirm($data == $value1);

	p("Retrieving all settings applicable to this user");
	$data = request('GET', 'settings', [], $headers);
	$data = json_decode($data, true);
	affirm($data[$name1] == $value1);
	
	p("Attempting to update the setting");
	$value2 = "two";
	$data = request('PUT', 'settings/'.$name1, ['value'=>$value2], $headers);
	$data = request('GET', 'settings/'.$name1, [], $headers);
	$data = json_decode($data, true);
	json($data);
	affirm($data == $value2);
	

	// Delete a setting
	p("Deleting the setting <code>$name1</code>");
	$data = request('GET', 'debug/on', [], $headers);
	$data = request('DELETE', 'settings/'.$name1, [], $headers);
	$data = request('GET', 'settings/'.$name1, [], $headers);
	$data = json_decode($data, true);
	// json($data);
	affirm($data == null);

}