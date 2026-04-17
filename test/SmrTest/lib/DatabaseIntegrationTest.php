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
		$dbProperties = DiContainer::getClass(DatabaseProperties::class);
		// When using the factory to retrieve a connection instance
		$conn = Database::connectionFactory($dbProperties);
		// The the connection is successful
		self::assertSame($dbProperties->database, $conn->getDatabase());
	}

	public function test__construct_happy_path(): void {
		$db = DiContainer::getClass(Database::class);
		self::assertInstanceOf(Database::class, $db);
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
		$original = DiContainer::getClass(Connection::class);
		// And resetInstance is called
		Database::resetInstance();
		// And Database is usable again after reconnecting
		Database::getInstance()->read('SELECT 1');
		// Then new instance is not the same as the original instance
		self::assertNotSame($original, DiContainer::getClass(Connection::class));
	}

	public function test_resetInstance_closes_connection(): void {
		$conn = DiContainer::getClass(Connection::class);
		$db = Database::getInstance();
		$db->read('SELECT 1'); // initialize the connection
		self::assertTrue($conn->isConnected());

		Database::resetInstance();
		self::assertFalse($conn->isConnected());
	}

	public function test_select(): void {
		$db = Database::getInstance();

		// Test specifying all arguments
		$dbResult = $db->select('level', ['level_id' => 2], ['level_name']);
		$expected = ['level_name' => 'Recruit'];
		self::assertSame($expected, $dbResult->record()->getRow());

		// Test specifying multiple return columns
		$dbResult = $db->select('level', ['level_id' => 2], ['level_name', 'level_id']);
		$expected = [
			'level_name' => 'Recruit',
			'level_id' => 2,
		];
		self::assertSame($expected, $dbResult->record()->getRow());

		// Test specifying multiple criteria
		$dbResult = $db->select('level', ['level_id' => 2, 'requirement' => 25], ['level_name']);
		$expected = ['level_name' => 'Recruit'];
		self::assertSame($expected, $dbResult->record()->getRow());

		// Test with default returnColumns (all columns)
		$dbResult = $db->select('level', ['level_id' => 2]);
		$expected = [
			'level_id' => 2,
			'level_name' => 'Recruit',
			'requirement' => 25,
		];
		self::assertSame($expected, $dbResult->record()->getRow());

		// Test with default criteria (all rows)
		$dbResult = $db->select('level');
		self::assertSame(50, $dbResult->getNumRecords());

		// Test orderBy with default ordering (ASC)
		$dbResult = $db->select(
			'location_type',
			orderBy: ['location_processor', 'location_type_id'],
		);
		$records = iterator_to_array($dbResult->records());
		self::assertSame('bank_personal.php', $records[10]->getNullableString('location_processor'));
		self::assertSame('government.php', $records[18]->getNullableString('location_processor'));
		$expectedOrder = [10 => 701, 11 => 702, 18 => 101];
		foreach ($expectedOrder as $index => $id) {
			self::assertSame($id, $records[$index]->getInt('location_type_id'));
		}

		// Test orderBy with specified ordering
		$dbResult = $db->select(
			'location_type',
			orderBy: ['location_processor', 'location_type_id'],
			order: ['DESC', 'DESC'],
		);
		$records = iterator_to_array($dbResult->records());
		foreach ($expectedOrder as $index => $id) {
			$reverseIndex = count($records) - $index - 1;
			self::assertSame($id, $records[$reverseIndex]->getInt('location_type_id'));
		}

		// Test limit
		$dbResult = $db->select('level', limit: 2);
		self::assertSame(2, $dbResult->getNumRecords());
	}

	public function test_select_orderBy_order_length_error(): void {
		$db = Database::getInstance();
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('order and orderBy must be the same length');
		$db->select('level', orderBy: ['level_id', 'level_name'], order: ['DESC']);
	}

	public function test_count(): void {
		$db = Database::getInstance();
		// Test with criteria
		self::assertSame(1, $db->count('level', ['level_id' => 2]));
		// Test without criteria (all rows)
		self::assertSame(50, $db->count('level', []));
	}

	public function test_getDbBytes(): void {
		$db = Database::getInstance();
		$bytes = $db->getDbBytes();
		// This value will need to change whenever database migrations are
		// added that modify the base table size. If that becomes too onerous,
		// we can do a fuzzier comparison. Until then, this is a useful check
		// that the test database is properly reset between invocations.
		self::assertSame($bytes, 728580);
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
			$db->select('account', limit: 1);
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
			$result = $db->select('good', ['good_id' => 1], ['good_name']);
			self::assertSame(['good_name' => 'Wood'], $result->record()->getRow());
		} finally {
			$db->unlock();
		}
		// After unlock we can access other tables again
		$db->select('account', limit: 1);
	}

	public function test_lockTable_additional_read_locks(): void {
		$db = Database::getInstance();
		$db->lockTable('player', ['good']);
		try {
			// Perform a read query on the read-locked table
			$result = $db->select('good', ['good_id' => 1], ['good_name']);
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
