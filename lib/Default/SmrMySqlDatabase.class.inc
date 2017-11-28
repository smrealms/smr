<?php
require_once('MySqlDatabase.class.inc');
require_once(CONFIG . 'SmrMySqlSecrets.inc');

class SmrMySqlDatabase extends MySqlDatabase {
	// add host, user, password, and databaseName static members via traits
	use SmrMySqlSecrets;
	public function SmrMySqlDatabase() {
		parent::__construct(self::$host, self::$user, self::$password, self::$databaseName);
	}
}
?>
