<?php
namespace altef\lull7;

class SimpleCRUD {
	private $database;
	private $table;
	
	private $fields;
	
	public function __construct($database, $table) {
		$this->database = $database;
		$this->table = $table;
	}
	
	public function read($where_map = null) {
		$params = [];
		$q = 'SELECT * FROM `'. $this->table.'`';
		if ($where_map != null) { // Only return a single primary
			$where_map = $this->strip_extraneous($where_map);
			if (count($where_map) > 0) {
				$q .= ' WHERE ' . implode(" AND ", array_map(function($a) { return "`$a`=:$a"; }, array_keys($where_map)));
				//$primaries = $this->pri($this->fields());
				$params = $where_map;
			}
		}
		
		return PDO::assoc($this->database, $q, $params);
	}
	

	public function create($data) {
		// Strip out any data passed in that isn't in the table
		$data = $this->strip_extraneous($data);
		
		// Figure out a way to deal with AUTO INCREMENT
		// if ["Extra"] == "auto_increment"
		$data = $this->strip_auto_increment($data);
		
		$q = 'INSERT INTO `'.$this->table .'` (`'. implode("`,`", array_keys($data)) . "`) VALUES (" . implode(",", array_map(function($a) { return ":".$a; }, array_keys($data))). ")";
		$stmt = $this->database->prepare($q);
		$stmt->execute($data);
		
		
		if ($this->isAutoIncrement())
			return $this->database->lastInsertId();
		else
			return $stmt->rowCount() == 1;
	}
	
	public function update($data) {
		// Strip out any data passed in that isn't in the table
		$data = $this->strip_extraneous($data);
		$keys = $this->retrieve_primaries($data);
		$values = $this->retrieve_values($data);

		// Make sure that all the keys are present
		foreach($this->pri($this->fields()) as $p) {
			if (!array_key_exists($p['Field'], $keys)) {
				throw new \Exception("Missing field expected: " . $p['Field'], 400);
			}
		}
		
		// REMOVE primaries from data saved where PRIMARIES
		$q = 'UPDATE `'.$this->table.'` SET ' . implode(", ", array_map(function($a) { return "`$a` = :$a"; }, array_keys($values)));
		$q.= ' WHERE ' . implode(" AND ", array_map(function($a) { return "`$a` = :$a"; }, array_keys($keys)));
		
		$stmt = $this->database->prepare($q);
		$stmt->execute($data);
		return $stmt->rowCount() == 1;
	}
	
	public function del($data) {
		$keys = $this->retrieve_primaries($data);
		// Make sure that all the keys are present
		foreach($this->pri($this->fields()) as $p) {
			if (!array_key_exists($p['Field'], $keys)) {
				throw new \Exception("Missing field expected: " . $p['Field'], 400);
			}
		}
		
		// WHERE keys
		$q = 'DELETE FROM `'.$this->table.'` WHERE ' . implode(" AND ", array_map(function($a) { return "`$a` = :$a"; }, array_keys($keys)));
		$stmt = $this->database->prepare($q);
		$stmt->execute($keys);
		return $stmt->rowCount() == 1;
	}
	
	public function softCreate($data) {
		// On duplicate key update
		// all BUT primaries
		
		// Strip out any data passed in that isn't in the table
		$data = $this->strip_extraneous($data);
		
		$keys = $this->retrieve_values($data);
		$values = $this->retrieve_values($data);
		
		
		$q = 'INSERT INTO `'.$this->table .'` (`'. implode("`,`", array_keys($data)) . "`) VALUES (" . implode(",", array_map(function($a) { return ":".$a; }, array_keys($data))). ")";
		$q .= ' ON DUPLICATE KEY UPDATE ' . implode(", ", array_map(function($a) { return "`$a`=VALUES(`$a`)";}, array_keys($values)));
		$stmt = $this->database->prepare($q);
		$stmt->execute($data);
		
		if ($this->isAutoIncrement())
			return $this->database->lastInsertId();
		else
			return $stmt->rowCount() == 1;
	}
	
	
	private function isAutoIncrement() {
		foreach($this->fields() as $f) {
			if ($f['Extra'] == 'auto_increment')
				return true;
		}
		return false;
	}
	
	
	private function fancy_filter($source, $extra, $compare) {
		$out = [];
		foreach($source as $k=>$v) {
			if ($compare(['key'=>$k, 'value'=>$v], $extra))
				$out[$k] = $v;
		}
		return $out;
	}
	
		
	private function strip_extraneous($data) {
		return $this->fancy_filter($data, $this->fields(), function($a, $b) { return array_key_exists($a['key'], $b); });
	}

	private function strip_auto_increment($data) {
		return $this->fancy_filter($data, $this->fields(), function($a, $b) { return $b[$a['key']]['Extra'] != 'auto_increment'; });
	}
	
	private function retrieve_primaries($data) {
		return $this->fancy_filter($data, $this->fields(), function($a, $b) { return $b[$a['key']]['Key'] == 'PRI'; });
	}

	private function retrieve_values($data) {
		return $this->fancy_filter($data, $this->fields(), function($a, $b) { return $b[$a['key']]['Key'] != 'PRI'; });
	}
	
	
	
	
	private function non_pri($fields) {
		return  array_filter($fields, function($f) { return $f['Key'] != 'PRI'; });
	}

	
	/**
	 * Returns the field entry for the primary key
	 * @param $fields list (from $this->fields(...))
	 */
	private function pri( $fields ) {
		return  array_filter($fields, function($f) { return $f['Key'] == 'PRI'; });
	}


	/**
	 * Returns a list of field objects.
	 * @param $table name.
	 */
	private function fields() {
		if ($this->fields == null) {
			$stmt = $this->database->prepare( 'SHOW COLUMNS FROM `'.$this->table.'`' );
			$stmt->execute();
			$fields = [];
			foreach($stmt->fetchAll(\PDO::FETCH_ASSOC) as $f)
				$fields[$f['Field']] = $f;
			$this->fields = $fields;
		}
		return $this->fields;
	}
	
	
}


?>