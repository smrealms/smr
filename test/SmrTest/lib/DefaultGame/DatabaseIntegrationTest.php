<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use mysqli;
use PHPUnit\Framework\TestCase;
use Smr\Container\DiContainer;
use Smr\Database;
use Smr\DatabaseProperties;

/**
 * This is an integration test, but does not need to extend BaseIntegrationTest since we are not writing any data.
 * @covers \Smr\Database
 */
class DatabaseIntegrationTest extends TestCase {

	protected function setUp(): void {
		// Start each test with a fresh container (and mysqli connection).
		// This ensures the independence of each test.
		DiContainer::initializeContainer();
	}

	public function test_mysqli_factory() {
		// Given database properties are retrieved from the container
		$dbProperties = DiContainer::get(DatabaseProperties::class);
		// When using the factory to retrieve a mysqli instance
		$mysql = Database::mysqliFactory($dbProperties);
		// Then the connection is successful
		self::assertNotNull($mysql->server_info);
	}

	public function test__construct_happy_path() {
		$db = DiContainer::get(Database::class);
		$this->assertNotNull($db);
	}

	public function test_getInstance_always_returns_new_instance() {
		// Given a Database object
		$original = Database::getInstance();
		// When calling getInstance again
		$second = Database::getInstance();
		self::assertNotSame($original, $second);
	}

	public function test_performing_operations_on_closed_database_throws_error() {
		// Given a Database instance
		$db = Database::getInstance();
		// And disconnect is called
		$db->close();
		// When calling database methods
		$this->expectException(\Error::class);
		$this->expectExceptionMessage('Typed property Smr\Database::$dbConn must not be accessed before initialization');
		$db->query("foo query");
	}

	public function test_closing_database_returns_boolean() {
		$db = Database::getInstance();
		// Returns true when closing an open database connection
		self::assertTrue($db->close());
		// Returns false if the database has already been closed
		self::assertFalse($db->close());
	}

	public function test_getInstance_will_perform_reconnect_after_connection_closed() {
		// Given an original mysql connection
		$originalMysql = DiContainer::get(mysqli::class);
		// And a Database instance
		$db = Database::getInstance();
		// And disconnect is called
		$db->close();
		// And Database is retrieved from the container
		$db = Database::getInstance();
		// When performing a query
		$db ->query("select 1");
		// Then new mysqli instance is not the same as the initial mock
		self::assertNotSame($originalMysql, DiContainer::get(mysqli::class));
	}

	public function test_getInstance_will_not_perform_reconnect_if_connection_not_closed() {
		// Given an original mysql connection
		$originalMysql = DiContainer::get(mysqli::class);
		// And a Database instance
		Database::getInstance();
		// And get instance is called again
		Database::getInstance();
		// Then the two mysqli instances are the same
		self::assertSame($originalMysql, DiContainer::get(mysqli::class));
	}

	public function test_escapeMicrotime() {
		$db = Database::getInstance();
		// The current microtime must not throw an exception
		$db->escapeMicrotime(microtime(true));
		// Check that the formatting preserves all digits
		self::assertSame("1608455259123456", $db->escapeMicrotime(1608455259.123456));
	}

	public function test_escapeBoolean() {
		$db = Database::getInstance();
		// Test both boolean values
		self::assertSame("'TRUE'", $db->escapeBoolean(true));
		self::assertSame("'FALSE'", $db->escapeBoolean(false));
	}

	public function test_escapeString() {
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

	public function test_escapeString_null_throws() {
		$db = Database::getInstance();
		$this->expectException(\TypeError::class);
		$db->escapeString(null);
	}

	public function test_escapeArray() {
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

	public function test_escapeArray_nested_array_throws() {
		// Warning: It is dangerous to use nested arrays with escapeIndividually=false
		$db = Database::getInstance();
		$this->expectWarning();
		$this->expectWarningMessage('Array to string conversion');
		$db->escapeArray(['a', ['x', 9, 'y'], 2, 'c'], ':', false);
	}

	public function test_escapeNumber() {
		// No escaping is done of numeric types
		$db = Database::getInstance();
		// Test int
		self::assertSame(42, $db->escapeNumber(42));
		// Test float
		self::assertSame(0.21, $db->escapeNumber(0.21));
		// Test numeric string
		self::assertSame('42', $db->escapeNumber('42'));
	}

	public function test_escapeNumber_nonnumeric_throws() {
		$db = Database::getInstance();
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Not a number');
		$db->escapeNumber('bla');
	}

	public function test_escapeObject() {
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

	public function test_lockTable_throws_if_read_other_table() {
		$db = Database::getInstance();
		$db->lockTable('player');
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage("Table 'account' was not locked with LOCK TABLES");
		try {
			$db->query('SELECT 1 FROM account LIMIT 1');
		} catch (\RuntimeException $err) {
			// Avoid leaving database in a locked state
			$db->unlock();
			throw $err;
		}
	}

	public function test_lockTable_allows_read() {
		$db = Database::getInstance();
		$db->lockTable('good');

		// Perform a query on the locked table
		$db->query('SELECT good_name FROM good WHERE good_id = 1');
		$db->requireRecord();
		self::assertSame(['good_name' => 'Wood'], $db->getRow());

		// After unlock we can access other tables again
		$db->unlock();
		$db->query('SELECT 1 FROM account LIMIT 1');
	}

	public function test_requireRecord() {
		$db = Database::getInstance();
		// Create a query that returns one record
		$db->query('SELECT 1');
		$db->requireRecord();
		self::assertSame([1 => '1'], $db->getRow());
	}

	public function test_requireRecord_too_many_rows() {
		$db = Database::getInstance();
		// Create a query that returns two rows
		$db->query('SELECT 1 UNION SELECT 2');
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('One record required, but found 2');
		$db->requireRecord();
	}

	public function test_nextRecord_no_resource() {
		$db = Database::getInstance();
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('No resource to get record from.');
		// Call nextRecord before any query is made
		$db->nextRecord();
	}

	public function test_nextRecord_no_result() {
		$db = Database::getInstance();
		// Construct a query that has an empty result set
		$db->query('SELECT 1 FROM good WHERE good_id = 0');
		self::assertFalse($db->nextRecord());
	}

	public function test_hasField() {
		$db = Database::getInstance();
		// Construct a query that has the field 'val', but not 'bla'
		$db->query('SELECT 1 AS val');
		$db->requireRecord();
		self::assertTrue($db->hasField('val'));
		self::assertFalse($db->hasField('bla'));
	}

	public function test_inversion_of_escape_and_get() {
		$db = Database::getInstance();
		// [value, escape function, getter, comparator, extra args]
		$params = [
			[true, 'escapeBoolean', 'getBoolean', 'assertSame', []],
			[false, 'escapeBoolean', 'getBoolean', 'assertSame', []],
			[3, 'escapeNumber', 'getInt', 'assertSame', []],
			[3.14, 'escapeNumber', 'getFloat', 'assertSame', []],
			['hello', 'escapeString', 'getField', 'assertSame', []],
			// Test nullable objects
			[null, 'escapeObject', 'getObject', 'assertSame', [false, true]],
			// Test object with compression
			[[1, 2, 3], 'escapeObject', 'getObject', 'assertSame', [true]],
			// Test object without compression
			[[1, 2, 3], 'escapeObject', 'getObject', 'assertSame', []],
			// Microtime takes a float and returns a string because of DateTime::createFromFormat
			[microtime(true), 'escapeMicrotime', 'getMicrotime', 'assertEquals', []],
		];
		foreach ($params as list($value, $escaper, $getter, $cmp, $args)) {
			$db->query('SELECT ' . $db->$escaper($value, ...$args) . ' AS val');
			$db->requireRecord();
			self::$cmp($value, $db->$getter('val', ...$args));
		}
	}

	public function test_getBoolean_with_non_boolean_field() {
		$db = Database::getInstance();
		$db->query('SELECT \'bla\'');
		$db->requireRecord();
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Field is not a boolean: bla');
		$db->getBoolean('bla');
	}

}
