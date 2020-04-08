<?php

namespace endpoints;

// Spoof another user, if you're allowed (So we can test other accounts without their login credentials)
class AuthSpoofEndpoint extends \altef\lull7\Endpoint {
	
	// This should be posted, but for now I'm going to GET
	public function get($path, $data) {
		$this->requireLogin();
		$id = $this->id_int(2, $path);
		
		if ($this->api->auth->canSpoof()) {
			try {
				$this->api->auth->loginById($id);
				return $this->api->auth->data();
			} catch (\Exception $e) {
				$this->api->error(404, "Are you sure that's a valid user id?");
			}
		} 
		$this->api->error(401, "You don't have permission to be here!");
	}
}



?>