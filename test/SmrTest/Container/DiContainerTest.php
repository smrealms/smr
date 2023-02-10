<?php declare(strict_types=1);

namespace SmrTest\Container;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Smr\Container\DiContainer;
use Smr\DatabaseProperties;

#[CoversClass(DiContainer::class)]
class DiContainerTest extends TestCase {

	private const PHPDI_COMPILED_CONTAINER_FILE = '/tmp/CompiledContainer.php';

	protected function tearDown(): void {
		if (file_exists(self::PHPDI_COMPILED_CONTAINER_FILE)) {
			unlink(self::PHPDI_COMPILED_CONTAINER_FILE);
		}
	}

	public function test_compilation_enabled_true(): void {
		// Given the container is built with compilation enabled
		DiContainer::initialize(true);
		// Then
		self::assertFileExists(self::PHPDI_COMPILED_CONTAINER_FILE);
	}

	public function test_compilation_enabled_false(): void {
		// Given the container is built with compilation disabled
		DiContainer::initialize(false);
		// Then
		self::assertFileDoesNotExist(self::PHPDI_COMPILED_CONTAINER_FILE);
	}

	public function test_container_get_and_make(): void {
		// Start with a fresh container
		DiContainer::initialize(false);

		// The first get should construct a new object
		$class = DatabaseProperties::class;
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

	public function test_factory_DatabaseName(): void {
		// Start with a fresh container
		DiContainer::initialize(false);
		// Then make sure the 'DatabaseName' is as expected
		$dbName = DiContainer::get('DatabaseName');
		self::assertSame($dbName, 'smr_live_test');
	}

	#[RunInSeparateProcess]
	public function test_initialized(): void {
		// Note that we need to run in a separate process since this is the
		// only way to ensure that the DiContainer is not yet initialized
		// (and static properties cannot be unset).

		// Before the DiContainer is initialized, all entries should report
		// that they are not initialized as well.
		$entry = 'DatabaseName';
		self::assertFalse(DiContainer::initialized($entry));

		// After the DiContainer is initialized, all entries should still
		// report that they are not initialized.
		DiContainer::initialize(false);
		self::assertFalse(DiContainer::initialized($entry));

		// Only once the entry is requested should it be initialized.
		DiContainer::get($entry);
		self::assertTrue(DiContainer::initialized($entry));
	}

}
