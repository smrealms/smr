<?php declare(strict_types=1);

namespace SmrTest\lib;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Smr\BarDrink;

#[CoversClass(BarDrink::class)]
class BarDrinkTest extends TestCase {

	public function test_getAll(): void {
		// all drinks are either special or common
		$all = BarDrink::getAll();
		$joined = array_merge(BarDrink::getCommon(), BarDrink::getSpecial());
		rsort($all);
		rsort($joined);
		self::assertSame($all, $joined);
	}

	public function test_getSpecial(): void {
		// all special drinks must be in the list of all drinks
		foreach (BarDrink::getSpecial() as $drink) {
			self::assertContains($drink, BarDrink::getAll());
		}
	}

	public function test_getCommon(): void {
		// all common drinks must be in the list of all drinks
		foreach (BarDrink::getCommon() as $drink) {
			self::assertContains($drink, BarDrink::getAll());
		}
	}

	public function test_isSpecial(): void {
		// special drinks are special
		foreach (BarDrink::getSpecial() as $drink) {
			self::assertTrue(BarDrink::isSpecial($drink));
		}
		// common drinks are not
		foreach (BarDrink::getCommon() as $drink) {
			self::assertFalse(BarDrink::isSpecial($drink));
		}
	}

	public function test_getSpecialMessage(): void {
		// every special drink has a special message
		foreach (BarDrink::getSpecial() as $drink) {
			self::assertIsString(BarDrink::getSpecialMessage($drink));
		}
	}

}
