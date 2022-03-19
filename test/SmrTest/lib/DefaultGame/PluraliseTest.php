<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use PHPUnit\Framework\TestCase;

/**
 * @covers ::pluralise
 */
class PluraliseTest extends TestCase {

	/**
	 * @dataProvider pluralise_provider
	 */
	public function test_pluralise(float|int $amount, bool $includeAmount, string $expect): void {
		$result = pluralise($amount, 'test', $includeAmount);
		$this->assertSame($expect, $result);
	}

	public function pluralise_provider(): array {
		return [
			[3, true, '3 tests'],
			[1, true, '1 test'],
			[0, true, '0 tests'],
			[0.5, true, '0.5 tests'],
			[3, false, 'tests'],
			[1, false, 'test'],
		];
	}

}
