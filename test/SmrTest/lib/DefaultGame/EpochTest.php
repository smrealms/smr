<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use Exception;
use PHPUnit\Framework\TestCase;
use Smr\Container\DiContainer;
use Smr\Epoch;

/**
 * @covers Smr\Epoch
 */
class EpochTest extends TestCase {

	protected function tearDown(): void {
		// Reset the DI container to avoid contaminating other tests
		DiContainer::initialize(false);
	}

	/**
	 * Test that the `update` function works properly when NPC_SCRIPT is set.
	 */
	public function test_update_cli(): void {
		// Set the NPC_SCRIPT variable as if this were a CLI program
		DiContainer::getContainer()->set('NPC_SCRIPT', true);

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
	 * update should throw if NPC_SCRIPT is false (its default value).
	 */
	public function test_update(): void {
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Only call this function from CLI programs');
		Epoch::update();
	}

	/**
	 * We can't check the time/microtime values, but we can ensure that
	 * the rounded values are identical.
	 */
	public function test_time_microtime_equality(): void {
		$this->assertEquals(Epoch::time(), floor(Epoch::microtime()));
	}

}
