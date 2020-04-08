<?php
namespace altef\keyvaluestore;

class APCuSession extends KVSAbstract {
		public $_id;

		public function id($id = null) {
			if ($id != null) { 
				$this->_id = $id;
				setcookie( session_name(), $this->_id, time()+$this->length, '/' );	// Respect PHP's passing of sessions.
			}
			return $this->_id;
		}

		public function __construct($prefix = 'session_') {
			parent::__construct($prefix);
			$this->id($_REQUEST['sid'] ?? $_COOKIE[session_name()] ?? uniqid(true));
		}


		public function store($key, $value, $ttl = 3600) {
			return apcu_store($this->keyify($key), igbinary_serialize($value), $ttl );
		}

		public function get($key, $updated_ttl = null) {
			$key = $this->keyify($key);
			$result = apcu_fetch($key);
			if ($result === false)
				return null;
			if ($updated_ttl != null)
				apcu_store($key, $result, $updated_ttl); // Update TTL
			return igbinary_unserialize($result);
		}

		protected function keyify($key) {
			return $this->prefix . $this->_id . '_' . $key;
		}
}
?>