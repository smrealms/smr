<?php declare(strict_types=1);

use Smr\Container\DiContainer;
use Smr\MySqlProperties;

class MySqlDatabase {
	private mysqli $dbConn;
	private MySqlProperties $mysqlProperties;
	private string $selectedDbName;
	/**
	 * @var bool | mysqli_result
	 */
	private $dbResult = null;
	/**
	 * @var array | null
	 */
	private $dbRecord = null;

	public static function getInstance(bool $requireNewInstance = false): MySqlDatabase {
		$class = MySqlDatabase::class;
		return !$requireNewInstance
			? DiContainer::get($class)
			: DiContainer::make($class);
	}

	/**
	 * MySqlDatabase constructor.
	 * Not intended to be constructed by hand. If you need an instance of MySqlDatabase,
	 * use MySqlDatabase::getInstance();
	 * @param mysqli $dbConn The mysqli instance
	 * @param MySqlProperties $mysqlProperties The properties object that was used to construct the mysqli instance
	 */
	public function __construct(mysqli $dbConn, MySqlProperties $mysqlProperties) {
		$charset = $dbConn->character_set_name();
		if ($charset != 'utf8') {
			$this->error('Unexpected charset: ' . $charset);
		}
		$this->dbConn = $dbConn;
		$this->mysqlProperties = $mysqlProperties;
		$this->selectedDbName = $mysqlProperties->getDatabaseName();
	}

	/**
	 * This method will switch the connection to the specified database.
	 * Useful for switching back and forth between historical, and live databases.
	 *
	 * @param string $databaseName The name of the database to switch to
	 */
	public function switchDatabases(string $databaseName) {
		$this->dbConn->select_db($databaseName);
		$this->selectedDbName = $databaseName;
	}

	/**
	 * Switch back to the configured live database
	 */
	public function switchDatabaseToLive() {
		$this->switchDatabases($this->mysqlProperties->getDatabaseName());
	}

	/**
	 * Returns the size of the selected database in bytes.
	 */
	public function getDbBytes() {
		$query = 'SELECT SUM(data_length + index_length) as db_bytes FROM information_schema.tables WHERE table_schema=' . $this->escapeString($this->selectedDbName);
		$result = $this->dbConn->query($query);
		return (int)$result->fetch_assoc()['db_bytes'];
	}

	// This should not be needed except perhaps by persistent connections
	public function close() {
		if ($this->dbConn) {
			$this->dbConn->close();
			unset($this->dbConn);
		}
	}

	public function query($query) {
		$this->dbResult = $this->dbConn->query($query);
	}

	/**
	 * Use to populate this instance with the next record of the active query.
	 */
	public function nextRecord(): bool {
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
	public function requireRecord(): void {
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
//		$this->error('Field is not a boolean');
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
		$sec = substr($data, 0, 10);
		$msec = substr($data, 10);
		if (strlen($msec) != 6) {
			if ($pad_msec) {
				$msec = str_pad($msec, 6, '0', STR_PAD_LEFT);
			} else {
				$this->error('Field is not an escaped microtime (' . $data . ')');
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
		$this->dbConn->query('LOCK TABLES ' . $table . ' WRITE');
	}

	public function unlock() {
		$this->dbConn->query('UNLOCK TABLES');
	}

	public function getNumRows() {
		return $this->dbResult->num_rows;
	}

	public function getChangedRows() {
		return $this->dbConn->affected_rows;
	}

	public function getInsertID() {
		return $this->dbConn->insert_id;
	}

	protected function error($err) {
		throw new RuntimeException($err);
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
			return '\'' . $this->dbConn->real_escape_string($string) . '\'';
		}
		return $this->dbConn->real_escape_string($string);
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
			$this->error('Not a number! (' . $num . ')');
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
			$this->error('Not a boolean: ' . $bool);
		}
	}

	public function escapeObject($object, $compress = false, $quotes = true, $nullable = false) {
		if ($compress === true) {
			return $this->escapeBinary(gzcompress(serialize($object)));
		}
		return $this->escapeString(serialize($object), $quotes, $nullable);
	}
}
