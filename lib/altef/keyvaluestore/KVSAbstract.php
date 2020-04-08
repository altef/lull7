<?php
namespace altef\keyvaluestore;

abstract class KVSAbstract {
	protected $prefix;

	public function __construct($prefix = 'kvs_') {
		$this->prefix = $prefix;
	}

	abstract protected function get($key);
	abstract protected function store($key, $value, $ttl = 3600);

	public function del($key) { $this->store($key, null, -1); }

	protected function keyify($key) {
		return $this->prefix . $key;
	}
	
	// Session has a specific ID
	public function id() {
		return null;
	}
}