<?php declare(strict_types=1);

namespace SmrTest\Container;

use PHPUnit\Framework\TestCase;
use Smr\Container\DiContainer;
use Smr\MySqlProperties;

/**
 * Class DiContainerTest
 * @package SmrTest\Container
 * @covers \Smr\Container\DiContainer
 */
class DiContainerTest extends TestCase {
	private const PHPDI_COMPILED_CONTAINER_FILE = "/tmp/CompiledContainer.php";

	protected function setUp(): void {
		if (file_exists(self::PHPDI_COMPILED_CONTAINER_FILE)) {
			unlink(self::PHPDI_COMPILED_CONTAINER_FILE);
		}
	}

	public function test_compilation_enabled_true() {
		// Given environment variable is turned off
		unset($_ENV["DISABLE_PHPDI_COMPILATION"]);
		// And the container is built
		DiContainer::initializeContainer();
		// Then
		self::assertFileExists(self::PHPDI_COMPILED_CONTAINER_FILE);
	}

	public function test_compilation_enabled_false() {
		// Given environment variable is turned on
		$_ENV["DISABLE_PHPDI_COMPILATION"] = "true";
		// And the container is built
		DiContainer::initializeContainer();
		// Then
		self::assertFileDoesNotExist(self::PHPDI_COMPILED_CONTAINER_FILE);
	}

	public function test_container_get_and_make() {
		// Start with a fresh container
		DiContainer::initializeContainer();

		// The first get should construct a new object
		$class = MySqlProperties::class;
		$instance1 = DiContainer::get($class);
		self::assertInstanceOf($class, $instance1);

		// Getting the same class should now give the exact same object
		$instance2 = DiContainer::get($class);
		self::assertSame($instance1, $instance2);

		// Using make should construct a new object
		$instance3 = DiContainer::make($class);
		self::assertNotSame($instance1, $instance3);
		self::assertEquals($instance1, $instance3);
	}

}
