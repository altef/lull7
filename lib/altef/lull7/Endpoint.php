<?php

namespace altef\lull7;

/**
 * An Endpoint super class for endpoints to extend.
 * Written by Bradley Gill
 * altered effect (http://alteredeffect.com)
 * bradgill@gmail.com
 */
class Endpoint {
	
	protected $api;
	
	public function __construct($api) {
		$this->api = $api;
	}
	
	// HTTP verbs to implement
	public function get( $chunks, $data ) { Api::error(405, "That's no way to access this endpoint, sir.");}
	public function put( $chunks, $data ) { Api::error(405, "That's no way to access this endpoint, sir.");}
	public function post( $chunks, $data ) { Api::error(405, "That's no way to access this endpoint, sir.");}
	public function delete( $chunks, $data ) { Api::error(405, "That's no way to access this endpoint, sir.");}		
	public function options( $chunks, $data ) { die(); }

	protected function requireLogin() {
		if (!$this->api->auth->isLoggedIn())
			$this->api->error( 401, 'Please login' );
	}

	function requireParam($list, $source = false) {
		$list = is_array($list) ? $list : [$list];
		$source = $source ? $source : $_REQUEST;
		
		foreach($list as $l) {
			if (!array_key_exists($l, $source))
				Api::error(406, "'$l' is required.");
		}
	}

	protected function id($offset, $data) {
		return $data[$offset] ?? false;
	}

	protected function id_int($offset, $data) {
		$n = $this->id($offset, $data);
		return (is_numeric($n) and (string)(int)$n == (string)$n) ? intval($n) : false;
	}

	public static function prepareData($http_verb) {
		switch($http_verb) {
			case 'put':
			case 'delete':
				return json_decode(file_get_contents("php://input"), true );
			case 'get':
				return $_GET;
			case 'post':
				return $_POST;
			default:
				return null;
		}
	}
}
?>