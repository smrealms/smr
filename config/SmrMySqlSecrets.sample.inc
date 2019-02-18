<?php
trait SmrMySqlSecrets {
	private static $databaseName = 'smr_live';
	private static $host = 'smr-mysql'; // must match MYSQL_HOST in .env
	private static $user = 'smr';       // must match MYSQL_USER in .env
	private static $password = 'smr';   // must match MYSQL_PASSWORD in .env

	// Set to null to use defaults
	private static $port = null;
	private static $socket = null;

	// Uncomment if using history databases
	//private static $dbName_Smr12History = 'smr_12_history';
	//private static $dbName_SmrClassicHistory = 'smr_classic_history';
}
