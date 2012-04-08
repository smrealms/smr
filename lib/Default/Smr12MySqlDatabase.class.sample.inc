<?php
require_once('MySqlDatabase.class.inc');
class Smr12MySqlDatabase extends MySqlDatabase
{
	private static $host = 'localhost';
	private static $databaseName = 'smr_live';
	private static $user = 'smr';
	private static $password = 'VG4HdZfjF8TY2Cdj';
	public function Smr12MySqlDatabase()
	{
		parent::__construct(self::$host, self::$user, self::$password, self::$databaseName);
	}
}
?>