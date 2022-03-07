<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

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

	public function test_performing_operations_on_closed_database_throws_error(): void {
		// Given a Database instance
		$db = Database::getInstance();
		// And disconnect is called
		$db->close();
		// When calling database methods
		$this->expectException(Error::class);
		$this->expectExceptionMessage('Typed property Smr\Database::$dbConn must not be accessed before initialization');
		$db->read('foo query');
	}

	public function test_getDbBytes(): void {
		$db = Database::getInstance();
		$bytes = $db->getDbBytes();
		// This value will need to change whenever database migrations are
		// added that modify the base table size. If that becomes too onerous,
		// we can do a fuzzier comparison. Until then, this is a useful check
		// that the test database is properly reset between invocations.
		self::assertSame($bytes, 729944);
	}

	public function test_closing_database_returns_boolean(): void {
		$db = Database::getInstance();
		// Returns true when closing an open database connection
		self::assertTrue($db->close());
		// Returns false if the database has already been closed
		self::assertFalse($db->close());
	}

	public function test_reconnect_after_connection_closed(): void {
		// Given an original mysql connection
		$originalMysql = DiContainer::get(mysqli::class);
		// And a Database instance
		$db = Database::getInstance();
		// And disconnect is called
		$db->close();
		// And Database is usable again after reconnecting
		$db->reconnect();
		$db->read('SELECT 1');
		// Then new mysqli instance is not the same as the initial mock
		self::assertNotSame($originalMysql, DiContainer::get(mysqli::class));
	}

	public function test_reconnect_when_connection_not_closed(): void {
		// Given an original mysql connection
		$originalMysql = DiContainer::get(mysqli::class);
		// And a Database instance
		$db = Database::getInstance();
		// And reconnect is called before closing the connection
		$db->reconnect();
		// Then the two mysqli instances are the same
		self::assertSame($originalMysql, DiContainer::get(mysqli::class));
	}

	public function test_escapeMicrotime(): void {
		$db = Database::getInstance();
		// The current microtime must not throw an exception
		$db->escapeMicrotime(microtime(true));
		// Check that the formatting preserves all digits
		self::assertSame('1608455259123456', $db->escapeMicrotime(1608455259.123456));
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
		// Test a different implodeString
		self::assertSame("'a':2:'c'", $db->escapeArray(['a', 2, 'c'], ':'));
		// Test escapeIndividually=false
		self::assertSame("'a,2,c'", $db->escapeArray(['a', 2, 'c'], ',', false));
		// Test nested arrays
		// Warning: The array is flattened, which may be unexpected!
		self::assertSame("'a','x',9,2", $db->escapeArray(['a', ['x', 9], 2], ',', true));
	}

	public function test_escapeArray_nested_array_throws(): void {
		// Warning: It is dangerous to use nested arrays with escapeIndividually=false
		$db = Database::getInstance();
		$this->expectWarning();
		$this->expectWarningMessage('Array to string conversion');
		$db->escapeArray(['a', ['x', 9, 'y'], 2, 'c'], ':', false);
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
			['hello', 'escapeString', 'getField', 'assertSame', []],
			// Test nullable objects
			[null, 'escapeString', 'getField', 'assertSame', [true]],
			[null, 'escapeObject', 'getObject', 'assertSame', [false, true]],
			// Test object with compression
			[[1, 2, 3], 'escapeObject', 'getObject', 'assertSame', [true]],
			// Test object without compression
			[[1, 2, 3], 'escapeObject', 'getObject', 'assertSame', []],
			// Microtime takes a float and returns a string because of DateTime::createFromFormat
			[microtime(true), 'escapeMicrotime', 'getMicrotime', 'assertEquals', []],
		];
		foreach ($params as [$value, $escaper, $getter, $cmp, $args]) {
			$result = $db->read('SELECT ' . $db->$escaper($value, ...$args) . ' AS val');
			self::$cmp($value, $result->record()->$getter('val', ...$args));
		}
	}

	public function test_insert(): void {
		$db = Database::getInstance();

		// Zero insert ID when table does not have an auto-increment column
		self::assertSame(0, $db->insert('debug', []));

		// Non-zero insert ID when table has an auto-increment column
		for ($i = 1; $i <= 3; $i++) {
			self::assertSame($i, $db->insert('newsletter', []));
		}

		// Non-empty fields are successfully recovered
		$logID = $db->insert('newsletter', [
			'newsletter_text' => $db->escapeString('foo'),
			'newsletter_html' => $db->escapeString('bar'),
		]);
		$result = $db->read('SELECT * FROM newsletter WHERE newsletter_id = ' . $logID);
		$record = $result->record();
		self::assertSame('foo', $record->getString('newsletter_text'));
		self::assertSame('bar', $record->getString('newsletter_html'));
	}

}
