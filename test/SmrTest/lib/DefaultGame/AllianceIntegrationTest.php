<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use Smr\Alliance;
use Smr\Exceptions\AllianceNotFound;
use Smr\Exceptions\UserError;
use SmrTest\BaseIntegrationSpec;

/**
 * @covers Smr\Alliance
 */
class AllianceIntegrationTest extends BaseIntegrationSpec {

	protected function tablesToTruncate(): array {
		return ['alliance', 'irc_alliance_has_channel'];
	}

	protected function setUp(): void {
		// Start each test with an empty alliance cache
		Alliance::clearCache();
	}

	public function test_getAlliance_throws_when_no_record_found(): void {
		$this->expectException(AllianceNotFound::class);
		$this->expectExceptionMessage('Invalid allianceID: 2 OR gameID: 3');
		Alliance::getAlliance(2, 3);
	}

	public function test_getAllianceByIrcChannel(): void {
		// Create an Alliance and set its IRC channel
		$gameID = 1;
		$channel = '#ircrules';
		$alliance1 = Alliance::createAlliance($gameID, 'test');
		$alliance1->setIrcChannel($channel);
		$alliance1->update();

		// Test that we recover the original Alliance from the IRC channel
		$alliance2 = Alliance::getAllianceByIrcChannel($channel);
		self::assertSame($alliance2, $alliance1);

		// Test that we raise an exception with the wrong IRC channel
		$this->expectException(AllianceNotFound::class);
		$this->expectExceptionMessage('Alliance IRC Channel not found');
		Alliance::getAllianceByIrcChannel('#notircrules');
	}

	public function test_getAllianceByName(): void {
		// Create an Alliance with a specific name
		$gameID = 3;
		$name = 'test';
		$alliance1 = Alliance::createAlliance($gameID, $name);

		// Test that we recover the original Alliance from the name
		$alliance2 = Alliance::getAllianceByName($name, $gameID);
		self::assertSame($alliance2, $alliance1);

		// Test that we raise an exception with the wrong Alliance name
		$this->expectException(AllianceNotFound::class);
		$this->expectExceptionMessage('Alliance name not found');
		Alliance::getAllianceByName('not' . $name, $gameID);
	}

	public function test_createAlliance(): void {
		// Test arbitrary input
		$gameID = 42;
		$name = 'test';

		$alliance = Alliance::createAlliance($gameID, $name);

		self::assertSame($gameID, $alliance->getGameID());
		self::assertSame($name, $alliance->getAllianceName());
		self::assertSame(1, $alliance->getAllianceID());
	}

	public function test_createAlliance_duplicate_name(): void {
		$name = 'test';
		Alliance::createAlliance(1, $name);
		$this->expectException(UserError::class);
		$this->expectExceptionMessage('That alliance name already exists.');
		Alliance::createAlliance(1, $name);
	}

	public function test_createAlliance_with_NHA_name(): void {
		$this->expectException(UserError::class);
		$this->expectExceptionMessage('That alliance name is reserved.');
		Alliance::createAlliance(1, NHA_ALLIANCE_NAME);
	}

	public function test_createAlliance_increment_allianceID(): void {
		Alliance::createAlliance(1, 'test1');
		$alliance = Alliance::createAlliance(1, 'test2');
		self::assertSame(2, $alliance->getAllianceID());
	}

	public function test_isNHA(): void {
		// Create an alliance that is not the NHA
		$alliance = Alliance::createAlliance(1, 'Vet Help Alliance', true);
		self::assertFalse($alliance->isNHA());

		// Create an alliance that is the NHA
		$alliance = Alliance::createAlliance(1, NHA_ALLIANCE_NAME, true);
		self::assertTrue($alliance->isNHA());
	}

	public function test_isNone(): void {
		// Create an alliance that is not "none"
		$alliance = Alliance::createAlliance(1, 'Some alliance');
		self::assertFalse($alliance->isNone());

		// Create an alliance that is "none"
		$alliance = Alliance::getAlliance(0, 1);
		self::assertTrue($alliance->isNone());
	}

}
