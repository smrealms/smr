<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use MySqlDatabase;
use mysqli;
use PHPUnit\Framework\TestCase;
use Smr\Container\DiContainer;
use Smr\MySqlProperties;

/**
 * Class MySqlDatabaseIntegrationTest
 * This is an integration test, but does not need to extend BaseIntegrationTest since we are not writing any data.
 * @covers MySqlDatabase
 * @package SmrTest\lib\DefaultGame
 */
class MySqlDatabaseIntegrationTest extends TestCase {

	protected function setUp(): void {
		// Start each test with a fresh container (and mysqli connection).
		// This ensures the independence of each test.
		DiContainer::initializeContainer();
	}

	public function test_mysql_factory() {
		// Given mysql properties are retrieved from the container
		$mysqlProperties = DiContainer::get(MySqlProperties::class);
		// When using the factory to retrieve a mysqli instance
		$mysqlDatabase = MySqlDatabase::mysqliFactory($mysqlProperties);
		// Then the connection is successful
		self::assertNotNull($mysqlDatabase->server_info);
	}

	public function test__construct_happy_path() {
		$mysqlDatabase = DiContainer::get(MySqlDatabase::class);
		$this->assertNotNull($mysqlDatabase);
	}

	public function test_getInstance_always_returns_new_instance() {
		// Given a MySqlDatabase object
		$original = MySqlDatabase::getInstance();
		// When calling getInstance again
		$second = MySqlDatabase::getInstance();
		self::assertNotSame($second, $original);
	}

	public function test_performing_operations_on_closed_database_throws_error() {
		// Expectations
		$this->expectException(\Error::class);
		$this->expectExceptionMessage('Typed property MySqlDatabase::$dbConn must not be accessed before initialization');
		// Given a mysql database instance
		$mysqlDatabase = MySqlDatabase::getInstance();
		// And disconnect is called
		$mysqlDatabase->close();
		// When calling database methods
		$mysqlDatabase->query("foo query");
	}

	public function test_getInstance_will_perform_reconnect_after_connection_closed() {
		// Given an original mysql connection
		$originalMysql = DiContainer::get(mysqli::class);
		// And a mysql database instance
		$mysqlDatabase = MySqlDatabase::getInstance();
		// And disconnect is called
		$mysqlDatabase->close();
		// And mysql database is retrieved from the container
		$mysqlDatabase = MySqlDatabase::getInstance();
		// When performing a query
		$mysqlDatabase->query("select 1");
		// Then new mysqli instance is not the same as the initial mock
		self::assertNotSame($originalMysql, DiContainer::get(mysqli::class));
	}

	public function test_getInstance_will_not_perform_reconnect_if_connection_not_closed() {
		// Given an original mysql connection
		$originalMysql = DiContainer::get(mysqli::class);
		// And a mysql database instance
		MySqlDatabase::getInstance();
		// And get instance is called again
		MySqlDatabase::getInstance();
		// Then the two mysqli instances are the same
		self::assertSame($originalMysql, DiContainer::get(mysqli::class));
	}

	public function test_escapeMicrotime() {
		$db = MySqlDatabase::getInstance();
		// The current microtime must not throw an exception
		$db->escapeMicrotime(microtime(true));
		// Check that the formatting preserves all digits
		self::assertSame("1608455259123456", $db->escapeMicrotime(1608455259.123456));
	}

	public function test_escapeBoolean() {
		$db = MySqlDatabase::getInstance();
		// Test both boolean values
		self::assertSame("'TRUE'", $db->escapeBoolean(true));
		self::assertSame("'FALSE'", $db->escapeBoolean(false));
	}

	public function test_escapeString() {
		$db = MySqlDatabase::getInstance();
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
		$db = MySqlDatabase::getInstance();
		$this->expectException(\TypeError::class);
		$db->escapeString(null);
	}

	public function test_escapeArray() {
		$db = MySqlDatabase::getInstance();
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
		$db = MySqlDatabase::getInstance();
		$this->expectWarning();
		$this->expectWarningMessage('Array to string conversion');
		$db->escapeArray(['a', ['x', 9, 'y'], 2, 'c'], ':', false);
	}

	public function test_escapeNumber() {
		// No escaping is done of numeric types
		$db = MySqlDatabase::getInstance();
		// Test int
		self::assertSame(42, $db->escapeNumber(42));
		// Test float
		self::assertSame(0.21, $db->escapeNumber(0.21));
		// Test numeric string
		self::assertSame('42', $db->escapeNumber('42'));
	}

	public function test_escapeNumber_nonnumeric_throws() {
		$db = MySqlDatabase::getInstance();
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Not a number');
		$db->escapeNumber('bla');
	}

	public function test_escapeObject() {
		$db = MySqlDatabase::getInstance();
		// Test null
		self::assertSame('NULL', $db->escapeObject(null, false, true));
		// Test empty array
		self::assertSame("'a:0:{}'", $db->escapeObject([]));
		// Test empty string
		self::assertSame('\'s:0:\"\";\'', $db->escapeObject(''));
		// Test zero
		self::assertSame("'i:0;'", $db->escapeObject(0));
	}
}
