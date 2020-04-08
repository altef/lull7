<?php

namespace endpoints;

class SettingsEndpoint extends \altef\lull7\Endpoint {
	
	public function get($path, $data) {
		$this->requireLogin();
		$id = $this->id(1, $path);
		if ($id) {
			return $this->api->settings->get($id, $this->api->auth->userId());
		}
		return $this->api->settings->getAll($this->api->auth->userId());
	}

	private function store($path, $data) {
		$this->requireLogin();
		$this->requireParam(['value'], $data);
		$id = $this->id(1, $path);
		if ($id) {
			return $this->api->settings->store($id, $data['value'], $this->api->auth->userId());
		}
		$this->api->error(400, "You must specify a setting.");
	}

	public function post($path, $data) {
		$this->store($path, $data);
	}

	public function put($path, $data) {
		$this->store($path, $data);
	}


	public function delete($path, $data) {
		$this->requireLogin();
		$id = $this->id(1, $path);
		if ($id) {
			return $this->api->settings->del($id, $this->api->auth->userId());
		}
		$this->api->error(400, "You must specify a setting.");
	}



}



?>