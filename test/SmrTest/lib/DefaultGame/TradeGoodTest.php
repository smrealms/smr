<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use Smr\TradeGood;

/**
 * @covers Smr\TradeGood
 */
class TradeGoodTest extends TestCase {

	private static bool $original_libxml_use_internal_errors;

	public static function setUpBeforeClass(): void {
		// Make sure cache is clear so we can cover the cache population code
		TradeGood::clearCache();

		// Get the original libxml state so we can restore it later
		self::$original_libxml_use_internal_errors = libxml_use_internal_errors(true);
		libxml_clear_errors();
	}

	public static function tearDownAfterClass(): void {
		// Restore the original libxml state
		libxml_use_internal_errors(self::$original_libxml_use_internal_errors);
	}

	public function test_getAllIDs(): void {
		$expected = [
			GOODS_WOOD,
			GOODS_FOOD,
			GOODS_ORE,
			GOODS_PRECIOUS_METALS,
			GOODS_SLAVES,
			GOODS_TEXTILES,
			GOODS_MACHINERY,
			GOODS_CIRCUITRY,
			GOODS_WEAPONS,
			GOODS_COMPUTERS,
			GOODS_LUXURY_ITEMS,
			GOODS_NARCOTICS,
		];
		self::assertSame($expected, TradeGood::getAllIDs());
	}

	public function test_get(): void {
		// Spot check one of the TradeGoods
		$expected = new TradeGood(
			id: GOODS_WEAPONS,
			name: 'Weapons',
			maxPortAmount: 5000,
			basePrice: 168,
			class: 2,
			alignRestriction: -115,
		);
		self::assertEquals($expected, TradeGood::get(GOODS_WEAPONS));
	}

	public function test_getImageHTML(): void {
		foreach (TradeGood::getAll() as $good) {
			// Test as both HTML and XML because they each catch different errors
			// (loadHTML can validate things like tags, but automatically fixes many
			// HTML syntax errors without reporting them; whereas loadXML is more
			// strict about syntax, but not all XML is also valid HTML).
			$html = $good->getImageHTML();
			$dom = new DOMDocument();
			$dom->loadHTML($html);
			$dom->loadXML($html);
			self::assertSame([], libxml_get_errors(), $good->name);
			// Clear errors for the next case
			libxml_clear_errors();
		}
	}

}
