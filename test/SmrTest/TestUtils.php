<?php declare(strict_types=1);

namespace SmrTest;

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
		$class = new ReflectionClass($obj);
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method;
	}

}
