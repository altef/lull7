<?php

namespace endpoints;

// Handle log in and out
class AuthEndpoint extends \altef\lull7\Endpoint {
	
	// Return your logged in data
	public function get($path, $data) {
		$this->requireLogin();
		return $this->api->auth->data();
	}
	
	// Try to login
	public function post($path, $data) {
		$username = $data['u'] ?? '';
		$password = $data['p'] ?? '';
		
		if (strlen($username) == 0) 
			$this->api::error(406, "Please supply a username via 'u'");
		if (strlen($password) == 0) 
			$this->api::error(406, "Please supply a password via 'p'");
		
		if ($this->api->auth->login($username, $password))
			return $this->api->auth->data();
		$this->api::error(401, 'Invalid credentials.');
	}
	
	// Logout
	public function delete($path, $data) {
		$this->requireLogin();
		return $this->api->auth->logout();
	}
}



?>