<?php declare(strict_types=1);

namespace Smr\Container;

use DI\Container;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use mysqli;
use Smr\Database;
use Smr\DatabaseProperties;
use Smr\Epoch;
use Smr\Session;
use Smr\Template;
use function DI\autowire;

/**
 * A wrapper around the DI\Container functionality that will allow
 * static usage of container methods.
 */
class DiContainer {

	private static DiContainer $instance;
	private Container $container;

	private function __construct(bool $enableCompilation) {
		$this->container = $this->buildContainer($enableCompilation);
	}

	private function getDefinitions(): array {
		return [
			/*
			 * mysqli is a 3rd party library, and we do not have control over its constructor.
			 * In order for PHP-DI to construct an instance of a class, each constructor argument must
			 * be able to be constructed.
			 * PHP-DI cannot construct mysqli by itself, because all of its arguments are primitive types.
			 * Therefore, we need to declare a provider factory for the container to use when constructing new instances.
			 *
			 * The factories themselves are able to use dependency injection as well, so we can provide the DatabaseProperties
			 * typehint to make sure the container constructs an instance and provides it to the factory.
			 */
			mysqli::class => function(DatabaseProperties $dbProperties): mysqli {
				return Database::mysqliFactory($dbProperties);
			},
			Dotenv::class => function(): Dotenv {
				return Dotenv::createArrayBacked(CONFIG, 'env');
			},
			'DatabaseName' => function(DatabaseProperties $dbProperties): string {
				return $dbProperties->getDatabaseName();
			},
			// Explicitly name all classes that are autowired, so we can take advantage of
			// the compiled container feature for a performance boost
			Epoch::class => autowire(),
			DatabaseProperties::class => autowire(),
			Database::class => autowire()
				->constructorParameter('dbName', \DI\get('DatabaseName')),
			Session::class => autowire(),
			Template::class => autowire(),
		];
	}

	private function buildContainer(bool $enableCompilation): Container {
		$builder = new ContainerBuilder();
		$builder
			->addDefinitions($this->getDefinitions())
			->useAnnotations(false)
			->useAutowiring(true);
		if ($enableCompilation) {
			// The CompiledContainer.php will be saved to the /tmp directory on the Docker container once
			// during its lifecycle (first request)
			$builder->enableCompilation('/tmp');
		}
		// TODO: deprecation warnings suppressed until PHP 8.1 supported!
		// See https://github.com/PHP-DI/PHP-DI/pull/794.
		return @$builder->build();
	}

	/**
	 * Create a new DI\Container instance.
	 * This needs to be done once during a bootstrapping script, like bootstrap.php
	 */
	public static function initialize($enableCompilation): void {
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
	 * Return the raw dependency injection Container instance for more robust
	 * container management operations.
	 */
	public static function getContainer(): Container {
		return self::$instance->container;
	}

}
