<?php

namespace endpoints;

// Spoof another user, if you're allowed (So we can test other accounts without their login credentials)
class AuthForgotEndpoint extends \altef\lull7\Endpoint {
	
	// This should be posted, but for now I'm going to GET
	public function get($path, $data) {
		$email = $data['u'] ?? '';
		if (strlen($email) == 0)
			$this->api->error(406, "Please specify an email address.");
		
		$user = $this->api->users->byUsername($email);
		if ($user === false)
			$this->api->error(404, "User not found.");
		
		$key = $this->api->users->forgotPassword($user['email']);
		$data = [
			'url' => $this->api->config->get('client_url') . '/auth/reset?key=' . $key
		];
		$result = $this->api->email->send($user['email'], $this->api->config->get('email')->get('from'), $this->api->config->get('email')->get('subjects')->get('forgot'), 'forgot', $data);
		return $result;
		
	}
}



?>