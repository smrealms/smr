<?php

require_once('MySqlDatabase.class.inc');
require_once(CONFIG . 'SmrMySqlSecrets.inc');

class SmrClassicHistoryMySqlDatabase extends MySqlDatabase {
	use SmrMySqlSecrets;
	public function __construct() {
		parent::__construct(self::$host, self::$user, self::$password, self::$dbName_SmrClassicHistory,
		                    self::$port, self::$socket);
	}
}
