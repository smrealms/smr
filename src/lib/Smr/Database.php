<?php declare(strict_types=1);

namespace Smr;

use Exception;
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
	 * This should not be needed except perhaps by persistent services
	 * (such as Dicord/IRC clients) to prevent connection timeouts between
	 * callbacks.
	 *
	 * Closes the underlying database connection and resets the state of the
	 * DI container so that a new Database and mysqli instance will be made
	 * the next time Database::getInstance() is called. Existing instances of
	 * this class will no longer be valid, and will throw when attempting to
	 * perform database operations.
	 *
	 * This function is safe to use even if the DI container or the Database
	 * instances have not been initialized yet.
	 */
	public static function resetInstance(): void {
		if (DiContainer::initialized(mysqli::class)) {
			$container = DiContainer::getContainer();
			if (DiContainer::initialized(self::class)) {
				self::getInstance()->dbConn->close();
				$container->reset(self::class);
			}
			$container->reset(mysqli::class);
		}
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
			$dbProperties->host,
			$dbProperties->user,
			$dbProperties->password,
			$dbProperties->database,
		);
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
	 * @param \mysqli $dbConn The mysqli instance
	 * @param string $dbName The name of the database that was used to construct the mysqli instance
	 */
	public function __construct(
		private readonly mysqli $dbConn,
		private readonly string $dbName,
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
	 * Perform a write-only query on the database.
	 * Used for UPDATE, DELETE, REPLACE and INSERT queries, for example.
	 */
	public function write(string $query): void {
		$result = $this->dbConn->query($query);
		if ($result !== true) {
			throw new RuntimeException('Wrong query type or query failed');
		}
	}

	/**
	 * Perform a read-only query on the database.
	 * Used for SELECT queries, for example.
	 */
	public function read(string $query): DatabaseResult {
		$result = $this->dbConn->query($query);
		if (is_bool($result)) {
			throw new RuntimeException('Wrong query type or query failed');
		}
		return new DatabaseResult($result);
	}

	/**
	 * INSERT a row into $table.
	 *
	 * @param string $table
	 * @param array<string, mixed> $fields
	 * @return int Insert ID of auto-incrementing column, if applicable
	 */
	public function insert(string $table, array $fields): int {
		$query = 'INSERT INTO ' . $table . ' (' . implode(', ', array_keys($fields))
			. ') VALUES (' . implode(', ', array_values($fields)) . ')';
		$this->write($query);
		return $this->getInsertID();
	}

	/**
	 * REPLACE a row into $table.
	 *
	 * @param string $table
	 * @param array<string, mixed> $fields
	 * @return int Insert ID of auto-incrementing column, if applicable
	 */
	public function replace(string $table, array $fields): int {
		$query = 'REPLACE INTO ' . $table . ' (' . implode(', ', array_keys($fields))
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
		$affectedRows = $this->dbConn->affected_rows;
		if (is_string($affectedRows)) {
			throw new Exception('Number of rows is too large to represent as an int: ' . $affectedRows);
		}
		return $affectedRows;
	}

	public function getInsertID(): int {
		$insertID = $this->dbConn->insert_id;
		if (is_string($insertID)) {
			throw new Exception('Number of rows is too large to represent as an int: ' . $insertID);
		}
		return $insertID;
	}

	public function escape(mixed $escape): mixed {
		return match (true) {
			is_bool($escape) => $this->escapeBoolean($escape),
			is_float($escape) || is_int($escape) => $this->escapeNumber($escape),
			is_string($escape) => $this->escapeString($escape),
			is_array($escape) => $this->escapeArray($escape),
			is_object($escape) => $this->escapeObject($escape),
			default => throw new Exception('Unhandled value: ' . $escape)
		};
	}

	public function escapeNullableString(?string $string): string {
		if ($string === null || $string === '') {
			return 'NULL';
		}
		return $this->escapeString($string);
	}

	public function escapeString(string $string): string {
		return '\'' . $this->dbConn->real_escape_string($string) . '\'';
	}

	/**
	 * @param array<int>|array<string> $array
	 */
	public function escapeArray(array $array): string {
		return implode(',', array_map(fn($item) => $this->escape($item), $array));
	}

	/**
	 * @template T of int|float
	 * @param T $num
	 * @return T
	 */
	public function escapeNumber(int|float $num): int|float {
		// Numbers need not be quoted in MySQL queries, so if we know $num is
		// numeric, we can simply return its value (no quoting or escaping).
		return $num;
	}

	public function escapeBoolean(bool $bool): string {
		// We store booleans as an enum
		return $bool ? '\'TRUE\'' : '\'FALSE\'';
	}

	/**
	 * @param object|array<mixed>|string|null $object
	 */
	public function escapeNullableObject(object|array|string|null $object, bool $compress = false): string {
		if ($object === null) {
			return 'NULL';
		}
		return $this->escapeObject($object, $compress);
	}

	/**
	 * @param object|array<mixed>|string $object
	 */
	public function escapeObject(object|array|string $object, bool $compress = false): string {
		$objectStr = serialize($object);
		if ($compress === true) {
			$objectStr = gzcompress($objectStr);
			if ($objectStr === false) {
				throw new Exception('An error occurred while compressing the object');
			}
		}
		return $this->escapeString($objectStr);
	}

}
