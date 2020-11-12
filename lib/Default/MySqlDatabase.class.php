<?php declare(strict_types=1);

abstract class MySqlDatabase {
	protected static $dbConn;
	protected static $selectedDbName;
	protected $dbResult = null;
	protected $dbRecord = null;

	public function __construct($dbName=null, MysqlProperties $mysqlProperties=null) {
		if (!self::$dbConn) {
			if(!$mysqlProperties){
				$mysqlProperties = new MysqlProperties();
			}
			// Set the mysqli driver to raise exceptions on errors
			if (!mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT)) {
				$this->error('Failed to enable mysqli error reporting');
			}

			$dbName = $dbName ?? $mysqlProperties->getDatabaseName();
			self::$dbConn = new mysqli(
				$mysqlProperties->getHost(),
				$mysqlProperties->getUser(),
				$mysqlProperties->getPassword(),
				$dbName,
				$mysqlProperties->getPort());
			self::$selectedDbName = $dbName;

			// Default server charset should be set correctly. Using the default
			// avoids the additional query involved in `set_charset`.
			$charset = self::$dbConn->character_set_name();
			if ($charset != 'utf8') {
				$this->error('Unexpected charset: ' . $charset);
			}
		}

		// Do we need to switch databases (e.g. for compatability db access)?
		if ($dbName != null &&self::$selectedDbName != $dbName) {
			self::$dbConn->select_db($dbName);
			self::$selectedDbName = $dbName;
		}
	}

	/**
	 * Returns the size of the selected database in bytes.
	 */
	public function getDbBytes() {
		$query = 'SELECT SUM(data_length + index_length) as db_bytes FROM information_schema.tables WHERE table_schema=' . $this->escapeString(self::$selectedDbName);
		$result = self::$dbConn->query($query);
		return (int)$result->fetch_assoc()['db_bytes'];
	}

	// This should not be needed except perhaps by persistent connections
	public function close() {
		if (self::$dbConn) {
			self::$dbConn->close();
			self::$dbConn = false;
		}
	}

	public function query($query) {
		$this->dbResult = self::$dbConn->query($query);
	}

	/**
	 * Use to populate this instance with the next record of the active query.
	 */
	public function nextRecord() : bool {
		if (!$this->dbResult) {
			$this->error('No resource to get record from.');
		}

		if ($this->dbRecord = $this->dbResult->fetch_assoc()) {
			return true;
		}
		return false;
	}

	/**
	 * Use instead of nextRecord when exactly one record is expected from the
	 * active query.
	 */
	public function requireRecord() : void {
		if (!$this->nextRecord() || $this->getNumRows() != 1) {
			$this->error('One record required, but found ' . $this->getNumRows());
		}
	}

	public function hasField($name) {
		return isset($this->dbRecord[$name]);
	}

	public function getField($name) {
		return $this->dbRecord[$name];
	}

	public function getBoolean($name) {
		if ($this->dbRecord[$name] == 'TRUE') {
			return true;
		}
//		if($this->dbRecord[$name] == 'FALSE')
		return false;
//		throw new Exception('Field is not a boolean');
	}

	public function getInt($name) {
		return (int)$this->dbRecord[$name];
	}

	public function getFloat($name) {
		return (float)$this->dbRecord[$name];
	}

	// WARNING: In the past, Microtime was stored in the database incorrectly.
	// For backwards compatibility, set $pad_msec=true to try to guess at the
	// intended value. This is not safe if the Microtime length is wrong for an
	// unrelated reason!
	public function getMicrotime($name, $pad_msec = false) {
		$data = $this->dbRecord[$name];
		$sec  = substr($data, 0, 10);
		$msec = substr($data, 10);
		if (strlen($msec) != 6) {
			if ($pad_msec) {
				$msec = str_pad($msec, 6, '0', STR_PAD_LEFT);
			} else {
				throw new Exception('Field is not an escaped microtime (' . $data . ')');
			}
		}
		return "$sec.$msec";
	}

	public function getObject($name, $compressed = false) {
		$object = $this->getField($name);
		if ($compressed === true) {
			$object = gzuncompress($object);
		}
		return unserialize($object);
	}

	public function getRow() {
		return $this->dbRecord;
	}

	public function lockTable($table) {
		self::$dbConn->query('LOCK TABLES ' . $table . ' WRITE');
	}

	public function unlock() {
		self::$dbConn->query('UNLOCK TABLES');
	}

	public function getNumRows() {
		return $this->dbResult->num_rows;
	}

	public function getChangedRows() {
		return self::$dbConn->affected_rows;
	}

	public function getInsertID() {
		return self::$dbConn->insert_id;
	}

	protected function error($err) {
		throw new Exception($err);
	}

	public function escape($escape, $autoQuotes = true, $quotes = true) {
		if (is_bool($escape)) {
			if ($autoQuotes) {
				return $this->escapeBoolean($escape);
			} else {
				return $this->escapeBoolean($escape, $quotes);
			}
		}
		if (is_numeric($escape)) {
			return $this->escapeNumber($escape);
		}
		if (is_string($escape)) {
			if ($autoQuotes) {
				return $this->escapeString($escape);
			} else {
				return $this->escapeString($escape, $quotes);
			}
		}
		if (is_array($escape)) {
			return $this->escapeArray($escape, $autoQuotes, $quotes);
		}
		if (is_object($escape)) {
			if ($autoQuotes) {
				return $this->escapeObject($escape);
			} else {
				return $this->escapeObject($escape, $quotes);
			}
		}
	}

	public function escapeString($string, $quotes = true, $nullable = false) {
		if ($nullable === true && ($string === null || $string === '')) {
			return 'NULL';
		}
		if ($string === true) {
			$string = 'TRUE';
		} elseif ($string === false) {
			$string = 'FALSE';
		}
		if (is_array($string)) {
			$escapedString = '';
			foreach ($string as $value) {
				$escapedString .= $this->escapeString($value, $quotes) . ',';
			}
			return substr($escapedString, 0, -1);
		}
		if ($quotes) {
			return '\'' . self::$dbConn->real_escape_string($string) . '\'';
		}
		return self::$dbConn->real_escape_string($string);
	}

	public function escapeBinary($binary) {
		return '0x' . bin2hex($binary);
	}

	public function escapeArray(array $array, $autoQuotes = true, $quotes = true, $implodeString = ',', $escapeIndividually = true) {
		$string = '';
		if ($escapeIndividually) {
			foreach ($array as $value) {
				if (is_array($value)) {
					$string .= $this->escapeArray($value, $autoQuotes, $quotes, $implodeString, $escapeIndividually) . $implodeString;
				} else {
					$string .= $this->escape($value, $autoQuotes, $quotes) . $implodeString;
				}
			}
			$string = substr($string, 0, -1);
		} else {
			$string = $this->escape(implode($implodeString, $array), $autoQuotes, $quotes);
		}
		return $string;
	}

	public function escapeNumber($num) {
		// Numbers need not be quoted in MySQL queries, so if we know $num is
		// numeric, we can simply return its value (no quoting or escaping).
		if (is_numeric($num)) {
			return $num;
		} else {
			throw new Exception('Not a number! (' . $num . ')');
		}
	}

	public function escapeMicrotime($microtime, $quotes = false) {
		$sec_str = sprintf('%010d', $microtime);
		$usec_str = sprintf('%06d', fmod($microtime, 1) * 1E6);
		return $this->escapeString($sec_str . $usec_str, $quotes);
	}

	public function escapeBoolean($bool, $quotes = true) {
		if ($bool === true) {
			return $this->escapeString('TRUE', $quotes);
		} elseif ($bool === false) {
			return $this->escapeString('FALSE', $quotes);
		} else {
			throw new Exception('Not a boolean: ' . $bool);
		}
	}

	public function escapeObject($object, $compress = false, $quotes = true, $nullable = false) {
		if ($compress === true) {
			return $this->escapeBinary(gzcompress(serialize($object)));
		}
		return $this->escapeString(serialize($object), $quotes, $nullable);
	}

}
