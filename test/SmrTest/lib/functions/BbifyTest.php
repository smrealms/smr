<?php declare(strict_types=1);

namespace SmrTest\lib\functions;

use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversFunction('bbify')]
#[CoversFunction('smrBBCode')]
class BbifyTest extends TestCase {

	public function test_verbatim(): void {
		$result = bbify('[verbatim]Hello[/verbatim]', gameID: 0, noLinks: true);
		$expect = '<tt>Hello</tt>';
		self::assertSame($expect, $result);
	}

	#[TestWith([true, '<span style="color:#ffff00">Creonti</span>'])]
	#[TestWith([false, '<a href="/loader.php?sn=icmlzw"><span style="color:#ffff00">Creonti</span></a>'])]
	public function test_race(bool $noLinks, string $expect): void {
		srand(321); // set rand seed for session href generation
		$result = bbify('[race=3]', gameID: 0, noLinks: $noLinks);
		self::assertSame($expect, $result);
	}

}
