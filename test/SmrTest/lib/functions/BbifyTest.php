<?php declare(strict_types=1);

namespace SmrTest\lib\functions;

use Nbbc\BBCode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Smr\BBCodeFormatter;

#[CoversFunction('bbify')]
#[CoversClass(BBCodeFormatter::class)]
class BbifyTest extends TestCase {

	#[TestWith(['No tag', 'No tag'])]
	#[TestWith(["Multi\nline", "Multi<br />\nline"])]
	public function test_no_tag(string $input, string $expect): void {
		$result = bbify($input, gameID: 0);
		self::assertSame($expect, $result);
	}

	public function test_unknown_tag(): void {
		$input = '[unknown=value]';
		self::assertSame($input, bbify($input, gameID: 0));
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

	public function test_combatlog(): void {
		$result = bbify('[combatlog=42]', gameID: 0);
		self::assertStringContainsString('<a href="/loader.php?sn=', $result);
		self::assertStringEndsWith('title="View the combat log" /></a>', $result);
	}

	public function test_sector_without_links(): void {
		$result = bbify('[sector=42]', gameID: 0, noLinks: true);
		self::assertSame('<span class="sectorColour">#42</span>', $result);
	}

	#[TestWith(['combatlog'])]
	#[TestWith(['player'])]
	#[TestWith(['alliance'])]
	#[TestWith(['chess'])]
	#[TestWith(['sector'])]
	public function test_bbify_preserves_invalid_numeric_tags(string $tag): void {
		// These reject non-numeric strings, and so return the original.
		$input = '[' . $tag . '=invalid]';
		self::assertSame($input, bbify($input, gameID: 0));
	}

	#[TestWith(['combatlog', '42'])]
	#[TestWith(['player', '42'])]
	#[TestWith(['alliance', '42'])]
	#[TestWith(['race', 'Creonti'])]
	#[TestWith(['servertimetouser', 'not a date'])]
	#[TestWith(['chess', '42'])]
	#[TestWith(['sector', '42'])]
	#[TestWith(['join_alliance', '42'])]
	public function test_renderTag_bbcode_check(string $tagName, string $default): void {
		// BBCODE_CHECK performs syntax validation only
		$result = BBCodeFormatter::getInstance()->renderTag(
			bbParser: new BBCode(),
			action: BBCode::BBCODE_CHECK,
			tagName: $tagName,
			default: $default,
			tagParams: [],
			tagContent: '',
		);
		self::assertTrue($result);
	}

	public function test_getInstance_returns_same_formatter(): void {
		self::assertSame(BBCodeFormatter::getInstance(), BBCodeFormatter::getInstance());
	}

}
