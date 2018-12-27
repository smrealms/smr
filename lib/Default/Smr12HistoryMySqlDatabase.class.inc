<?php

require_once(CONFIG . 'SmrMySqlSecrets.inc');

class Smr12HistoryMySqlDatabase extends MySqlDatabase {
	use SmrMySqlSecrets;
	public function __construct() {
		parent::__construct(self::$host, self::$user, self::$password, self::$dbName_Smr12History,
		                    self::$port, self::$socket);
	}
}
