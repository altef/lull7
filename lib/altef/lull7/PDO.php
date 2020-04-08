<?php
namespace altef\lull7;

// A simple extension to PDO with helper functions to streamline a few common tasks
class PDO extends \PDO {

	public function __construct( $dsn, $username=null, $passwd=null, $options=null) {
		 parent::__construct($dsn, $username, $passwd, $options);
		$this->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
		$this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$this->setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC );
	}


	// Any private static function below can be accessed as a member function (without the $db params)
	// Or as a static function with the db param.  For example, both of these will work, assuming DB is an instance of \altef\lull7\PDO
	//		\altef\lull7\PDO::assoc($db, 'show tables'), 
	//		$db->assoc('show tables')
	
	// The reason for this is that I wanted some helper functions that I could use
	// But I didn't want to enforce the use of my PDO wrapper.
	// This way I can use it, if I want to, but my library classes don't assume its use.
	// They use the helper functions statically.  But for any classes I make for a *particular* API
	// I will probably assume its use, and call via the member function.
	// The end.


	// Executes some SQL and returns the resulting data
	private static function assoc($db, $sql, $params=null, $mode=\PDO::FETCH_ASSOC) {
		$statement = self::effect($db, $sql, $params);
		return $statement->fetchAll($mode);
	}


	// Executes some SQL and returns the statement
	private static function effect($db, $sql, $params=null) {
		$statement = $db->prepare($sql);
		$statement->execute($params);
		return $statement;
	}

	// ** TODO: verify this is correct
	private static function success($db, $statement) {
		return $statement->errorCode() == "00000";
	}

	// The magic function that do that. If a private function named $name exists in the class, call it with the $arguments.
	public static function __callStatic($name, $arguments) {
		if (method_exists(__CLASS__, $name)) return call_user_func_array([__CLASS__,$name], $arguments);
	}

	// If a private function named $name exists in the class, call it with $this, and the $arguments.
	public function __call($name, $arguments) {
		if (method_exists(__CLASS__, $name)) return call_user_func_array([__CLASS__,$name], array_merge([$this], $arguments));
	}

}
