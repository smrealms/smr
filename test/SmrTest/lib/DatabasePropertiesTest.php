<?php declare(strict_types=1);

namespace SmrTest\lib;

use Exception;
use PHPUnit\Framework\TestCase;
use Smr\DatabaseProperties;

/**
 * @covers \Smr\DatabaseProperties
 */
class DatabasePropertiesTest extends TestCase {

	private const MYSQL_PASSWORD_FILE = '/tmp/phpunit_dummy_mysql_password';

	private const TEST_ENV = [
		'MYSQL_HOST' => 'host',
		'MYSQL_USER' => 'user',
		'MYSQL_DATABASE' => 'database',
		'MYSQL_PASSWORD_FILE' => self::MYSQL_PASSWORD_FILE,
	];

	/**
	 * @var array<string, string>
	 */
	private array $originalEnv = [];

	/**
	 * @param array<string, string> $env
	 */
	protected function setEnv(array $env): void {
		foreach ($env as $name => $value) {
			$stmt = $name . '=' . $value;
			$result = putenv($stmt);
			if ($result === false) {
				throw new Exception('Failed to putenv: ' . $stmt);
			}
		}
	}

	protected function setUp(): void {
		// Store original environment (not protected by @backupGlobals!)
		foreach (array_keys(self::TEST_ENV) as $name) {
			$result = getenv($name, true);
			if ($result === false) {
				throw new Exception('Failed to getenv: ' . $name);
			}
			$this->originalEnv[$name] = $result;
		}
	}

	protected function tearDown(): void {
		// Remove test file
		if (file_exists(self::MYSQL_PASSWORD_FILE)) {
			unlink(self::MYSQL_PASSWORD_FILE);
		}

		// Restore original environment
		$this->setEnv($this->originalEnv);
	}

	public function test_happy_path(): void {
		// Set custom environment variables
		$this->setEnv(self::TEST_ENV);
		file_put_contents(self::MYSQL_PASSWORD_FILE, 'pass');

		// Then the properties have expected values
		$dbProperties = new DatabaseProperties();
		self::assertEquals('host', $dbProperties->host);
		self::assertEquals('user', $dbProperties->user);
		self::assertEquals('pass', $dbProperties->password);
		self::assertEquals('database', $dbProperties->database);
	}

	/**
	 * @testWith ["MYSQL_HOST"]
	 *           ["MYSQL_USER"]
	 *           ["MYSQL_DATABASE"]
	 *           ["MYSQL_PASSWORD_FILE"]
	 */
	public function test_missing_environment_variable(string $name): void {
		// Unset one of the required environment variables
		putenv($name);
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Database environment variable is missing: ' . $name);
		new DatabaseProperties();
	}

}
