<?php declare(strict_types=1);

namespace SmrTest;

use Exception;
use ReflectionClass;
use ReflectionMethod;

class TestUtils {

	/**
	 * Get a private or protected method for testing/documentation purposes.
	 * Note that this function should only be used as a last resort! Its use
	 * indicates that the input class is a good candidate for refactoring.
	 *
	 * How to use for MyClass->foo():
	 *    $cls = new MyClass();
	 *    $foo = SmrTest\TestUtils::getPrivateMethod($cls, 'foo');
	 *    $foo->invoke($cls, $args, ...);
	 *
	 * @param object $obj The instance of your class
	 * @param string $name The name of your private/protected method
	 * @return \ReflectionMethod The method you want to test
	 */
	public static function getPrivateMethod(object $obj, string $name): ReflectionMethod {
		$method = new ReflectionMethod($obj, $name);
		$method->setAccessible(true);
		return $method;
	}

	/**
	 * Construct an instance of a class with a protected constructor for test
	 * purposes. Note that this function should only be used as a last resort!
	 * Its use indicates that the input class is a candidate for refactoring.
	 *
	 * @template T of object
	 * @param class-string<T> $name The name of the class to construct
	 * @param mixed ...$args The arguments to pass to the constructor
	 * @return T
	 */
	public static function constructPrivateClass(string $name, ...$args): object {
		$class = new ReflectionClass($name);
		$constructor = $class->getConstructor();
		if ($constructor === null) {
			throw new Exception('Class does not have a constructor: ' . $name);
		}
		$constructor->setAccessible(true);
		$object = $class->newInstanceWithoutConstructor();
		$constructor->invoke($object, ...$args);
		return $object;
	}

}
