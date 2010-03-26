<?php

// include database class for mysql
require_once('MySqlDatabase.class.inc');

class Smr12HistoryMySqlDatabase extends MySqlDatabase
{
	private static $host     = 'localhost';
	private static $databaseName = 'smr_history';
	private static $user     = 'smr';
	private static $password = 'YmR3vK6eDKPYrX67';
	public function Smr12HistoryMySqlDatabase()
	{
		parent::__construct(self::$host, self::$user, self::$password, self::$databaseName);
	}
}

?>