<?php
namespace altef\lull7;

class Settings  {
	private $database;
	private $table;


	public function __construct($database, $table) {
		$this->database = $database;
		$this->table = $table;
	}

	public function _createTable() {
		$this->database->query(
			'CREATE TABLE IF NOT EXISTS `'.$this->table.'` (
				`user_id` INT(10) UNSIGNED NOT NULL DEFAULT \'0\',
				`key` VARCHAR(50) NOT NULL,
				`value` TEXT NOT NULL,
				PRIMARY KEY (`user_id`, `key`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;'
		);
	}

	public function get($key, $user_id=0) {
		$result = PDO::assoc($this->database, 'SELECT coalesce(`value`) as `value` FROM (
			SELECT `value` FROM `'.$this->table.'` WHERE `key` = ? AND `user_id` = ?
			UNION
			SELECT `value` FROM `'.$this->table.'` WHERE `key` = ? AND `user_id` = 0
			) AS a', [$key, $user_id, $key]);
		if ($result !== false && count($result) == 1)
			$result = json_decode($result[0]['value'], true);
		else 
			$result = null;
		return $result;
	}

	public function getAll($user_id=0) {
		$results = PDO::assoc($this->database, 'SELECT `key`, coalesce(`value`) as `value` FROM (
			SELECT `key`, `value` FROM `'.$this->table.'` WHERE `user_id` = ?
			UNION
			SELECT `key`, `value` FROM `'.$this->table.'` WHERE `user_id` = 0
			) AS a GROUP BY `key`', [$user_id]);
		
		$out = [];
		foreach($results as $k=>$result) {
			if ($result !== false)
				$out[$result['key']] = json_decode($result['value'], true);
			else 
				$out[$result['key']] = null;
		}
		return $out;
	}


	public function store($key, $value, $user_id=0) { 
		$stmt = PDO::effect($this->database, 'INSERT INTO `'.$this->table.'` (`key`, `value`, `user_id`) VALUES (:key, :value, :user_id) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)',
			[
				'key' => $key,
				'value' => JSON_ENCODE($value),
				'user_id' => $user_id
			]
		);
		return true;
	}
	

	public function del($key, $user_id=0) {
		$stmt = PDO::effect($this->database, 'DELETE FROM `'.$this->table.'` Where `key` = :key AND `user_id` = :user_id',
			[
				'key' => $key,
				'user_id' => $user_id
			]
		);
		return true;
	}

}
