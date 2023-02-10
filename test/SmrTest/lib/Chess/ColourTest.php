<?php declare(strict_types=1);

namespace SmrTest\lib\Chess;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Smr\Chess\Colour;

#[CoversClass(Colour::class)]
class ColourTest extends TestCase {

	public function test_opposite(): void {
		self::assertSame(Colour::White, Colour::Black->opposite());
		self::assertSame(Colour::Black, Colour::White->opposite());
	}

}
