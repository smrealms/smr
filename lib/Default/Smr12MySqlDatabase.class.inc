<?php

require_once('MySqlDatabase.class.inc');
require_once(CONFIG . 'SmrMySqlSecrets.inc');

class Smr12MySqlDatabase extends MySqlDatabase {
	use SmrMySqlSecrets;
	public function Smr12MySqlDatabase() {
		parent::__construct(self::$host, self::$user, self::$password, self::$dbName_Smr12);
	}
}
?>
