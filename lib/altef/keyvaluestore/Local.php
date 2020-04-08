<?php
namespace altef\keyvaluestore;


class Local extends KVSAbstract {

	private $data = [];

	public static function recursive($data, $depth = 0) {
		$map = new Local();
		foreach($data as $k=>$v) {
			if (is_array($v)) { // Should this only be for associative arrays?
				$map->store($k, Self::recursive($v, $depth + 1));
			} else {
				$map->store($k, $v);
			}
		}
		return $map;
	}


	public function __construct($data = []) {
		$this->data = $data;
	}


	public function store($key, $value, $ttl = 'IGNORED') { // TTL is ignored, because why
		$this->data[$key] = $value;
		return true;
	}

	public function get($key = null) {
		if ($key == null)
			return $this->data;
		if (array_key_exists($key, $this->data))
			return $this->data[$key];
		throw new \Exception("Key not found: $key");
	}

	public function has($key) {
		return array_key_exists($key, $this->data);
	}
}

?>