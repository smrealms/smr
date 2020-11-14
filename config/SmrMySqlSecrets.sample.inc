<?php
trait SmrMySqlSecrets {
	protected static $databaseName = 'smr_live';
	private static $host = 'smr-mysql'; // must match MYSQL_HOST in .env
	private static $user = 'smr';       // must match MYSQL_USER in .env
	private static $password = 'smr';   // must match MYSQL_PASSWORD in .env

	// Set to null to use defaults
	private static $port = null;
	private static $socket = null;
}
