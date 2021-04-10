<?php declare(strict_types=1);

namespace Smr;

use mysqli;
use RuntimeException;
use Smr\Container\DiContainer;
use Smr\DatabaseProperties;

class Database {
	private mysqli $dbConn;
	private DatabaseProperties $dbProperties;
	private string $selectedDbName;
	/**
	 * @var bool | mysqli_result
	 */
	private $dbResult = null;
	private ?array $dbRecord = null;

	public static function getInstance(): self {
		return DiContainer::make(self::class);
	}

	/**
	 * Reconnects to the MySQL database, and replaces the managed mysqli instance
	 * in the dependency injection container for future retrievals.
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	private static function reconnectMysql(): mysqli {
		$newMysqli = DiContainer::make(mysqli::class);
		DiContainer::getContainer()->set(mysqli::class, $newMysqli);
		return $newMysqli;
	}

	public static function mysqliFactory(DatabaseProperties $dbProperties): mysqli {
		if (!mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT)) {
			throw new RuntimeException('Failed to enable mysqli error reporting');
		}
		$mysql = new mysqli(
			$dbProperties->getHost(),
			$dbProperties->getUser(),
			$dbProperties->getPassword(),
			$dbProperties->getDatabaseName());
		$charset = $mysql->character_set_name();
		if ($charset != 'utf8') {
			throw new RuntimeException('Unexpected charset: ' . $charset);
		}
		return $mysql;
	}

	/**
	 * Database constructor.
	 * Not intended to be constructed by hand. If you need an instance of Database,
	 * use Database::getInstance();
	 * @param ?mysqli $dbConn The mysqli instance (null if reconnect needed)
	 * @param DatabaseProperties $dbProperties The properties object that was used to construct the mysqli instance
	 */
	public function __construct(?mysqli $dbConn, DatabaseProperties $dbProperties) {
		if (is_null($dbConn)) {
			$dbConn = self::reconnectMysql();
		}
		$this->dbConn = $dbConn;
		$this->dbProperties = $dbProperties;
		$this->selectedDbName = $dbProperties->getDatabaseName();
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
		$this->switchDatabases($this->dbProperties->getDatabaseName());
	}

	/**
	 * Returns the size of the selected database in bytes.
	 */
	public function getDbBytes() {
		$query = 'SELECT SUM(data_length + index_length) as db_bytes FROM information_schema.tables WHERE table_schema=' . $this->escapeString($this->selectedDbName);
		$result = $this->dbConn->query($query);
		return (int)$result->fetch_assoc()['db_bytes'];
	}

	/**
	 * This should not be needed except perhaps by persistent connections
	 *
	 * Closes the connection to the MySQL database. After closing this connection,
	 * this instance is no longer valid, and will subsequently throw exceptions when
	 * attempting to perform database operations.
	 *
	 * You must call Database::getInstance() again to retrieve a valid instance that
	 * is reconnected to the database.
	 *
	 * @return bool Whether the underlying connection was closed by this call.
	 */
	public function close() : bool {
		if (!isset($this->dbConn)) {
			// Connection is already closed; nothing to do.
			return false;
		}
		$this->dbConn->close();
		unset($this->dbConn);
		// Set the mysqli instance in the dependency injection container to
		// null so that the Database constructor will reconnect the next time
		// it is called.
		DiContainer::getContainer()->set(mysqli::class, null);
		return true;
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

	public function getBoolean(string $name) : bool {
		if ($this->dbRecord[$name] === 'TRUE') {
			return true;
		} elseif ($this->dbRecord[$name] === 'FALSE') {
			return false;
		}
		$this->error('Field is not a boolean: ' . $name);
	}

	public function getInt($name) {
		return (int)$this->dbRecord[$name];
	}

	public function getFloat($name) {
		return (float)$this->dbRecord[$name];
	}

	public function getMicrotime(string $name) : string {
		// All digits of precision are stored in a MySQL bigint
		$data = $this->dbRecord[$name];
		return sprintf('%f', $data / 1E6);
	}

	public function getObject($name, $compressed = false, $nullable = false) {
		$object = $this->getField($name);
		if ($nullable === true && $object === null) {
			return null;
		}
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

	public function escape($escape) {
		if (is_bool($escape)) {
			return $this->escapeBoolean($escape);
		}
		if (is_numeric($escape)) {
			return $this->escapeNumber($escape);
		}
		if (is_string($escape)) {
			return $this->escapeString($escape);
		}
		if (is_array($escape)) {
			return $this->escapeArray($escape);
		}
		if (is_object($escape)) {
			return $this->escapeObject($escape);
		}
	}

	public function escapeString(?string $string, bool $nullable = false) : string {
		if ($nullable === true && ($string === null || $string === '')) {
			return 'NULL';
		}
		return '\'' . $this->dbConn->real_escape_string($string) . '\'';
	}

	public function escapeBinary($binary) {
		return '0x' . bin2hex($binary);
	}

	/**
	 * Warning: If escaping a nested array, use escapeIndividually=true,
	 * but beware that the escaped array is flattened!
	 */
	public function escapeArray(array $array, string $delimiter = ',', bool $escapeIndividually = true) : string {
		if ($escapeIndividually) {
			$string = join($delimiter, array_map(function($item) { return $this->escape($item); }, $array));
		} else {
			$string = $this->escape(join($delimiter, $array));
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

	public function escapeMicrotime(float $microtime) : string {
		// Retain all digits of precision for storing in a MySQL bigint
		return sprintf('%d', $microtime * 1E6);
	}

	public function escapeBoolean(bool $bool) : string {
		// We store booleans as an enum
		if ($bool) {
			return '\'TRUE\'';
		} else {
			return '\'FALSE\'';
		}
	}

	public function escapeObject($object, bool $compress = false, bool $nullable = false) : string {
		if ($nullable === true && $object === null) {
			return 'NULL';
		}
		if ($compress === true) {
			return $this->escapeBinary(gzcompress(serialize($object)));
		}
		return $this->escapeString(serialize($object));
	}
}
