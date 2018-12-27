<?php
require_once(CONFIG . 'SmrMySqlSecrets.inc');

class SmrSessionMySqlDatabase extends MySqlDatabase {
	// add static members via traits
	use SmrMySqlSecrets;
	public function __construct() {
		parent::__construct(self::$host, self::$user, self::$password, self::$databaseName,
		                    self::$port, self::$socket);
	}
}
