<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use DI\Container;
use Error;
use Exception;
use MySqlDatabase;
use mysqli;
use PHPUnit\Framework\TestCase;
use Smr\Container\DiContainer;

/**
 * Class MySqlDatabaseIntegrationTest
 * This is an integration test, but does not need to extend BaseIntegrationTest since we are not writing any data.
 * @covers MySqlDatabase
 * @package SmrTest\lib\DefaultGame
 */
class MySqlDatabaseIntegrationTest extends TestCase {
	private Container $container;

	protected function setUp(): void {
		DiContainer::initializeContainer();
		$this->container = DiContainer::getContainer();
	}

	public function test_performing_operations_on_closed_database_throws_error() {
		// Expectations
		$this->expectException(Error::class);
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
		$originalMysql = $this->container->get(mysqli::class);
		// And a mysql database instance
		$mysqlDatabase = MySqlDatabase::getInstance();
		// And disconnect is called
		$mysqlDatabase->close();
		// And mysql database is retrieved from the container
		$mysqlDatabase = MySqlDatabase::getInstance();
		// When performing a query
		$mysqlDatabase->query("select 1");
		// Then new mysqli instance is not the same as the initial mock
		self::assertNotSame($originalMysql, $this->container->get(mysqli::class));
	}

	public function test_getInstance_will_not_perform_reconnect_if_connection_not_closed() {
		// Given an original mysql connection
		$originalMysql = $this->container->get(mysqli::class);
		// And a mysql database instance
		MySqlDatabase::getInstance();
		// And get instance is called again
		MySqlDatabase::getInstance();
		// Then the two mysqli instances are the same
		self::assertSame($originalMysql, $this->container->get(mysqli::class));
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
		$this->expectNotice();
		$this->expectNoticeMessage('Array to string conversion');
		$db->escapeArray(['a', ['x', 9, 'y'], 2, 'c'], ':', false);
	}
}
