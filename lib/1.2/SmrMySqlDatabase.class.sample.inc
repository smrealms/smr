<?php
require_once(get_file_loc('MySqlDatabase.class.inc'));
class SmrMySqlDatabase extends MySqlDatabase
{
	private static $host = 'localhost';
	private static $databaseName = 'smr_live';
	private static $user = 'smr';
	private static $password = 'VG4HdZfjF8TY2Cdj';
	public function SmrMySqlDatabase()
	{
		parent::__construct(self::$host, self::$user, self::$password, self::$databaseName);
	}
}
?>