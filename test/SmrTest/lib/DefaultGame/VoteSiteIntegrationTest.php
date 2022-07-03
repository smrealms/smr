<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use Exception;
use Smr\VoteLink;
use Smr\VoteSite;
use SmrTest\BaseIntegrationSpec;

/**
 * @covers Smr\VoteLink
 * @covers Smr\VoteSite
 */
class VoteSiteIntegrationTest extends BaseIntegrationSpec {

	protected function tablesToTruncate(): array {
		return ['vote_links'];
	}

	protected function tearDown(): void {
		VoteLink::clearCache();
	}

	public function test_getTimeUntilFreeTurns_invalid(): void {
		// Get a vote site that is not configured to award free turns
		$link = new VoteLink(VoteSite::PBBG, 1, 1);

		// Make sure it raises an exception if we call getTimeUntilFreeTurns
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('This vote site cannot award free turns!');
		$link->getTimeUntilFreeTurns();
	}

	public function test_vote_callback_happy_path(): void {
		// Get a vote site that is configured to award free turns
		$link = new VoteLink(VoteSite::TWG, 1, 2);

		// Test that the site is eligible to award free turns by default
		self::assertTrue($link->freeTurnsReady());

		// Simulate clicking a vote link
		$link->setClicked();

		// Simulate awarding free turns
		self::assertTrue($link->setFreeTurnsAwarded());

		// Test that we're not ready to award free turns anymore
		self::assertFalse($link->freeTurnsReady(true));
	}

	public function test_vote_callback_abuse_detection(): void {
		// Get a vote site that is configured to award free turns
		$link = new VoteLink(VoteSite::TWG, 1, 2);

		// Award free turns normally
		$link->setClicked();
		$link->setFreeTurnsAwarded();

		// Test that we do not award free turns again
		self::assertFalse($link->setFreeTurnsAwarded());

		// Test that circumventing the timeout throws an exception
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Account ID 1 attempted vote link abuse');
		$link->setClicked();
	}

	public function test_vote_button_properties(): void {
		// Set some arbitrary test data
		$accountID = 7;
		$gameID = 42;

		// Set expected results when free turns are available
		$expected = [
			VoteSite::TWG->value => [
				'img' => 'twg_vote.png',
				'url' => 'http://topwebgames.com/in.aspx?ID=136&account=7&game=42&link=3&alwaysreward=1',
				'sn' => LOADER_URI . '?sn=gbuyay',
			],
			VoteSite::DOG->value => [
				'img' => 'dog_vote.png',
				'url' => 'http://www.directoryofgames.com/main.php?view=topgames&action=vote&v_tgame=2315&votedef=7,42,4',
				'sn' => LOADER_URI . '?sn=wnpclr',
			],
			VoteSite::PBBG->value => [
				'img' => 'pbbg.png',
				'url' => 'https://pbbg.com/games/space-merchant-realms',
				'sn' => false,
			],
		];

		srand(123); // set rand seed for session href generation
		foreach ($expected as $siteID => $data) {
			$link = new VoteLink(VoteSite::from($siteID), $accountID, $gameID);
			self::assertSame($data['img'], $link->getImg());
			self::assertSame($data['url'], $link->getUrl());
			self::assertSame($data['sn'], $link->getSN());
		}

		// Set expected results when free turns are NOT available
		$expected = [
			VoteSite::TWG->value => [
				'img' => 'twg.png',
				'url' => 'http://topwebgames.com/in.aspx?ID=136',
				'sn' => false,
			],
			VoteSite::DOG->value => [
				'img' => 'dog.png',
				'url' => 'http://www.directoryofgames.com/main.php?view=topgames&action=vote&v_tgame=2315',
				'sn' => false,
			],
			VoteSite::PBBG->value => [
				'img' => 'pbbg.png',
				'url' => 'https://pbbg.com/games/space-merchant-realms',
				'sn' => false,
			],
		];

		foreach ($expected as $siteID => $data) {
			// game ID 0 suppresses free turn links
			$link = new VoteLink(VoteSite::from($siteID), $accountID, 0);
			self::assertSame($data['img'], $link->getImg());
			self::assertSame($data['url'], $link->getUrl());
			self::assertSame($data['sn'], $link->getSN());
		}
	}

	public function test_getMinTimeUntilFreeTurns(): void {
		// Set arbitrary test data
		$accountID = 9;
		$gameID = 17;

		// Test that by default that the min time is negative
		self::assertTrue(VoteLink::getMinTimeUntilFreeTurns($accountID, $gameID) < 0);

		// Test that min time is still negative if we claim turns on one site
		$link = new VoteLink(VoteSite::DOG, $accountID, $gameID);
		$link->setClicked();
		$link->setFreeTurnsAwarded();
		VoteLink::clearCache();
		self::assertTrue(VoteLink::getMinTimeUntilFreeTurns($accountID, $gameID) < 0);

		// Test that the min time is positive if we claim turns on all sites
		foreach (VoteSite::cases() as $site) {
			$link = new VoteLink($site, $accountID, $gameID);
			if ($link->freeTurnsReady()) {
				$link->setClicked();
				$link->setFreeTurnsAwarded();
			}
		}
		VoteLink::clearCache();
		self::assertSame(VoteLink::TIME_BETWEEN_VOTING, VoteLink::getMinTimeUntilFreeTurns($accountID, $gameID));
	}

}
