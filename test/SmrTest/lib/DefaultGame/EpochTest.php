<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use Smr\Epoch;

/**
 * @covers Smr\Epoch
 */
class EpochTest extends \PHPUnit\Framework\TestCase {

	/**
	 * Test that the `update` function works properly when NPC_SCRIPT is set.
	 * We run in a separate process so that the constant doesn't propagate into
	 * other tests.
	 * @runInSeparateProcess
	 */
	public function test_update_cli() {
		// Set the NPC_SCRIPT variable as if this were a CLI program
		define('NPC_SCRIPT', true);

		$time = Epoch::time();
		$microtime = Epoch::microtime();

		// Sleep 1 second to ensure that the integer time has incremented
		sleep(1);
		Epoch::update();

		// Make sure the times have changed
		$this->assertNotEquals($time, Epoch::time());
		$this->assertNotEquals($microtime, Epoch::microtime());
	}

	/**
	 * update should throw if called without NPC_SCRIPT defined.
	 */
	public function test_update() {
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Only call this function from CLI programs');
		Epoch::update();
	}

	/**
	 * We can't check the time/microtime values, but we can ensure that
	 * the rounded values are identical.
	 */
	public function test_time_microtime_equality() {
		$this->assertEquals(Epoch::time(), floor(Epoch::microtime()));
	}

}
