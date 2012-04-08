<?php
require_once('MySqlDatabase.class.inc');
class SmrClassicMySqlDatabase extends MySqlDatabase
{
	private static $host = 'localhost';
	private static $databaseName = 'smr_live';
	private static $user = 'smr';
	private static $password = 'VG4HdZfjF8TY2Cdj';
	public function SmrClassicMySqlDatabase()
	{
		parent::__construct(self::$host, self::$user, self::$password, self::$databaseName);
	}
}
?>