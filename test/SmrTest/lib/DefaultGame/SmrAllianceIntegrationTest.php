<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use SmrAlliance;
use SmrTest\BaseIntegrationSpec;
use Smr\Exceptions\UserError;
use Smr\Exceptions\AllianceNotFound;

/**
 * @covers SmrAlliance
 */
class SmrAllianceIntegrationTest extends BaseIntegrationSpec {

	protected function setUp() : void {
		// Start each test with an empty alliance cache
		SmrAlliance::clearCache();
	}

	public function test_getAllianceByIrcChannel() : void {
		// Create an Alliance and set its IRC channel
		$gameID = 1;
		$channel = '#ircrules';
		$alliance1 = SmrAlliance::createAlliance($gameID, 'test');
		$alliance1->setIrcChannel($channel);
		$alliance1->update();

		// Test that we recover the original Alliance from the IRC channel
		$alliance2 = SmrAlliance::getAllianceByIrcChannel($channel);
		self::assertSame($alliance2, $alliance1);

		// Test that we raise an exception with the wrong IRC channel
		$this->expectException(AllianceNotFound::class);
		$this->expectExceptionMessage('Alliance IRC Channel not found');
		SmrAlliance::getAllianceByIrcChannel('#notircrules');
	}

	public function test_getAllianceByName() : void {
		// Create an Alliance with a specific name
		$gameID = 3;
		$name = 'test';
		$alliance1 = SmrAlliance::createAlliance($gameID, $name);

		// Test that we recover the original Alliance from the name
		$alliance2 = SmrAlliance::getAllianceByName($name, $gameID);
		self::assertSame($alliance2, $alliance1);

		// Test that we raise an exception with the wrong Alliance name
		$this->expectException(AllianceNotFound::class);
		$this->expectExceptionMessage('Alliance name not found');
		SmrAlliance::getAllianceByName('not' . $name, $gameID);
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

	public function test_createAlliance_with_NHA_name() : void {
		$this->expectException(UserError::class);
		$this->expectExceptionMessage('That alliance name is reserved.');
		SmrAlliance::createAlliance(1, NHA_ALLIANCE_NAME);
	}

	public function test_createAlliance_increment_allianceID() : void {
		SmrAlliance::createAlliance(1, 'test1');
		$alliance = SmrAlliance::createAlliance(1, 'test2');
		$this->assertSame(2, $alliance->getAllianceID());
	}

	public function test_isNHA() : void {
		// Create an alliance that is not the NHA
		$alliance = SmrAlliance::createAlliance(1, 'Vet Help Alliance', true);
		self::assertFalse($alliance->isNHA());

		// Create an alliance that is the NHA
		$alliance = SmrAlliance::createAlliance(1, NHA_ALLIANCE_NAME, true);
		self::assertTrue($alliance->isNHA());
	}

}
