<?php
namespace altef\keyvaluestore;

class Session extends KVSAbstract {

	public function __construct($prefix = 'session_') {
		parent::__construct($prefix);
		
		// Support the sid param
		if (@array_key_exists('sid', $_REQUEST)) { // Restart an existing session
			session_id($_REQUEST['sid']);
		}

		// Also support Authorization: Bearer xxx
		$headers = getallheaders();
		if (strtolower($_SERVER['REQUEST_METHOD']) != 'options') {
			foreach($headers as $k=>$v) {
				if (strtolower($k) == 'authorization' and strpos($v, 'Bearer ') !== false) {
					$sid = $v;
					$sid = str_replace('Bearer ', '', $sid);
					session_id($sid);
				}
			}
		}

		session_start();
	}


	public function id() {
		return session_id();
	}


	public function store($key, $value, $ttl = 3600) { // This TTL is actually a max TTL.  It could still be GCed before this
		$_SESSION[$this->keyify($key)] = [
			'expires_at' => time() + $ttl,
			'data' => $value
		];
		return true;
	}


	public function get($key) {
		$key = $this->keyify($key);
		if (array_key_exists($key, $_SESSION)) {
			$o = $_SESSION[$key];
			if (time() < $o['expires_at'])
				return $o['data'];
			else
				unset($_SESSION[$key]);
		}
		return null;
	}


	public function write_close() {
		session_write_close();
	}

}
?>