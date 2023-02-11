<?php declare(strict_types=1);

namespace Smr\Container;

use DI\ContainerBuilder;
use Doctrine\DBAL\Connection;
use Smr\Database;
use Smr\DatabaseProperties;
use Smr\Epoch;
use Smr\SectorLock;
use Smr\Session;
use Smr\Template;
use function DI\autowire;
use function DI\get;

/**
 * A wrapper around the DI\Container functionality that will allow
 * static usage of container methods.
 */
class DiContainer {

	private static DiContainer $instance;
	private readonly ResettableContainer|ResettableCompiledContainer $container;

	private function __construct(bool $enableCompilation) {
		$this->container = $this->buildContainer($enableCompilation);
	}

	/**
	 * @return array<string, mixed>
	 */
	private function getDefinitions(): array {
		return [
			Connection::class => function(DatabaseProperties $dbProperties): Connection {
				return Database::connectionFactory($dbProperties);
			},
			'DatabaseName' => function(DatabaseProperties $dbProperties): string {
				return $dbProperties->database;
			},
			'NPC_SCRIPT' => false,
			// Explicitly name all classes that are autowired, so we can take advantage of
			// the compiled container feature for a performance boost
			Epoch::class => autowire(),
			DatabaseProperties::class => autowire(),
			Database::class => autowire()
				->constructorParameter('dbName', get('DatabaseName')),
			SectorLock::class => autowire(),
			Session::class => autowire(),
			Template::class => autowire(),
		];
	}

	private function buildContainer(bool $enableCompilation): ResettableContainer|ResettableCompiledContainer {
		$builder = new ContainerBuilder(ResettableContainer::class);
		$builder
			->addDefinitions($this->getDefinitions())
			->useAutowiring(true);
		if ($enableCompilation) {
			// The CompiledContainer.php will be saved to the /tmp directory on the Docker container once
			// during its lifecycle (first request)
			$builder = $builder->enableCompilation('/tmp', containerParentClass: ResettableCompiledContainer::class);
		}
		return $builder->build();
	}

	/**
	 * Create a new DI\Container instance.
	 * This needs to be done once during a bootstrapping script, like bootstrap.php
	 */
	public static function initialize(bool $enableCompilation): void {
		self::$instance = new self($enableCompilation);
	}

	/**
	 * Retrieve the managed instance of $className, or construct a new instance with all dependencies.
	 * @param string $className The name of the class to retrieve from the container.
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	public static function get(string $className): mixed {
		return self::getContainer()->get($className);
	}

	/**
	 * Construct a fresh instance of $className. Dependencies will be retrieved from the container if they
	 * are already managed, and created themselves if they are not.
	 * @param string $className The name of the class to construct.
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	public static function make(string $className): mixed {
		return self::getContainer()->make($className);
	}

	/**
	 * Check if a managed instance of $className has been created.
	 * If the container itself has not been initialized yet, will always return false.
	 */
	public static function initialized(string $className): bool {
		return isset(self::$instance) && self::$instance->container->initialized($className);
	}

	/**
	 * Return the raw dependency injection Container instance for more robust
	 * container management operations.
	 */
	public static function getContainer(): ResettableContainer|ResettableCompiledContainer {
		return self::$instance->container;
	}

}
