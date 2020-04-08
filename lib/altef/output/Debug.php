<?php
namespace altef\output;

class Debug {

	const Verbose = "verbose";
	const Debug = "debug";
	const Silent = "silent";

	private $verbosity = Debug::Verbose;

	public function __construct($verbosity = self::Verbose) {
		$this->setVerbosity($verbosity);
	}


	public function setVerbosity($verbosity) {
		$this->verbosity = $verbosity;
		$this->v("Output verbosity set to $verbosity");
	}


	public function v($o) {
		if (in_array($this->verbosity, array(Debug::Verbose)))
			return $this->o($o, "v");
		return false;
	}


	public function d($o) {
		if (in_array($this->verbosity, array(Debug::Verbose, Debug::Debug)))
			return $this->o($o, "d");
		return false;
	}


	private function o($o, $tag) {
		echo $tag . ": " . json_encode($o, JSON_PRETTY_PRINT) . "\n";
		return true;
	}

}
?>