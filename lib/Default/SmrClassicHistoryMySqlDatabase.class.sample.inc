<?php

// include database class for mysql
require_once('MySqlDatabase.class.inc');

class SmrClassicHistoryMySqlDatabase extends MySqlDatabase
{
	private static $host = 'localhost';
	private static $databaseName = 'smr_history';
	private static $user = 'smr';
	private static $password = 'YmR3vK6eDKPYrX67';
	public function SmrClassicHistoryMySqlDatabase()
	{
		parent::__construct(self::$host, self::$user, self::$password, self::$databaseName);
	}
}

?>