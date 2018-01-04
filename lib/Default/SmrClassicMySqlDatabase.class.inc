<?php

require_once('MySqlDatabase.class.inc');
require_once(CONFIG . 'SmrMySqlSecrets.inc');

class SmrClassicMySqlDatabase extends MySqlDatabase {
	use SmrMySqlSecrets;
	public function SmrClassicMySqlDatabase() {
		parent::__construct(self::$host, self::$user, self::$password, self::$dbName_SmrClassic,
		                    self::$port, self::$socket);
	}
}
?>
