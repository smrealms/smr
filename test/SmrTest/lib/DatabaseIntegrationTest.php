<?php declare(strict_types=1);

namespace SmrTest\lib;

use Error;
use mysqli;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Smr\Container\DiContainer;
use Smr\Database;
use Smr\DatabaseProperties;
use TypeError;

/**
 * This is an integration test, but does not need to extend BaseIntegrationTest since we are not writing any data.
 *
 * @covers \Smr\Database
 */
class DatabaseIntegrationTest extends TestCase {

	protected function setUp(): void {
		// Start each test with a fresh container (and mysqli connection).
		// This ensures the independence of each test.
		DiContainer::initialize(false);
	}

	public function test_mysqli_factory(): void {
		// Given database properties are retrieved from the container
		$dbProperties = DiContainer::get(DatabaseProperties::class);
		// When using the factory to retrieve a mysqli instance
		$mysql = Database::mysqliFactory($dbProperties);
		// Then the connection is successful
		self::assertNotNull($mysql->server_info);
	}

	public function test__construct_happy_path(): void {
		$db = DiContainer::get(Database::class);
		$this->assertNotNull($db);
	}

	public function test_getInstance_always_returns_same_instance(): void {
		// Given a Database object
		$original = Database::getInstance();
		// When calling getInstance again
		$second = Database::getInstance();
		self::assertSame($original, $second);
	}

	public function test_resetInstance_returns_new_instance(): void {
		// Given an original mysql instance
		$originalMysql = DiContainer::get(mysqli::class);
		// And resetInstance is called
		Database::resetInstance();
		// And Database is usable again after reconnecting
		Database::getInstance()->read('SELECT 1');
		// Then new mysqli instance is not the same as the original instance
		self::assertNotSame($originalMysql, DiContainer::get(mysqli::class));
	}

	public function test_resetInstance_closes_connection(): void {
		$db = Database::getInstance();
		Database::resetInstance();
		$this->expectException(Error::class);
		$this->expectExceptionMessage('mysqli object is already closed');
		$db->read('SELECT 1');
	}

	public function test_getDbBytes(): void {
		$db = Database::getInstance();
		$bytes = $db->getDbBytes();
		// This value will need to change whenever database migrations are
		// added that modify the base table size. If that becomes too onerous,
		// we can do a fuzzier comparison. Until then, this is a useful check
		// that the test database is properly reset between invocations.
		self::assertSame($bytes, 730840);
	}

	public function test_escapeBoolean(): void {
		$db = Database::getInstance();
		// Test both boolean values
		self::assertSame("'TRUE'", $db->escapeBoolean(true));
		self::assertSame("'FALSE'", $db->escapeBoolean(false));
	}

	public function test_escapeString(): void {
		$db = Database::getInstance();
		// Test the empty string
		self::assertSame("''", $db->escapeString(''));
		self::assertSame('NULL', $db->escapeString('', true)); // nullable
		// Test null
		self::assertSame('NULL', $db->escapeString(null, true)); // nullable
		// Test a normal string
		self::assertSame("'bla'", $db->escapeString('bla'));
		self::assertSame("'bla'", $db->escapeString('bla', true)); // nullable
	}

	public function test_escapeString_null_throws(): void {
		$db = Database::getInstance();
		$this->expectException(TypeError::class);
		$db->escapeString(null);
	}

	public function test_escapeArray(): void {
		$db = Database::getInstance();
		// Test a mixed array
		self::assertSame("'a',2,'c'", $db->escapeArray(['a', 2, 'c']));
		// Test nested arrays
		// Warning: The array is flattened, which may be unexpected!
		self::assertSame("'a','x',9,2", $db->escapeArray(['a', ['x', 9], 2]));
	}

	public function test_escapeNumber(): void {
		// No escaping is done of numeric types
		$db = Database::getInstance();
		// Test int
		self::assertSame(42, $db->escapeNumber(42));
		// Test float
		self::assertSame(0.21, $db->escapeNumber(0.21));
		// Test numeric string
		self::assertSame('42', $db->escapeNumber('42'));
	}

	public function test_escapeNumber_nonnumeric_throws(): void {
		$db = Database::getInstance();
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Not a number');
		$db->escapeNumber('bla');
	}

	public function test_escapeObject(): void {
		$db = Database::getInstance();
		// Test null
		self::assertSame('NULL', $db->escapeObject(null, false, true));
		// Test empty array
		self::assertSame("'a:0:{}'", $db->escapeObject([]));
		// Test empty string
		self::assertSame('\'s:0:\"\";\'', $db->escapeObject(''));
		// Test zero
		self::assertSame("'i:0;'", $db->escapeObject(0));
	}

	public function test_write_throws_on_wrong_query_type(): void {
		// Queries that return a result should not use the 'write' method
		$db = Database::getInstance();
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Wrong query type');
		$db->write('SELECT 1');
	}

	public function test_lockTable_throws_if_read_other_table(): void {
		$db = Database::getInstance();
		$db->lockTable('player');
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage("Table 'account' was not locked with LOCK TABLES");
		try {
			$db->read('SELECT 1 FROM account LIMIT 1');
		} catch (\RuntimeException $err) {
			// Avoid leaving database in a locked state
			$db->unlock();
			throw $err;
		}
	}

	public function test_lockTable_allows_read(): void {
		$db = Database::getInstance();
		$db->lockTable('good');

		// Perform a query on the locked table
		$result = $db->read('SELECT good_name FROM good WHERE good_id = 1');
		self::assertSame(['good_name' => 'Wood'], $result->record()->getRow());

		// After unlock we can access other tables again
		$db->unlock();
		$db->read('SELECT 1 FROM account LIMIT 1');
	}

	public function test_inversion_of_escape_and_get(): void {
		$db = Database::getInstance();
		// [value, escape function, getter, comparator, extra args]
		$params = [
			[true, 'escapeBoolean', 'getBoolean', 'assertSame', []],
			[false, 'escapeBoolean', 'getBoolean', 'assertSame', []],
			[3, 'escapeNumber', 'getInt', 'assertSame', []],
			[3.14, 'escapeNumber', 'getFloat', 'assertSame', []],
			['hello', 'escapeString', 'getString', 'assertSame', []],
			['hello', 'escapeString', 'getNullableString', 'assertSame', []],
			// Test nullable objects
			[null, 'escapeString', 'getNullableString', 'assertSame', [true]],
			[null, 'escapeObject', 'getObject', 'assertSame', [false, true]],
			// Test object with compression
			[[1, 2, 3], 'escapeObject', 'getObject', 'assertSame', [true]],
			// Test object without compression
			[[1, 2, 3], 'escapeObject', 'getObject', 'assertSame', []],
		];
		foreach ($params as [$value, $escaper, $getter, $cmp, $args]) {
			$result = $db->read('SELECT ' . $db->$escaper($value, ...$args) . ' AS val');
			self::$cmp($value, $result->record()->$getter('val', ...$args));
		}
	}

}
