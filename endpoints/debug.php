<?php

namespace endpoints;

class DebugEndpoint extends \altef\lull7\Endpoint {
	
	public function get($path, $data) {
		if (count($path) > 1) {
			// Here we want to check if the user has access to debug.. 
			// But I'm not going to do that yet, because oh well
			$this->api->setDebug(in_array(strtolower($path[1]), ['1', 1, 'true', 'on']));
		} 
		return $this->api->isDebug();
	}
}



?>