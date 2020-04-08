<?php
namespace altef\keyvaluestore;

class Database extends KVSAbstract {
	private $database;
	private $table;


	public function __construct($database, $table) {
		parent::__construct(''); // No prefix
		$this->database = $database;
		$this->table = $table;
		
		$this->cleanup();
	}

	public function _createTable() {
		// Create the table if it doesn't exist
		$this->database->query(
			'CREATE TABLE IF NOT EXISTS `'.$this->table.'` (
				`key` varchar(100) NOT NULL,
				`value` TEXT,
				`expires_at` TIMESTAMP NOT NULL,
				PRIMARY KEY (`key`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;'
		);
	}


	public function get($key) {
		$stmt = $this->database->prepare('SELECT `value` FROM `'.$this->table.'` WHERE `key` = :key AND `expires_at` > NOW()');
		$result = $stmt->execute(['key' => $this->keyify($key)]);
		$result = $stmt->fetch(\PDO::FETCH_ASSOC);
		if ($result !== false)
			$result = json_decode($result['value'], true);
		else 
			$result = null;
		return $result;
	}


	public function store($key, $value, $ttl = 3600) { // 1 hour default TTL
		$stmt = $this->database->prepare('INSERT INTO `'.$this->table.'` (`key`, `value`, `expires_at`) VALUES (:key, :value, FROM_UNIXTIME(:expires_at)) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`), `expires_at`=VALUES(`expires_at`)');
		$stmt->execute([
			'key' => $this->keyify($key),
			'value' => JSON_ENCODE($value),
			'expires_at' => time() + $ttl
		]);
		return true;
	}

	public function del($key) {
		parent::del($key);
		$this->cleanup();
	}

	// Delete entries that have expired
	private function cleanup() {
		try {
			$stmt = $this->database->query('DELETE FROM '.$this->table.' WHERE expires_at < NOW()');
		} catch (\PDOException $e) { }  // Just ignore this error.
	}
}
