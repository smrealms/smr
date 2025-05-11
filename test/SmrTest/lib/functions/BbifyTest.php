<?php declare(strict_types=1);

namespace SmrTest\lib\functions;

use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversFunction('bbify')]
#[CoversFunction('smrBBCode')]
class BbifyTest extends TestCase {

	#[TestWith(['No tag', 'No tag'])]
	#[TestWith(["Multi\nline", "Multi<br />\nline"])]
	public function test_no_tag(string $input, string $expect): void {
		$result = bbify($input, gameID: 0);
		self::assertSame($expect, $result);
	}

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

	public function test_race_invalid_id(): void {
		// Returned unmodified if an exception is thrown (e.g. invalid race)
		$input = '[race=700]';
		$result = bbify($input, gameID: 0);
		self::assertSame($input, $result);
	}

}
