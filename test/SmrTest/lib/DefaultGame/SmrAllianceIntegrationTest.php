<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use SmrAlliance;
use SmrTest\BaseIntegrationSpec;
use Smr\Exceptions\UserError;

/**
 * @covers SmrAlliance
 */
class SmrAllianceIntegrationTest extends BaseIntegrationSpec {

	protected function setUp() : void {
		// Start each test with an empty alliance cache
		SmrAlliance::clearCache();
	}

	public function test_createAlliance() : void {
		// Test arbitrary input
		$gameID = 42;
		$name = 'test';

		$alliance = SmrAlliance::createAlliance($gameID, $name);

		$this->assertSame($gameID, $alliance->getGameID());
		$this->assertSame($name, $alliance->getAllianceName());
		$this->assertSame(1, $alliance->getAllianceID());
	}

	public function test_createAlliance_duplicate_name() : void {
		$name = 'test';
		SmrAlliance::createAlliance(1, $name);
		$this->expectException(UserError::class);
		$this->expectExceptionMessage('That alliance name already exists.');
		SmrAlliance::createAlliance(1, $name);
	}

	public function test_createAlliance_increment_allianceID() : void {
		SmrAlliance::createAlliance(1, 'test1');
		$alliance = SmrAlliance::createAlliance(1, 'test2');
		$this->assertSame(2, $alliance->getAllianceID());
	}

}
