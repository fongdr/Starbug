<?php
# Copyright (C) 2008-2010 Ali Gangji
# Distributed under the terms of the GNU General Public License v3
/**
 * This file is part of StarbugPHP
 * @file core/sb.php
 * The global sb object. provides data, errors, import/provide, load and pub/sub. The backbone of Starbug
 * @author Ali Gangji <ali@neonrain.com>
 * @ingroup core
 */
/**
 * The sb class. Provides data, errors, import/provide, pub/sub, and load to the rest of the application. The core component of StarbugPHP.
 * @ingroup core
 */
class sb {
	/**#@+
	* @access public
	*/
	/**
	 * @var db The db class is a PDO wrapper
	 */
	var $db;
	/**
	 * @var array active user
	 */
	var $user = false;
	/**
	 * @var string holds the active scope (usually 'global' or a model name)
	 */
	var $active_scope = "global";
	/**
	 * @var array holds the utils that have been provided
	 */
	var $provided = array();
	/**
	 * @var array holds validation errors
	 */
	var $errors = array();
	/**
	 * @var array holds alerts
	 */
	var $alerts = array();
	/**
	 * @var array holds $db change listeners
	 */
	var $listeners = array();
	/**#@-*/
	static $instance;


	/**
	 * constructor. connects to db and starts the session
	 */
	function __construct($db) {
		self::$instance = $this;
		$this->db = $db;
		if (defined("Etc::DEBUG")) $this->db->set_debug(Etc::DEBUG);
		$this->start_session();
	}

	function set_database($db) {
		$this->db = $db;
		if (defined("Etc::DEBUG")) $this->db->set_debug(Etc::DEBUG);
		foreach ($this->listeners as $object) $object->set_database($db);
	}

	/**
	 * load the Session class and validate the current session if the user has a cookie
	 */
	function start_session() {
		if (false !== ($session = Session::active())) {
			if (!empty($session['v']) && is_numeric($session['v'])) {
				$user = new query("users");
				$user = $user->select("users.*,users.groups as groups,users.statuses as statuses")->condition("users.id", $session['v'])->one();
				if (Session::validate($session, $user['password'], Etc::HMAC_KEY)) {
					$user['groups'] = is_null($user['groups']) ? array() : explode(",", $user['groups']);
					$user['statuses'] = is_null($user['statuses']) ? array() : explode(",", $user['statuses']);
					$this->user = $user;
				}
			}
		}
	}

	/**
	 * import function. only imports once when used with provide
	 * @param string $loc path of file to import without '.php' at the end
	 */
	function import($loc) {
		$sb = self::$instance;
		$args = func_get_args();
		foreach($args as $l) {
			$parts = explode("/", $l);
			if (!in_array($parts[0], array("app", "core", "util", "var")) && file_exists(BASE_DIR."/modules/".$parts[0])) $parts[0] = "modules/".$parts[0];
			$path = implode("/", $parts);
			if (!isset($this->provided[$l])) include(BASE_DIR."/".$path.".php");
		}
	}

	/**
	 * when imported use provide to prevent further imports from attempting to include it again
	 * @param string $loc the imported location. if i were to use $sb->import("util/form"), util/form.php would have $sb->provide("util/form") at the top
	 */
	function provide($loc) {$this->provided[$loc] = true;}

	/**
	 * get a model by name
	 * @param string $name the name of the model, such as 'users'
	 * @return the instantiated model
	 */
	function get($name) {
		return $this->db->model($name);
	}

	function add_listener($object) {
		$this->listeners[] = $object;
	}

}
?>
