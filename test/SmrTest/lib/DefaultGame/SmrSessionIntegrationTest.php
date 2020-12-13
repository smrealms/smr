<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use SmrSession;
use SmrTest\BaseIntegrationSpec;

/**
 * @covers SmrSession
 */
class SmrSessionIntegrationTest extends BaseIntegrationSpec {

	/**
	 * Test that the updateTime function works properly when NPC_SCRIPT is set.
	 * We run in a separate process so that the constant doesn't propagate into
	 * other tests.
	 * @runInSeparateProcess
	 */
	public function test_updateTime_cli() {
		// Set the NPC_SCRIPT variable as if this were a CLI program
		define('NPC_SCRIPT', true);

		$time = SmrSession::getTime();
		$microtime = SmrSession::getMicrotime();

		// Sleep 1 second to ensure that the integer time has incremented
		sleep(1);
		SmrSession::updateTime();

		// Make sure the times have changed
		$this->assertNotEquals($time, SmrSession::getTime());
		$this->assertNotEquals($microtime, SmrSession::getMicroTime());
	}

	/**
	 * updateTime should throw if called without NPC_SCRIPT defined.
	 */
	public function test_updateTime() {
		$this->expectException(\Exception::class);
		SmrSession::updateTime();
	}

	/**
	 * We can't check the getTime/getMicroTime values, but we can ensure that
	 * the rounded values are identical.
	 */
	public function test_getTime_getMicroTime_equality() {
		$this->assertEquals(SmrSession::getTime(), floor(SmrSession::getMicroTime()));
	}

}
