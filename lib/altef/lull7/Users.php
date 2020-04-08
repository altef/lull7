<?php
namespace altef\lull7;

class Users {
	
	private $database;
	private $table;
	private $reset_key_prefix = 'reset_';
	private $cache;
	
	public function __get($name) {
		if ($name == 'database')
			return $this->users->database;
		throw new Exception("No such property: $name");
	}

	public function __construct($database, $table, $cache) {
		$this->database = $database;
		$this->table = $table;
		$this->cache = $cache;
	}

	public function _createTable() {
		$this->database->query(
			'CREATE TABLE IF NOT EXISTS `'.$this->table.'` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`email` varchar(255) NOT NULL,
				`password` varchar(255) NOT NULL,
				`permissions` TEXT,
				`last_login` DATETIME NULL DEFAULT NULL,
				`last_seen` DATETIME NULL DEFAULT NULL,
				PRIMARY KEY (`id`),
				UNIQUE KEY `email` (`email`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;'
		);
	}


	public function byId($id) {
		return PDO::assoc( $this->database, 'SELECT * FROM `'.$this->table.'` where id = ?', [$id] )[0]; 
	}

	public function exist() {
		$d = PDO::assoc($this->database, 'SELECT count(*) as number FROM `'.$this->table.'`' ); 
		return $d[0]['number'] > 0;
	}


	public function byUsername($username) {
		return PDO::assoc($this->database, 'SELECT * FROM `'.$this->table.'` where email = ?', [$username] )[0]; 
	}

	public function updateLastLogin($id) {
		$this->updateOneRaw('last_login', 'NOW()', $id);
	}

	public function updateLastSeen($id) {
		$this->updateOneRaw('last_seen', 'NOW()', $id);
	}
	
	
	private function updateOne($field, $value, $id) {
		$statement = PDO::effect($this->database, 'UPDATE `'.$this->table.'` SET ' . $field . ' = ? where id = ?', [$value, $id]);
		return PDO::success($this->database, $statement);
	}
	
	private function updateOneRaw($field, $value, $id) {
		$statement = PDO::effect($this->database, 'UPDATE `'.$this->table.'` SET ' . $field . ' = '.$value.' where id = ?', [$id]);
		return PDO::success($this->database, $statement);
	}
	
	
	public function forgotPassword($email) {
		// Generate a key, save it in redis
		$u = $this->byUsername($email);
		if ($u === false)
			return false;

		$key = $this->createHash( 64, 2 );
		$this->cache->store($this->reset_key_prefix.$key, $u['id'], 86400); // 1 day
		return $key;
		// If we're worried, store the mapping the other way around.. And check if any other mappings exist, and remove from redis. We don't care ATM.
		// Maybe return the key.  That way the endpoint can send the email, and we can unit test this better.
	}
	
	public function resetPassword($key, $password) {
		$u = $this->byKey($key);
		if($u === false) 
			return false;
		
		$hash = password_hash( $password, PASSWORD_DEFAULT);
		if ($this->updateOne('password', $hash, $u['id'])) {
			$this->cache->del($this->reset_key_prefix.$key);
			$u = $this->byId($u['id']);
			return true;
		}
		return false;
	}
	
	public function byKey($key) {
		// check for a key, if it exists get the user id
		// return byId($id)
		$id = $this->cache->get($this->reset_key_prefix.$key);
		if ($id === false)
			return false;
		return $this->byId($id);
	}
	
	
	public function del($id) {
		$this->database->effect('DELETE FROM `'.$this->table.'` WHERE id=?', [$id]);
		return true;
	}
	
	/**
	 * Inserts a new user.
	 * @param $username email address.
	 * @param $password. A random password will be chosen if unspecified.
	 * @return Boolean.
	 */
	public function createUser( $username, $password=null, $permissions = [] ) {
		if ( $password == null )
			$password = $this->createHash(10);
		
		$data = array(
			'email'=>$username
		);
		
		$data['hash'] = password_hash( $password, PASSWORD_DEFAULT);
		$data['permissions'] = json_encode($permissions, JSON_PRETTY_PRINT);
		$query = 'INSERT INTO `'.$this->table.'` (`email`, `password`, `permissions`) VALUES(:email, :hash, :permissions)';
		
		$stmt = $this->database->prepare( $query );
		if($stmt->execute( $data ))
			return $this->database->lastInsertId();
		switch($stmt->errorCode()) {
			case '23000':
				throw new \Exception("User already exists.", 406);
			default:
				throw new Exception($stmt->errorInfo()[2], $stmt->errorCode());
		}
		return false;
	}
	
	
	public function createHash( $length=22, $sets=1 ) {
		$range = array( array( 65, 90 ), array( 97, 122 ), array(48,57), array( 40,47 ), array( 91, 95 ) );
		$pass = '';
		if ( $sets > count( $range ) )
			$sets = count( $range )-1;
		for($i=0; $i< $length; $i++) {
			$list = rand( 0, $sets );
			$pass .= chr(rand($range[$list][0],$range[$list][1]));
		}
		return $pass;
	}
	
}
?>