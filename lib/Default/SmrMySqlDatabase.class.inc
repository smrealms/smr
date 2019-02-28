<?php

class SmrMySqlDatabase extends MySqlDatabase {
	public function __construct() {
		parent::__construct(self::$databaseName);
	}
}
