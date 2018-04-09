<?php
trait SmrMySqlSecrets {
	private static $host = 'smr-mysql';
	private static $databaseName = 'smr_live';
	private static $user = 'smr';
	private static $password = 'smr';

	// Set to null to use defaults
	private static $port = null;
	private static $socket = null;

	// Uncomment if using compatibility databases
	//private static $dbName_Smr12History = 'smr_12_history';
	//private static $dbName_Smr12 = 'smr_live';
	//private static $dbName_SmrClassicHistory = 'smr_classic_history';
	//private static $dbName_SmrClassic = 'smr_live';
}
