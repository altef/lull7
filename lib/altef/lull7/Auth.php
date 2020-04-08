<?php

namespace altef\lull7;

class Auth {
	private $users = null;
	private $logged_in = false;
	private $data = null;
	private $session;

	public function __get($name) {
		if ($name == 'database')
			return $this->users->database;
		throw new \Exception("No such property: $name");
	}


	public function __construct($users, $session) {
		$this->session = $session;
		$this->users = $users;
	}


	public function userId() {
		if (is_array($this->data) && array_key_exists('id', $this->data))
			return $this->data['id'];
		throw new \Exception("Not logged in.", 401);
	}


	public function login($username, $password) {
		$data = $this->users->byUsername($username);
		if (password_verify( $password, $data['password'] )) {
			return $this->_login($data);
		}
		return false;
	}


	private function _login($user) {
		$user['permissions'] = json_decode($user['permissions'], true );
		unset($user['password']);
		$this->session->store('user', $user);
		$this->users->updateLastLogin($user['id']);
		return $this->isLoggedIn();
	}


	public function loginById($id) {
		$data = $this->users->byId($id);
		if ($data === false)
			throw new \Exception("Invalid user.");
		return $this->_login($data);
	}


	public function isLoggedIn() : bool {
		$this->data = $this->session->get('user');
		if (is_array($this->data)) {
			$this->users->updateLastSeen($this->data['id']);
			return true;
		}
		return false;
	}


	public function logout() {
		$this->session->del('user');
		return true;
	}


	public function data() {
		return array_merge($this->data == null ? [] : $this->data, ['sid'=>$this->session->id()]);
	}


	// This is just a note, I'll have to rewrite this.
	// Basically the idea is I don't want anything touching the permissions object directly, except this class.
	// This class should also handle the session data.
	// Check along the hierarchy to see if the last key exists (with anything but false) or any of the ones before it are true)
	public function hasPermission($hierarchy) { return true; }

	// Has sufficient permissions to spoof another account.
	public function canSpoof() {
		
		return @$this->data['permissions']['canSpoof'] == "1"; 
	}


	// Has sufficient permissions to turn on debug mode
	public function canDebug() {
		return @$this->data['permissions']['canDebug'] == "1"; 
	}
}