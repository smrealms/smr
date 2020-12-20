<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use DI\Container;
use MySqlDatabase;
use mysqli;
use PHPUnit\Framework\TestCase;
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
		$mysqlDatabase = $this->container->get(MySqlDatabase::class);
		$this->assertNotNull($mysqlDatabase);
	}

	public function test_getInstance_always_returns_new_instance() {
		// Given a MySqlDatabase object
		$original = MySqlDatabase::getInstance();
		// When calling getInstance again
		$second = MySqlDatabase::getInstance();
		self::assertNotSame($second, $original);
	}
}
