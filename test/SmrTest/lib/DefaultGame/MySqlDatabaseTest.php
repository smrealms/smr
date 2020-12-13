<?php

namespace SmrTest\lib\DefaultGame;

use DI\Container;
use Error;
use MySqlDatabase;
use mysqli;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Smr\Container\DiContainer;
use Smr\MySqlProperties;

class MySqlDatabaseTest extends TestCase {
	private Container $container;
	private $mysql;

	protected function setUp(): void {
		DiContainer::initializeContainer();
		$this->container = DiContainer::getContainer();
		$this->mysql = $this->createMock(mysqli::class);
		// Replace the factory definition for mysqli object with our mock, so when
		// requesting a mysqli instance, it will always return a mock for this test
		$this->container->set(mysqli::class, $this->mysql);
	}

	public function test_mysql_factory() {
		// Given mysql properties are retrieved from the container
		$mysqlProperties = $this->container->get(MySqlProperties::class);
		// When using the factory to retrieve a mysqli instance
		$mysqlDatabase = MySqlDatabase::mysqliFactory($mysqlProperties);
		// Then the connection is successful
		self::assertNotNull($mysqlDatabase->server_info);
	}

	public function test__construct_happy_path() {
		$this->mysql
			->expects(self::once())
			->method("character_set_name")
			->willReturn("utf8");
		$mysqlDatabase = $this->container->get(MySqlDatabase::class);
		$this->assertNotNull($mysqlDatabase);
	}

	public function test__construct_invalid_character_set_throws_exception() {
		$this->expectException(RuntimeException::class);
		$this->mysql
			->expects(self::once())
			->method("character_set_name")
			->willReturn("invalid character set");
		// MySqlDatabase::getInstance() is also a valid way to retrieve the managed class instance.
		MySqlDatabase::getInstance();
	}

	public function test_performing_operations_on_closed_database_throws_error() {
		// Expectations
		$this->expectException(Error::class);
		$this->expectExceptionMessage('Typed property MySqlDatabase::$dbConn must not be accessed before initialization');
		// Given mysqli return valid charset
		$this->mysql
			->expects(self::once())
			->method("character_set_name")
			->willReturn("utf8");
		// And a mysql database instance
		$mysqlDatabase = MySqlDatabase::getInstance();
		// And disconnect is called
		$mysqlDatabase->close();
		// When calling database methods
		$mysqlDatabase->query("foo query");
	}

	public function test_getInstance_will_perform_reconnect_after_connection_closed() {
		// Given mysqli return valid charset
		$this->mysql
			->expects(self::once())
			->method("character_set_name")
			->willReturn("utf8");
		// And a mysql database instance
		$mysqlDatabase = MySqlDatabase::getInstance();
		// And disconnect is called
		$mysqlDatabase->close();
		// And mysql database is retrieved from the container
		$mysqlDatabase = MySqlDatabase::getInstance();
		// When performing a query
		$mysqlDatabase->query("select 1");
		// Then new mysqli instance is not the same as the initial mock
		self::assertNotSame($this->mysql, $this->container->get(mysqli::class));
	}

	public function test_getInstance_will_not_perform_reconnect_if_connection_not_closed() {
		// Given mysqli return valid charset
		$this->mysql
			->expects(self::once())
			->method("character_set_name")
			->willReturn("utf8");
		// And a mysql database instance
		MySqlDatabase::getInstance();
		// And get instance is called again
		MySqlDatabase::getInstance();
		// Then the two mysqli instances are the same
		self::assertSame($this->mysql, $this->container->get(mysqli::class));
	}

	public function test_getInstance_will_return_reconnected_instance_when_called_multiple_times_after_closing_connection() {
		// Given mysqli return valid charset
		$this->mysql
			->expects(self::once())
			->method("character_set_name")
			->willReturn("utf8");
		// And a mysql database instance
		$mysqlDatabase = MySqlDatabase::getInstance();
		// And disconnect is called
		$mysqlDatabase->close();
		// And mysql database is retrieved from the container
		$mysqlDatabase2 = MySqlDatabase::getInstance();
		$mysqlDatabase3 = MySqlDatabase::getInstance();
		// Then the two new container retrievals are the same
		self::assertSame($mysqlDatabase2, $mysqlDatabase3);
		// And the original mysql database object is not the same reference as the others
		self::assertNotSame($mysqlDatabase, $mysqlDatabase2);
	}
}
