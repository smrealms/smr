<?php declare(strict_types=1);

namespace SmrTest\Container;

use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use Smr\Container\ResettableContainer;

/**
 * @covers \Smr\Container\ResettableContainerTrait
 */
class ResettableContainerTraitTest extends TestCase {

	private ContainerBuilder $builder;

	protected function setUp(): void {
		$this->builder = new ContainerBuilder(ResettableContainer::class);
	}

	public function test_not_initialized_by_definition(): void {
		$this->builder->addDefinitions([
			'foo' => 'bar',
		]);

		// Unlike has(), entries are not initialized by definitions
		self::assertFalse($this->builder->build()->initialized('foo'));
	}

	public function test_initialized_by_definition_after_get(): void {
		$this->builder->addDefinitions([
			'foo' => 'bar',
		]);
		$container = $this->builder->build();
		$container->get('foo');

		// Only once we get the entry for the first time is it initialized
		self::assertTrue($container->initialized('foo'));
	}

	public function test_initialized_when_set_directly(): void {
		$container = $this->builder->build();
		$container->set('foo', 'bar');

		// The entry is also initialized if set directly
		self::assertTrue($container->initialized('foo'));
	}

	public function test_not_initialized_when_unknown(): void {
		// Entry is not initialized in a default container
		self::assertFalse($this->builder->build()->initialized('foo'));
	}

	public function test_reset_with_definition(): void {
		$this->builder->addDefinitions([
			'foo' => 'bar',
		]);
		$container = $this->builder->build();

		// Sanity check the state of the entry prior to reset
		self::assertTrue($container->has('foo'));
		self::assertFalse($container->initialized('foo'));

		// Now initialize the entry, and sanity check the state again
		self::assertSame('bar', $container->get('foo'));
		self::assertTrue($container->has('foo'));
		self::assertTrue($container->initialized('foo'));

		// Then reset the entry
		$container->reset('foo');

		// It should still be gettable, but it is no longer initialized
		self::assertTrue($container->has('foo'));
		self::assertFalse($container->initialized('foo'));
		self::assertSame('bar', $container->get('foo'));
	}

	public function test_reset_when_set_directly(): void {
		$container = $this->builder->build();
		$container->set('foo', 'bar');

		// After resetting an entry that does not have a definition (i.e. it is
		// only set directly), it is no longer gettable.
		$container->reset('foo');
		self::assertFalse($container->has('foo'));
	}

}
