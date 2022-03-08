<?php declare(strict_types=1);

namespace Smr;

use mysqli;
use RuntimeException;
use Smr\Container\DiContainer;

/**
 * Wraps an active connection to the database.
 * Primarily provides query, escaping, and locking methods.
 */
class Database {

	/**
	 * Returns the instance of this class from the DI container.
	 * If one does not exist yet, it will be created.
	 * This is the intended way to construct this class.
	 */
	public static function getInstance(): self {
		return DiContainer::get(self::class);
	}

	/**
	 * Used by the DI container to construct a mysqli instance.
	 * Not intended to be used outside the DI context.
	 */
	public static function mysqliFactory(DatabaseProperties $dbProperties): mysqli {
		if (!mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT)) {
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
	 * Not intended to be constructed by hand. If you need an instance of Database,
	 * use Database::getInstance();
	 *
	 * @param mysqli $dbConn The mysqli instance
	 * @param string $dbName The name of the database that was used to construct the mysqli instance
	 */
	public function __construct(
		private mysqli $dbConn,
		private string $dbName,
	) {}

	/**
	 * This method will switch the connection to the specified database.
	 * Useful for switching back and forth between historical, and live databases.
	 *
	 * @param string $databaseName The name of the database to switch to
	 */
	public function switchDatabases(string $databaseName): void {
		$this->dbConn->select_db($databaseName);
	}

	/**
	 * Switch back to the configured live database
	 */
	public function switchDatabaseToLive(): void {
		$this->switchDatabases($this->dbName);
	}

	/**
	 * Returns the size of the current database in bytes.
	 */
	public function getDbBytes(): int {
		$query = 'SELECT SUM(data_length + index_length) as db_bytes FROM information_schema.tables WHERE table_schema=(SELECT database())';
		return $this->read($query)->record()->getInt('db_bytes');
	}

	/**
	 * This should not be needed except perhaps by persistent connections
	 *
	 * Closes the connection to the MySQL database. After closing this connection,
	 * this instance is no longer valid, and will subsequently throw exceptions when
	 * attempting to perform database operations.
	 *
	 * Once the connection is closed, you must call Database::reconnect() before
	 * any further database queries can be made.
	 *
	 * @return bool Whether the underlying connection was closed by this call.
	 */
	public function close(): bool {
		if (!isset($this->dbConn)) {
			// Connection is already closed; nothing to do.
			return false;
		}
		$this->dbConn->close();
		unset($this->dbConn);
		// Set the mysqli instance in the dependency injection container to
		// null so that we don't accidentally try to use it.
		DiContainer::getContainer()->set(mysqli::class, null);
		return true;
	}

	/**
	 * Reconnects to the MySQL database, and replaces the managed mysqli instance
	 * in the dependency injection container for future retrievals.
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	public function reconnect(): void {
		if (isset($this->dbConn)) {
			return; // No reconnect needed
		}
		$newMysqli = DiContainer::make(mysqli::class);
		DiContainer::getContainer()->set(mysqli::class, $newMysqli);
		$this->dbConn = $newMysqli;
	}

	/**
	 * Perform a write-only query on the database.
	 * Used for UPDATE, DELETE, REPLACE and INSERT queries, for example.
	 */
	public function write(string $query): void {
		$result = $this->dbConn->query($query);
		if ($result !== true) {
			throw new RuntimeException('Wrong query type');
		}
	}

	/**
	 * Perform a read-only query on the database.
	 * Used for SELECT queries, for example.
	 */
	public function read(string $query): DatabaseResult {
		return new DatabaseResult($this->dbConn->query($query));
	}

	/**
	 * INSERT a row into $table.
	 *
	 * @param array<string, mixed> $fields
	 * @return int Insert ID of auto-incrementing column, if applicable
	 */
	public function insert(string $table, array $fields): int {
		$query = 'INSERT INTO ' . $table . ' (' . implode(', ', array_keys($fields))
			. ') VALUES (' . implode(', ', array_values($fields)) . ')';
		$this->write($query);
		return $this->getInsertID();
	}

	public function lockTable(string $table): void {
		$this->write('LOCK TABLES ' . $table . ' WRITE');
	}

	public function unlock(): void {
		$this->write('UNLOCK TABLES');
	}

	public function getChangedRows(): int {
		return $this->dbConn->affected_rows;
	}

	public function getInsertID(): int {
		return $this->dbConn->insert_id;
	}

	public function escape(mixed $escape): mixed {
		return match (true) {
			is_bool($escape) => $this->escapeBoolean($escape),
			is_numeric($escape) => $this->escapeNumber($escape),
			is_string($escape) => $this->escapeString($escape),
			is_array($escape) => $this->escapeArray($escape),
			is_object($escape) => $this->escapeObject($escape),
		};
	}

	public function escapeString(?string $string, bool $nullable = false): string {
		if ($nullable === true && ($string === null || $string === '')) {
			return 'NULL';
		}
		return '\'' . $this->dbConn->real_escape_string($string) . '\'';
	}

	public function escapeBinary(string $binary): string {
		return '0x' . bin2hex($binary);
	}

	/**
	 * Warning: If escaping a nested array, use escapeIndividually=true,
	 * but beware that the escaped array is flattened!
	 */
	public function escapeArray(array $array, string $delimiter = ',', bool $escapeIndividually = true): string {
		if ($escapeIndividually) {
			$string = implode($delimiter, array_map(function($item) { return $this->escape($item); }, $array));
		} else {
			$string = $this->escape(implode($delimiter, $array));
		}
		return $string;
	}

	public function escapeNumber(mixed $num): mixed {
		// Numbers need not be quoted in MySQL queries, so if we know $num is
		// numeric, we can simply return its value (no quoting or escaping).
		if (!is_numeric($num)) {
			throw new RuntimeException('Not a number: ' . $num);
		}
		return $num;
	}

	public function escapeMicrotime(float $microtime): string {
		// Retain all digits of precision for storing in a MySQL bigint
		return sprintf('%d', $microtime * 1E6);
	}

	public function escapeBoolean(bool $bool): string {
		// We store booleans as an enum
		if ($bool) {
			return '\'TRUE\'';
		} else {
			return '\'FALSE\'';
		}
	}

	public function escapeObject(mixed $object, bool $compress = false, bool $nullable = false): string {
		if ($nullable === true && $object === null) {
			return 'NULL';
		}
		if ($compress === true) {
			return $this->escapeBinary(gzcompress(serialize($object)));
		}
		return $this->escapeString(serialize($object));
	}

}
