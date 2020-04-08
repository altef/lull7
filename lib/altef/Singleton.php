<?php

namespace altef;

abstract class Singleton {
	// This is a static class.
	// This section manages that.  Continue onward to see the class it instantiates.
	protected static $instance;
			
	public static function getInstance() {
		if (static::$instance === null) {
			static::$instance = new static();
		}
		return static::$instance;
	}
	private function __clone() {}
	private function __wakeup() {}
}