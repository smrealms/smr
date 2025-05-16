<?php declare(strict_types=1);

namespace SmrTest\lib\StdlibExtensions;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Smr\StdlibExtensions\InfiniteArrayIterator;

#[CoversClass(InfiniteArrayIterator::class)]
class InfiniteArrayIteratorTest extends TestCase {

	public function test_current_and_next(): void {
		// These methods forward directly to the stdlib iterator methods
		$it = new InfiniteArrayIterator(['a', 'b']);

		self::assertSame('a', $it->current());
		$it->next();
		self::assertSame('b', $it->current());

		// Since it is an InfiniteIterator, it loops back around
		$it->next();
		self::assertSame('a', $it->current());
	}

	public function test_getAndAdvance(): void {
		// Test that the iterator ignores keys
		$it = new InfiniteArrayIterator([7 => 'a', 3 => 'b', 6 => 'c']);

		// Test that it can keep looping around
		foreach (str_split('abcabcabc') as $expected) {
			self::assertSame($expected, $it->getAndAdvance());
		}
	}

}
