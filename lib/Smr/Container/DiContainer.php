<?php declare(strict_types=1);

namespace Smr\Container;

use DI\Container;
use DI\ContainerBuilder;
use MySqlDatabase;
use mysqli;
use Smr\MysqlProperties;
use function DI\autowire;

/**
 * Class DiContainer
 * A wrapper around the DI\Container functionality that will allow
 * static usage of container methods.
 * @package Smr\Container
 */
class DiContainer {
	private static DiContainer $instance;
	private Container $container;

	private function __construct() {
		self::$instance = $this;
		$this->container = $this->buildContainer();
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
			 * The factories themselves are able to use dependency injection as well, so we can provide the MysqlProperties
			 * typehint to make sure the container constructs and instance and provides it to the factory.
			 */
			mysqli::class => function (MysqlProperties $mysqlProperties): mysqli {
				return new mysqli(
					$mysqlProperties->getHost(),
					$mysqlProperties->getUser(),
					$mysqlProperties->getPassword(),
					$mysqlProperties->getDatabaseName(),
					$mysqlProperties->getPort());
			},
			// Explicitly name all classes that are autowired, so we can take advantage of
			// the compiled container feature for a performance boost
			MysqlProperties::class => autowire(),
			MySqlDatabase::class => autowire()
		];
	}

	private function buildContainer(): Container {
		$builder = new ContainerBuilder();
		return $builder
			->addDefinitions($this->getDefinitions())
			->useAnnotations(false)
			->useAutowiring(true)
			// The CompiledContainer.php will be saved to the /tmp directory on the Docker container once
			// during its lifecycle (first request)
			->enableCompilation("/tmp")
			->build();
	}

	/**
	 * Create a new DI\Container instance.
	 * This needs to be done once during a bootstrapping script, like htdocs/config.inc
	 */
	public static function initializeContainer() {
		new DiContainer();
	}

	/**
	 * Retrieve the managed instance of $className, or construct a new instance with all dependencies.
	 * @param string $className The name of the class to retrieve from the container.
	 * @return mixed
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	public static function get(string $className) {
		return self::getContainer()->get($className);
	}

	/**
	 * Construct a fresh instance of $className. Dependencies will be retrieved from the container if they
	 * are already managed, and created themselves if they are not.
	 * @param string $className The name of the class to construct.
	 * @return mixed
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	public static function make(string $className) {
		return self::getContainer()->make($className);
	}

	/**
	 * Return the raw Container instance
	 * @return Container
	 */
	public static function getContainer(): Container {
		return self::$instance->container;
	}
}
