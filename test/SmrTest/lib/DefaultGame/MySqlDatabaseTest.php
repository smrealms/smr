<?php

namespace SmrTest\lib\DefaultGame;

use DI\Container;
use MySqlDatabase;
use mysqli;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Smr\Container\DiContainer;

class MySqlDatabaseTest extends TestCase {
	private Container $container;
	private mysqli $mysql;

	protected function setUp(): void {
		DiContainer::initializeContainer();
		$this->container = DiContainer::getContainer();
		$this->mysql = $this->createMock(mysqli::class);
		// Replace the factory definition for mysqli object with our mock, so when
		// requesting a mysqli instance, it will always return a mock for this test
		$this->container->set(mysqli::class, $this->mysql);
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
}
