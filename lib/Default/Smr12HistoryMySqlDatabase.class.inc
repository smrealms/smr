<?php

class Smr12HistoryMySqlDatabase extends MySqlDatabase {
	public function __construct() {
		parent::__construct(self::$dbName_Smr12History);
	}
}
