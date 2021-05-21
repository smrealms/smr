<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

/**
 * @covers ::pluralise
 */
class pluraliseTest extends \PHPUnit\Framework\TestCase {

	/**
	 * @dataProvider pluralise_provider
	 */
	public function test_pluralise(string $in, mixed $count, string $expect) {
		$result = pluralise($in, $count);
		$this->assertSame($expect, $result);
	}

	public function pluralise_provider() {
		return [
			['test', 3, 'tests'],
			['test', 1, 'test'],
			['is', 3, 'are'],
			['Is', 3, 'are'],
			['is', 1, 'is'],
			['test', 0, 'tests'],
			['test', 0.5, 'tests'],
		];
	}

}
