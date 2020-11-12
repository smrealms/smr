<?php

namespace SmrTest\lib\DefaultGame;

use PHPUnit\Framework\TestCase;
use Smr\Container\DiContainer;

class Foo {
	private Bar $bar;
	private float $rand;

	public function __construct(Bar $bar) {
		$this->bar = $bar;
		$this->rand = mt_rand() / mt_getrandmax();
	}
}

class Bar {
	public function __construct() {
	}
}

class FooTest extends TestCase {

	public function test_foo_equals() {
		// Get always retrieves the same instance, so this test proves that they are referentially equal
		$foo = DiContainer::get(Foo::class);
		$foo2 = DiContainer::get(Foo::class);
		self::assertEquals($foo, $foo2);
	}

	public function test_foo_not_equals() {
		$foo = DiContainer::get(Foo::class);
		// Make will create a brand new instance for the requested class
		$foo2 = DiContainer::make(Foo::class);
		// They are not referentially equal.
		self::assertNotEquals($foo, $foo2);
	}
}
