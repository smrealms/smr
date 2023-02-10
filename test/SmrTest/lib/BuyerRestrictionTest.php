<?php declare(strict_types=1);

namespace SmrTest\lib;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Smr\AbstractPlayer;
use Smr\BuyerRestriction;

#[CoversClass(BuyerRestriction::class)]
class BuyerRestrictionTest extends TestCase {

	private static bool $original_libxml_use_internal_errors;

	public static function setUpBeforeClass(): void {
		// Get the original libxml state so we can restore it later
		self::$original_libxml_use_internal_errors = libxml_use_internal_errors(true);
		libxml_clear_errors();
	}

	public static function tearDownAfterClass(): void {
		// Restore the original libxml state
		libxml_use_internal_errors(self::$original_libxml_use_internal_errors);
	}

	public function test_passes(): void {
		$player = $this->createStub(AbstractPlayer::class);

		// Test None restriction passes by default
		self::assertTrue(BuyerRestriction::None->passes($player));

		// Test Port/Planet restrictions fail by default
		self::assertFalse(BuyerRestriction::Port->passes($player));
		self::assertFalse(BuyerRestriction::Planet->passes($player));

		// Test Good/Evil restriction only passes with good/evil alignment
		$player
			->method('hasGoodAlignment')
			->willReturnOnConsecutiveCalls(true, false);
		self::assertTrue(BuyerRestriction::Good->passes($player));
		self::assertFalse(BuyerRestriction::Good->passes($player));
		$player
			->method('hasEvilAlignment')
			->willReturnOnConsecutiveCalls(true, false);
		self::assertTrue(BuyerRestriction::Evil->passes($player));
		self::assertFalse(BuyerRestriction::Evil->passes($player));

		// Test Newbie restriction only passes for newbies
		$player
			->method('hasNewbieStatus')
			->willReturnOnConsecutiveCalls(true, false);
		self::assertTrue(BuyerRestriction::Newbie->passes($player));
		self::assertFalse(BuyerRestriction::Newbie->passes($player));
	}

	public function test_display(): void {
		// Test that each case has valid display HTML
		foreach (BuyerRestriction::cases() as $restriction) {
			simplexml_load_string($restriction->display());
			self::assertSame([], libxml_get_errors());
			// Clear errors for the next case
			libxml_clear_errors();
		}
	}

}
