<?php


if (requiredInput(['email', 'password'], "You must supply the email address and password of a valid user for this test.")) {

	p("You've supplied an email address and password");
	json($email);
	affirm($email !== false && $password !== false);


	//login
	p("Attempting to login");
	$data = request('POST', 'auth', ['u'=>$email, 'p'=>$password]);
	$data = json_decode($data, true);
	json($data);
	affirm($data['email'] == $email);
	$sid = $data['sid'];
	$headers = ['Authorization' => 'Bearer '.$sid];

	// Check auth bearer
	p("Checking Authorization Bearer method");
	$data = request('GET', 'auth', [], $headers);
	$data = json_decode($data, true);
	affirm($data['email'] == $email);


	// Check SID
	p("Checking SID method");
	$data = request('GET', 'auth', ['sid'=>$sid]);
	$data = json_decode($data, true);
	affirm($data['email'] == $email);


	// Check SID
	p("Checking PHPSESSID method");
	$data = request('GET', 'auth', [], ['Cookie'=>'PHPSESSID='.$sid]);
	$data = json_decode($data, true);
	affirm($data['email'] == $email);
	json($data);
	
	// logout
	p("Attempting to logout");
	$data = request('DELETE', 'auth', [], $headers);
	$data = json_decode($data, true);
	$data = request('GET', 'auth', [], $headers);
	$data = json_decode($data, true);
	affirm($data['result'] === false);

	// forgot
	p("Hitting the /auth/forgot endpoint &ndash; check your email");
	$data = request('GET', 'auth/forgot', ['u'=>$email], $headers);
	$data = json_decode($data, true);
	affirm($data);

}
?>