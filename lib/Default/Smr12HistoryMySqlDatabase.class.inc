<?php

require_once('MySqlDatabase.class.inc');
require_once(CONFIG . 'SmrMySqlSecrets.inc');

class Smr12HistoryMySqlDatabase extends MySqlDatabase {
	use SmrMySqlSecrets;
	public function Smr12HistoryMySqlDatabase() {
		parent::__construct(self::$host, self::$user, self::$password, self::$dbName_Smr12History);
	}
}

?>
