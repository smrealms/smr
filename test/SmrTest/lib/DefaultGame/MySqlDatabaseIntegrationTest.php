<?php

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

	protected function tearDown(): void {
		$mysqli = $this->container->get(mysqli::class);
		try {
			$mysqli->close();
		} catch (Exception $e) {
			print "tearDown() - mysqli connection already closed. $e\n";
		}
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
}
