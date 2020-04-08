<?php

namespace altef\lull7;

use \altef\keyvaluestore\Local as Map;

class Api extends \altef\Singleton {
	const DEBUGKEY = 'isDebug';
	
	private $properties = null;
	
	public function add($name, $obj) {
		$this->properties->store($name, $obj);
	}

	public function __get($name) {
		return $this->properties->get($name);
	}


	public function isDebug() {
		return $this->session->get(self::DEBUGKEY) == true;
	}


	public function setDebug(bool $v) {
		$this->session->store(self::DEBUGKEY, $v);
	}


	protected function configure($config) {
		$this->properties->store('config', Map::recursive($config));
	}


	protected function __construct() {
		$this->properties = new Map();
	}


	// In case something has cone wrong
	public static function error( $code, $desc ) {
		$codes = array();
		$codes[400] = 'Bad Request';
		$codes[401] = 'Unauthorized';
		$codes[403] = 'Forbidden';
		$codes[404] = 'Not Found';
		$codes[405] = 'Method Not Allowed';
		$codes[406] = 'Not Acceptable';
		$codes[500] = 'Internal Server Error';
		$codes[501] = 'Not Implemented';
		$codes[503] = 'Service unavailable';
		
	
		@header( 'HTTP/1.1 '. $code . ' '.  $desc ); //@$codes[$code] - chrome doesn't download the body anymore
		if (self::getInstance()->isDebug()) {
			//print_r(debug_backtrace());
		}
		die( json_encode( array( 'result'=>false, 'error'=>$code, 'description'=>$desc ) ) );
	}


	public static function json_out($output) {
		self::finish(json_encode($output, JSON_PRETTY_PRINT));
	}


	public static function finish($output) {
		echo $output;
		die();
	}
	
}
?>