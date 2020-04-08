<?php 
/**
 * This just acts as a global holder/singleton for your api libraries, and for common API functions (like error, or json out)
 */
use \altef\keyvaluestore\Local as Map;
 
class API extends \altef\lull7\Api {
	public $system_db;

	protected function __construct() {
		global $config, $argv;
		parent::__construct();
		$this->configure($config);

		// Connect to the database.  You can use whatever connection method you want here as long as it's basically PDO
		// But you don't have even have to use a database if you don't want to
		$dbinfo = $this->config->get('system')->get('database');
		// This is just an extension of PDO to make things easier for me.  You can use base PDO if you prefer.
		$this->system_db = new \altef\lull7\PDO('mysql:dbname='.$dbinfo->get('database').';host='.$dbinfo->get('host').';charset=utf8', $dbinfo->get('username'), $dbinfo->get('password'));


		// Configure and add in the other stuff we'll want..
		// Auth, debug, mailer, users
		if (isset($argv))
			$session = new \altef\keyvaluestore\LocalSoft(); // Don't want to use default sessions if we're coming from the CLI
		else
			$session = new \altef\keyvaluestore\Session();

		$this->add('session', $session);
		$this->add('debug', new \altef\output\Debug($this->isDebug() ? $this->config->get('verbosity')->get('debug') : $this->config->get('verbosity')->get('default')));
		$this->add('cache', new \altef\keyvaluestore\Database($this->system_db, $this->config->get('system')->get('database')->get('tables')->get('persistent_global_map')));


		// If you don't want to use any of these, you don't have to.  
		// Or you can extend them with your own classes.  
		// Or you can replace them entirely.
		// You can also add more (and probably should).
		// Really you can do whatever you want.
		// All things added here can be accessed through $api->[that thing].  So for items, below, $api->items;

		$this->add('email', new \altef\output\email\Sendmail($this->config->get('email_template_directory')));
		$this->add('users', new \altef\lull7\Users($this->system_db, $this->config->get('system')->get('database')->get('tables')->get('users'), $this->cache));
		$this->add('auth', new \altef\lull7\Auth($this->users, $this->session));
		$this->add('settings', new \altef\lull7\Settings($this->system_db, 'settings'));

	}
}