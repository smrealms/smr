<?php declare(strict_types=1);

namespace SmrTest\lib;

use PHPUnit\Framework\Attributes\CoversClass;
use Smr\AbstractPlayer;
use Smr\Bounty;
use Smr\BountyType;
use Smr\Database;
use Smr\DatabaseRecord;
use Smr\Player;
use Smr\ScoutMessageGroupType;
use SmrTest\BaseIntegrationSpec;
use SmrTest\TestUtils;

#[CoversClass(Bounty::class)]
class BountyTest extends BaseIntegrationSpec {

	protected function tablesToTruncate(): array {
		return ['bounty'];
	}

	public function test_max_credits(): void {
		$bounty = new Bounty(
			targetID: 1,
			bountyID: 1,
			gameID: 1,
			type: BountyType::UG,
			time: 0,
			claimerID: 0,
			credits: 0,
			smrCredits: 0,
		);
		// increase bounty credits to the maximum amount
		$bounty->increaseCredits(SQL_MAX_UNSIGNED_INT);
		self::assertEquals($bounty->getCredits(), SQL_MAX_UNSIGNED_INT);
		// further increases don't change the value
		$bounty->increaseCredits(1);
		self::assertEquals($bounty->getCredits(), SQL_MAX_UNSIGNED_INT);
	}

	public function test_update(): void {
		// Calling update on a new bounty will add it to the database
		$bounty = new Bounty(
			targetID: 3,
			bountyID: 4,
			gameID: 5,
			type: BountyType::UG,
			time: 6,
			claimerID: 0,
			credits: 8,
			smrCredits: 9,
		);
		self::assertTrue($bounty->update());

		// Reading it out of the database gets back an equal object
		$db = Database::getInstance();
		$dbRecord = $db->read('SELECT * FROM bounty')->record();
		self::assertEquals($bounty, Bounty::getFromRecord($dbRecord));

		// Calling update on an unchanged bounty should do nothing
		self::assertFalse($bounty->update());

		// Changing the credits should result in a database update
		$bounty->increaseCredits(2);
		self::assertSame(10, $bounty->getCredits());
		self::assertTrue($bounty->update());

		$bounty->increaseSmrCredits(2);
		self::assertSame(11, $bounty->getSmrCredits());
		self::assertTrue($bounty->update());

		// Changing the claimer should result in a database update
		self::assertTrue($bounty->isActive());
		$bounty->setClaimable(7);
		self::assertFalse($bounty->isActive());
		self::assertTrue($bounty->update());

		// All modified fields should be updated in the database
		$dbRecord = $db->read('SELECT * FROM bounty')->record();
		self::assertEquals($bounty, Bounty::getFromRecord($dbRecord));

		// Updating a claimed bounty should delete it from the database
		$bounty->setClaimed();
		self::assertSame(0, $bounty->getCredits());
		self::assertSame(0, $bounty->getSmrCredits());
		self::assertTrue($bounty->update());
		$dbResult = $db->read('SELECT * FROM bounty');
		self::assertFalse($dbResult->hasRecord());
	}

	public function test_getPlacedOnPlayer(): void {
		// Two bounties the same, except which player they're on
		$bounty1 = new Bounty(
			targetID: 1,
			bountyID: 7,
			gameID: 42,
			type: BountyType::HQ,
			time: 0,
			credits: 1,
		);
		$bounty2 = new Bounty(
			targetID: 2,
			bountyID: 7,
			gameID: 42,
			type: BountyType::HQ,
			time: 0,
			credits: 1,
		);
		// Add bounties to the database
		$bounty1->update();
		$bounty2->update();

		// Stub player (can't use default mock for enums)
		$dbRecord = $this->createStub(DatabaseRecord::class);
		$dbRecord->method('getStringEnum')->willReturn(ScoutMessageGroupType::Auto);
		$player1 = TestUtils::constructPrivateClass(
			name: Player::class,
			gameID: 42,
			accountID: 1,
			dbRecord: $dbRecord,
		);

		// We should only get $bounty1 if we get bounties on player 1
		$bounties = Bounty::getPlacedOnPlayer($player1);
		self::assertEquals([7 => $bounty1], $bounties);
	}

	public function test_getClaimableByPlayer(): void {
		$bounty1 = new Bounty(
			targetID: 2,
			bountyID: 1,
			gameID: 42,
			type: BountyType::HQ,
			time: 0,
			credits: 1,
		);
		// Same as bounty1, except claimable by player 1
		$bounty2 = new Bounty(
			targetID: 2,
			bountyID: 2,
			gameID: 42,
			type: BountyType::HQ,
			time: 0,
			credits: 1,
			claimerID: 1,
		);
		// Same as bounty1, except claimable by player 1 and for the UG
		$bounty3 = new Bounty(
			targetID: 2,
			bountyID: 3,
			gameID: 42,
			type: BountyType::UG,
			time: 0,
			credits: 1,
			claimerID: 1,
		);
		// Add bounties to the database
		$bounty1->update();
		$bounty2->update();
		$bounty3->update();

		$player1 = $this->createStub(AbstractPlayer::class);
		$player1->method('getAccountID')->willReturn(1);
		$player1->method('getGameID')->willReturn(42);

		$bountiesHQ = Bounty::getClaimableByPlayer($player1, BountyType::HQ);
		self::assertEquals([$bounty2], $bountiesHQ);

		$bountiesUG = Bounty::getClaimableByPlayer($player1, BountyType::UG);
		self::assertEquals([$bounty3], $bountiesUG);

		$bounties = Bounty::getClaimableByPlayer($player1);
		self::assertEquals([$bounty2, $bounty3], $bounties);
	}

}
