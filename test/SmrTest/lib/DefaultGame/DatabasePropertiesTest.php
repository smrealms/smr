<?php declare(strict_types=1);

namespace SmrTest;

use Dotenv\Dotenv;
use Dotenv\Validator;
use PHPUnit\Framework\TestCase;
use Smr\DatabaseProperties;

/**
 * @covers \Smr\DatabaseProperties
 */
class DatabasePropertiesTest extends TestCase {

	public function test_validate_config_happy_path(): void {
		// Given a Dotenv object
		$dotEnv = $this->createMock(Dotenv::class);
		$validator = $this->createMock(Validator::class);

		// And the dotenv config will return the following array when loaded
		$dotEnv
			->expects(self::once())
			->method('load')
			->willReturn([
				'MYSQL_HOST' => 'host',
				'MYSQL_USER' => 'user',
				'MYSQL_PASSWORD' => 'pass',
				'MYSQL_DATABASE' => 'database',
			]);

		// And we expect that the dotenv "required" method will be called with the following arguments
		$dotEnv
			->expects(self::once())
			->method('required')
			->with([
				'MYSQL_HOST',
				'MYSQL_USER',
				'MYSQL_PASSWORD',
				'MYSQL_DATABASE',
			])
			->willReturn($validator);

		$validator
			->expects(self::once())
			->method('notEmpty')
			->willReturnSelf();

		// When constructing the properties class
		$dbProperties = new DatabaseProperties($dotEnv);

		// Then the properties have expected values
		self::assertEquals('host', $dbProperties->getHost());
		self::assertEquals('user', $dbProperties->getUser());
		self::assertEquals('pass', $dbProperties->getPassword());
		self::assertEquals('database', $dbProperties->getDatabaseName());
	}

}
