<?php declare(strict_types=1);

namespace SmrTest\lib;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\DriverException;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Smr\Container\DiContainer;
use Smr\Database;
use Smr\DatabaseProperties;

/**
 * This is an integration test, but does not need to extend BaseIntegrationTest since we are not writing any data.
 */
#[CoversClass(Database::class)]
class DatabaseIntegrationTest extends TestCase {

	protected function setUp(): void {
		// Start each test with a fresh container (and connection).
		// This ensures the independence of each test.
		DiContainer::initialize(false);
	}

	public function test_connectionFactory(): void {
		// Given database properties are retrieved from the container
		$dbProperties = DiContainer::get(DatabaseProperties::class);
		// When using the factory to retrieve a connection instance
		$conn = Database::connectionFactory($dbProperties);
		// The the connection is successful
		self::assertSame($dbProperties->database, $conn->getDatabase());
	}

	public function test__construct_happy_path(): void {
		$db = DiContainer::get(Database::class);
		self::assertNotNull($db);
	}

	public function test_getInstance_always_returns_same_instance(): void {
		// Given a Database object
		$original = Database::getInstance();
		// When calling getInstance again
		$second = Database::getInstance();
		self::assertSame($original, $second);
	}

	public function test_resetInstance_returns_new_instance(): void {
		// Given an original connection instance
		$original = DiContainer::get(Connection::class);
		// And resetInstance is called
		Database::resetInstance();
		// And Database is usable again after reconnecting
		Database::getInstance()->read('SELECT 1');
		// Then new instance is not the same as the original instance
		self::assertNotSame($original, DiContainer::get(Connection::class));
	}

	public function test_resetInstance_closes_connection(): void {
		$conn = DiContainer::get(Connection::class);
		$db = Database::getInstance();
		$db->read('SELECT 1'); // initialize the connection
		self::assertTrue($conn->isConnected());

		Database::resetInstance();
		self::assertFalse($conn->isConnected());
	}

	public function test_getDbBytes(): void {
		$db = Database::getInstance();
		$bytes = $db->getDbBytes();
		// This value will need to change whenever database migrations are
		// added that modify the base table size. If that becomes too onerous,
		// we can do a fuzzier comparison. Until then, this is a useful check
		// that the test database is properly reset between invocations.
		self::assertSame($bytes, 712156);
	}

	public function test_escapeBoolean(): void {
		$db = Database::getInstance();
		// Test both boolean values
		self::assertSame('TRUE', $db->escapeBoolean(true));
		self::assertSame('FALSE', $db->escapeBoolean(false));
	}

	public function test_escapeString(): void {
		$db = Database::getInstance();
		// Test the empty string
		self::assertSame('', $db->escapeString(''));
		// Test a normal string
		self::assertSame('bla', $db->escapeString('bla'));
	}

	public function test_escapeNullableString(): void {
		$db = Database::getInstance();
		// Test the empty string
		self::assertNull($db->escapeNullableString(''));
		// Test null
		self::assertNull($db->escapeNullableString(null));
		// Test a normal string
		self::assertSame('bla', $db->escapeString('bla'));
	}

	public function test_escapeArray(): void {
		$db = Database::getInstance();
		// Test a string array
		self::assertSame(['a', 'b', 'c'], $db->escapeArray(['a', 'b', 'c']));
		// Test an int array
		self::assertSame([1, 2, 3], $db->escapeArray([1, 2, 3]));
	}

	public function test_escapeNumber(): void {
		// No escaping is done of numeric types
		$db = Database::getInstance();
		// Test int
		self::assertSame(42, $db->escapeNumber(42));
		// Test float
		self::assertSame(0.21, $db->escapeNumber(0.21));
	}

	public function test_escapeObject(): void {
		$db = Database::getInstance();
		// Test empty array
		self::assertSame('a:0:{}', $db->escapeObject([]));
		// Test empty string
		self::assertSame('s:0:"";', $db->escapeObject(''));
	}

	public function test_escapeNullableObject(): void {
		$db = Database::getInstance();
		// Test null
		self::assertNull($db->escapeNullableObject(null));
	}

	public function test_write_throws_on_wrong_query_type(): void {
		// Queries that return a result should not use the 'write' method
		$db = Database::getInstance();
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Wrong query type');
		$db->write('SELECT 1');
	}

	public function test_lockTable_throws_if_read_other_table(): void {
		$db = Database::getInstance();
		$db->lockTable('player');
		$this->expectException(DriverException::class);
		$this->expectExceptionMessage("Table 'account' was not locked with LOCK TABLES");
		try {
			$db->read('SELECT 1 FROM account LIMIT 1');
		} finally {
			// Avoid leaving database in a locked state
			$db->unlock();
		}
	}

	public function test_lockTable_allows_read(): void {
		$db = Database::getInstance();
		$db->lockTable('good');
		try {
			// Perform a query on the locked table
			$result = $db->read('SELECT good_name FROM good WHERE good_id = 1');
			self::assertSame(['good_name' => 'Wood'], $result->record()->getRow());
		} finally {
			$db->unlock();
		}
		// After unlock we can access other tables again
		$db->read('SELECT 1 FROM account LIMIT 1');
	}

	public function test_lockTable_additional_read_locks(): void {
		$db = Database::getInstance();
		$db->lockTable('player', ['good']);
		try {
			// Perform a read query on the read-locked table
			$result = $db->read('SELECT good_name FROM good WHERE good_id = 1');
			self::assertSame(['good_name' => 'Wood'], $result->record()->getRow());
		} finally {
			$db->unlock();
		}
	}

	/**
	 * @param array<mixed> $args Extra arguments to pass to the escaper/getter.
	 */
	#[TestWith([true, 'escapeBoolean', 'getBoolean'])]
	#[TestWith([false, 'escapeBoolean', 'getBoolean'])]
	#[TestWith([3, 'escapeNumber', 'getInt'])]
	#[TestWith([3.14, 'escapeNumber', 'getFloat'])]
	#[TestWith(['hello', 'escapeString', 'getString'])]
	#[TestWith(['hello', 'escapeNullableString', 'getNullableString'])]
	// Test nullable objects
	#[TestWith([null, 'escapeNullableString', 'getNullableString'])]
	#[TestWith([null, 'escapeNullableObject', 'getNullableObject'])]
	// Test object with compression
	#[TestWith([[1, 2, 3], 'escapeObject', 'getObject', [true]])]
	#[TestWith([[1, 2, 3], 'escapeNullableObject', 'getNullableObject', [true]])]
	// Test object without compression
	#[TestWith([[1, 2, 3], 'escapeObject', 'getObject'])]
	#[TestWith([[1, 2, 3], 'escapeNullableObject', 'getNullableObject'])]
	public function test_inversion_of_escape_and_get(mixed $value, string $escaper, string $getter, array $args = []): void {
		$db = Database::getInstance();
		$result = $db->read('SELECT ? AS val', [$db->$escaper($value, ...$args)]);
		self::assertSame($value, $result->record()->$getter('val', ...$args));
	}

}
