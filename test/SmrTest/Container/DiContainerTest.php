<?php

namespace SmrTest\Container;

use PHPUnit\Framework\TestCase;
use Smr\Container\DiContainer;

/**
 * Class DiContainerTest
 * @package SmrTest\Container
 * @covers \Smr\Container\DiContainer
 */
class DiContainerTest extends TestCase {
	private const PHPDI_COMPILED_CONTAINER_FILE = "/tmp/CompiledContainer.php";

	protected function setup(): void {
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
}
