<?php declare(strict_types=1);

namespace Smr;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\ParameterType;
use Exception;
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
	 * Closes the underlying connection and removes it, along with the Database
	 * instance that wraps it, from the DI container. A new Database instance
	 * will be made, along with a fresh database connection, the next time that
	 * Database::getInstance() is called.
	 *
	 * This function is safe to use even if the DI container or the Database
	 * instances have not been initialized yet.
	 */
	public static function resetInstance(): void {
		if (DiContainer::initialized(Connection::class)) {
			$container = DiContainer::getContainer();
			if (DiContainer::initialized(self::class)) {
				self::getInstance()->dbConn->close();
				$container->reset(self::class);
			}
			$container->reset(Connection::class);
		}
	}

	/**
	 * Used by the DI container to construct the underlying connection object.
	 * Not intended to be used outside the DI context.
	 */
	public static function connectionFactory(DatabaseProperties $dbProperties): Connection {
		return DriverManager::getConnection([
			'dbname' => $dbProperties->database,
			'user' => $dbProperties->user,
			'password' => $dbProperties->password,
			'host' => $dbProperties->host,
			'driver' => 'pdo_mysql',
			'charset' => 'utf8',
		]);
	}

	/**
	 * Not intended to be constructed by hand. If you need an instance of Database,
	 * use Database::getInstance();
	 */
	public function __construct(
		private readonly Connection $dbConn,
		private readonly string $dbName,
	) {}

	/**
	 * This method will switch the connection to the specified database.
	 * Useful for switching back and forth between historical, and live databases.
	 *
	 * @param string $databaseName The name of the database to switch to
	 */
	public function switchDatabases(string $databaseName): void {
		$this->write('USE ' . $databaseName);
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
	 *
	 * @param array<mixed> $params
	 * @return int Number of affected rows
	 */
	public function write(string $query, array $params = []): int {
		if (str_starts_with($query, 'SELECT')) {
			throw new Exception('Wrong query type');
		}
		$types = self::getParamTypes($params);
		return (int)$this->dbConn->executeStatement($query, $params, $types);
	}

	/**
	 * Perform a read-only query on the database.
	 * Used for SELECT queries, for example.
	 *
	 * @param array<mixed> $params
	 */
	public function read(string $query, array $params = []): DatabaseResult {
		$types = self::getParamTypes($params);
		$result = $this->dbConn->executeQuery($query, $params, $types);
		return new DatabaseResult($result);
	}

	/**
	 * Determine Doctrine\DBAL types automatically based on the passed in type.
	 *
	 * @param array<mixed> $params
	 * @return array<string, int>
	 */
	private static function getParamTypes(array $params): array {
		// Default is ParameterType::STRING for any unspecified fields
		$types = [];
		foreach ($params as $field => $value) {
			// Handle ints explicitly for cases where a string is not valid,
			// such as in a LIMIT condition, since int types won't get quoted.
			if (is_int($value)) {
				$types[$field] = ParameterType::INTEGER;
			} elseif (is_array($value)) {
				if (count($value) > 0 && is_int($value[array_key_first($value)])) {
					$types[$field] = ArrayParameterType::INTEGER;
				} else {
					$types[$field] = ArrayParameterType::STRING;
				}
			}
		}
		return $types;
	}

	/**
	 * INSERT a row into $table.
	 *
	 * @param string $table
	 * @param array<string, mixed> $fields
	 * @return int Insert ID of auto-incrementing column, if applicable
	 */
	public function insert(string $table, array $fields): int {
		$this->dbConn->insert($table, $fields);
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
			. ') VALUES (' . implode(', ', array_fill(0, count($fields), '?')) . ')';
		$this->write($query, array_values($fields));
		return $this->getInsertID();
	}

	public function lockTable(string $table): void {
		$this->write('LOCK TABLES ' . $table . ' WRITE');
	}

	public function unlock(): void {
		$this->write('UNLOCK TABLES');
	}

	public function getInsertID(): int {
		$insertID = $this->dbConn->lastInsertId();
		if ($insertID === false) {
			throw new Exception('Failed to get the last insert ID');
		}
		return (int)$insertID;
	}

	public function escapeNullableString(?string $string): ?string {
		if ($string === '') {
			return null;
		}
		return $string;
	}

	public function escapeString(string $string): string {
		return $string;
	}

	/**
	 * @template T of array<int>|array<string> $array
	 * @param T $array
	 * @return T
	 */
	public function escapeArray(array $array): array {
		return $array;
	}

	/**
	 * @template T of int|float
	 * @param T $num
	 * @return T
	 */
	public function escapeNumber(int|float $num): int|float {
		return $num;
	}

	public function escapeBoolean(bool $bool): string {
		// We store booleans as an enum
		return $bool ? 'TRUE' : 'FALSE';
	}

	/**
	 * @param object|array<mixed>|string|null $object
	 */
	public function escapeNullableObject(object|array|string|null $object, bool $compress = false): ?string {
		if ($object === null) {
			return null;
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
		return $objectStr;
	}

}
