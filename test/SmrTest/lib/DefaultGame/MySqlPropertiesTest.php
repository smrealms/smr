<?php

namespace SmrTest\lib\DefaultGame;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use Smr\MySqlProperties;

/**
 * Class MySqlPropertiesTest
 * @package SmrTest\lib\DefaultGame
 * @covers MySqlProperties
 */
class MySqlPropertiesTest extends TestCase {
	public function test_validate_config_happy_path() {
		//# Given a Dotenv object
		$dotEnv = $this->createMock(Dotenv::class);
		// And the dotenv config will return the following array when loaded
		$dotEnv
			->expects(self::once())
			->method("load")
			->willReturn([
				"MYSQL_HOST" => "host",
				"MYSQL_USER" => "user",
				"MYSQL_PASSWORD" => "pass",
				"MYSQL_DATABASE" => "database"
			]);
		// And we expect that the dotenv "required" method will be called with the following arguments
		$dotEnv
			->expects(self::exactly(4))
			->method("required")
			->with(
				$this->logicalOr(
					"MYSQL_USER",
					"MYSQL_HOST",
					"MYSQL_PASSWORD",
					"MYSQL_DATABASE"));
		// When constructing the properties class
		$mysqlProperties = new MySqlProperties($dotEnv);
		// Then the properties have expected values
		self::assertEquals("host", $mysqlProperties->getHost());
		self::assertEquals("user", $mysqlProperties->getUser());
		self::assertEquals("pass", $mysqlProperties->getPassword());
		self::assertEquals("database", $mysqlProperties->getDatabaseName());
	}
}
