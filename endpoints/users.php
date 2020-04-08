<?php

namespace endpoints;

// This endpoint should be heavily altered to your needs.
// Probably only certain users should be able to perform these actions.
// Also, you might adjust your users table (and extend the `\altef\lull7\Users`) and require more fields in the post.
class UsersEndpoint extends \altef\lull7\Endpoint {
	
	public function get($path, $data) {
		$this->requireLogin();
		$this->api->error(404, "Not implemented."); // Probably you don't want just any user to be able to see all others
	}
	
	public function post($path, $data) {
		// Create a new user
		$username = $data['u'] ?? '';
		
		if (strlen($username) == 0) 
			$this->api->error(406, "Please supply a username via 'u'");
		// Maybe validate that it's an email address, if we're using those.  Otherwise, let the verification process as described below help.
		
		
		// Create the user with no password, have it email the password reset link
		// Possibly the user's class has been extended, and you need to pass more data.  For now, I'll just use the basic one
		try {
			$id = $this->api->users->createUser($username, null, $this->api->config->get('system')->get('default_permissions'));
		} catch (\Exception $e) {
			$this->api->error($e->getCode(), $e->getMessage());
		}
		// You could also update here if there's extra data
		// Send a password reset email to the user
		$user = $this->api->users->byId($id);
		$key = $this->api->users->forgotPassword($user['email']);
		$data = [
			'url' =>$this->api->config->get('client_url') . '/auth/reset?key=' . $key
		];
		$result = $this->api->email->send($user['email'], $this->api->config->get('email')->get('from'), $this->api->config->get('email')->get('subjects')->get('welcome'), 'welcome', $data);
		if ($result === false)
			$this->api->error(500, "User created, but couldn't send welcome email.");
		return $id;
	}
	
	public function put($path, $data) {
		$this->requireLogin();
		$this->api->error(404, "Not implemented."); // Update logic goes here
	}
	
	public function delete($path, $data) {
		$this->requireLogin();
		$this->api->error(404, "Not implemented."); // Possibly you want every user to be able to delete itself, but some to delete others
	}
}



?>