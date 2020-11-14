<?php

namespace SmrTest\lib\DefaultGame;

use Dotenv\Dotenv;
use Dotenv\Exception\ValidationException;
use MySqlProperties;
use PHPUnit\Framework\TestCase;

/**
 * Class MySqlPropertiesTest
 * @package SmrTest\lib\DefaultGame
 * @covers MySqlProperties
 */
class MySqlPropertiesTest extends TestCase {
	private const RESOURCES = ROOT . "test/resources/mysql-config/validation";

	public function test_determine_config_environment_test_path() {
		//# Given the MYSQL_CONFIG_ENVIRONMENT env variable has already been set by PHPUnit
		// When determining the config to load
		$environment = MySqlProperties::determineConfigEnvironmentFile();
		self::assertEquals("test.env", $environment);
	}

	public function test_determine_config_environment_unset_path() {
		//# Given the MYSQL_CONFIG_ENVIRONMENT is not set in the environment
		unset($_ENV["MYSQL_CONFIG_ENVIRONMENT"]);
		// When determining the config to load
		$environment = MySqlProperties::determineConfigEnvironmentFile();
		self::assertEquals(".env", $environment);
	}

	public function test_validate_config_happy_path() {
		// Given required environment file is present
		$config = Dotenv::createArrayBacked(self::RESOURCES, "good.env");
		$config->load();
		// When performing validation of the config
		MySqlProperties::validateConfig($config);
		// Then no errors are present.
		$this->assertTrue(true);
	}

	public function test_validate_config_bad_port_throws_exception() {
		try {
			// Given required environment file is present that contains an invalid port number definition
			$config = Dotenv::createArrayBacked(self::RESOURCES, "bad-port.env");
			$config->load();
			// When performing validation on the config
			MySqlProperties::validateConfig($config);
		} finally {
			// Then a validation exception is expected
			$this->expectException(ValidationException::class);
		}
	}

	public function test_validate_config_missing_config_values_throws_exception() {
		try {
			// Given required environment file is present that is missing required fields
			$config = Dotenv::createArrayBacked(self::RESOURCES, "bad.env");
			$config->load();
			// When performing validation on the config
			MySqlProperties::validateConfig($config);
		} finally {
			// Then a validation exception is expected
			$this->expectException(ValidationException::class);
		}
	}
}
