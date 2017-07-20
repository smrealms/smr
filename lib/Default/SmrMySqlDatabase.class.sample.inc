<?php
require_once('MySqlDatabase.class.inc');
class SmrMySqlDatabase extends MySqlDatabase {
	private static $host = 'smr-mysql';
	private static $databaseName = 'smr_live';
	private static $user = 'smr';
	private static $password = 'smr';
	public function SmrMySqlDatabase() {
		parent::__construct(self::$host, self::$user, self::$password, self::$databaseName);
	}
}
?>