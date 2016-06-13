<?php
/**
 * Planning Poker for the backlog
 * @package  Poker
 * @author   Alan Hardman <alan@phpizza.com>
 * @version  0.0.1
 */

namespace Plugin\Poker;

class Base extends \Plugin {

	/**
	 * Initialize the plugin
	 */
	public function _load() {
		$f3 = \Base::instance();

		// Set up routes
		$f3->route("GET /backlog/poker", "Plugin\Poker\Controller->index");
		$f3->route("GET /backlog/poker/results", "Plugin\Poker\Controller->results");
		$f3->route("POST /backlog/poker", "Plugin\Poker\Controller->post");
	}

	/**
	 * Install plugin (add database tables)
	 */
	public function _install() {
		$f3 = \Base::instance();
		$db = $f3->get("db.instance");
		$install_db = file_get_contents(__DIR__ . "/db/database.sql");
		$db->exec(explode(";", $install_db));
	}

	/**
	 * Check if plugin is installed
	 * @return bool
	 */
	public function _installed() {
		$f3 = \Base::instance();
		$db = $f3->get("db.instance");
		$q = $db->exec("SHOW TABLES LIKE 'poker_vote'");
		return !!$db->count();
	}

}
