<?php

namespace endpoints;

// Handle log in and out
class AuthResetEndpoint extends \altef\lull7\Endpoint {
	
	// This should be posted, but for now I'm going to GET
	public function get($path, $data) {
		$key = $data['key'] ?? '';
		if (strlen($key) == 0)
			$this->api::error(406, "Please supply a key");
		
		
		$password = $data['p'] ?? '';
		if (strlen($password) == 0)
			$this->api::error(406, "Please supply a password");

		if ($this->api->users->resetPassword($key, $password))
			return true;
		else
			$this->api::error(404, "I couldn't locate that key, has it expired?");
	}
}



?>