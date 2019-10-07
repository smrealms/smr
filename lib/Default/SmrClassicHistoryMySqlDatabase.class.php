<?php

class SmrClassicHistoryMySqlDatabase extends MySqlDatabase {
	public function __construct() {
		parent::__construct(self::$dbName_SmrClassicHistory);
	}
}
