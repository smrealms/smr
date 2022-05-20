<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use Exception;
use Smr\VoteSite;
use SmrTest\BaseIntegrationSpec;

/**
 * @covers Smr\VoteSite
 */
class VoteSiteIntegrationTest extends BaseIntegrationSpec {

	protected function tablesToTruncate(): array {
		return ['vote_links'];
	}

	protected function tearDown(): void {
		VoteSite::clearCache();
	}

	public function test_getTimeUntilFreeTurns_invalid(): void {
		// Get a vote site that is not configured to award free turns
		$site = VoteSite::getSite(VoteSite::LINK_ID_PBBG, 1);

		// Make sure it raises an exception if we call getTimeUntilFreeTurns
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('This vote site cannot award free turns!');
		$site->getTimeUntilFreeTurns();
	}

	public function test_vote_callback_workflow(): void {
		// Get a vote site that is configured to award free turns
		$site = VoteSite::getSite(VoteSite::LINK_ID_TWG, 1);

		// Test that the site is eligible to award free turns by default
		self::assertTrue($site->getTimeUntilFreeTurns() < 0);
		// Test that the site is in the "not clicked" state by default
		self::assertFalse($site->isLinkClicked());

		// Test again after setting the "clicked" link state
		$site->setLinkClicked();
		self::assertTrue($site->isLinkClicked());

		// Now pretend that we have awarded free turns
		$site->setFreeTurnsAwarded();

		// Test that we're back to the "not clicked" link state
		self::assertFalse($site->isLinkClicked());
		// Test that we're not ready to award free turns anymore
		VoteSite::clearCache();
		self::assertSame(TIME_BETWEEN_VOTING, $site->getTimeUntilFreeTurns());
	}

	public function test_vote_button_properties(): void {
		// Set some arbitrary test data
		$accountID = 7;
		$gameID = 42;

		// Set expected results when free turns are available
		$expected = [
			VoteSite::LINK_ID_TWG => [
				'img' => 'twg_vote.png',
				'url' => 'http://topwebgames.com/in.aspx?ID=136&account=7&game=42&link=3&alwaysreward=1',
				'sn' => LOADER_URI . '?sn=gbuyay',
			],
			VoteSite::LINK_ID_DOG => [
				'img' => 'dog_vote.png',
				'url' => 'http://www.directoryofgames.com/main.php?view=topgames&action=vote&v_tgame=2315&votedef=7,42,4',
				'sn' => LOADER_URI . '?sn=wnpclr',
			],
			VoteSite::LINK_ID_PBBG => [
				'img' => 'pbbg.png',
				'url' => 'https://pbbg.com/games/space-merchant-realms',
				'sn' => false,
			],
		];

		srand(123); // set rand seed for session href generation
		foreach ($expected as $linkID => $data) {
			$site = VoteSite::getSite($linkID, $accountID);
			self::assertSame($data['img'], $site->getLinkImg($gameID));
			self::assertSame($data['url'], $site->getLinkUrl($gameID));
			self::assertSame($data['sn'], $site->getSN($gameID));
		}

		// Set expected results when free turns are NOT available
		$expected = [
			VoteSite::LINK_ID_TWG => [
				'img' => 'twg.png',
				'url' => 'http://topwebgames.com/in.aspx?ID=136',
				'sn' => false,
			],
			VoteSite::LINK_ID_DOG => [
				'img' => 'dog.png',
				'url' => 'http://www.directoryofgames.com/main.php?view=topgames&action=vote&v_tgame=2315',
				'sn' => false,
			],
			VoteSite::LINK_ID_PBBG => [
				'img' => 'pbbg.png',
				'url' => 'https://pbbg.com/games/space-merchant-realms',
				'sn' => false,
			],
		];

		// Now claim free turns for each site
		foreach (array_keys($expected) as $linkID) {
			$site = VoteSite::getSite($linkID, $accountID);
			$site->setFreeTurnsAwarded();
		}
		VoteSite::clearCache();

		foreach ($expected as $linkID => $data) {
			$site = VoteSite::getSite($linkID, $accountID);
			self::assertSame($data['img'], $site->getLinkImg($gameID));
			self::assertSame($data['url'], $site->getLinkUrl($gameID));
			self::assertSame($data['sn'], $site->getSN($gameID));
		}
	}

	public function test_getMinTimeUntilFreeTurns(): void {
		// Set arbitrary test data
		$accountID = 9;

		// Test that by default that the min time is negative
		self::assertTrue(VoteSite::getMinTimeUntilFreeTurns($accountID) < 0);

		// Test that min time is still negative if we claim turns on one site
		VoteSite::getSite(VoteSite::LINK_ID_DOG, $accountID)->setFreeTurnsAwarded();
		VoteSite::clearCache();
		self::assertTrue(VoteSite::getMinTimeUntilFreeTurns($accountID) < 0);

		// Test that the min time is positive if we claim turns on all sites
		foreach (VoteSite::getAllSites($accountID) as $site) {
			$site->setFreeTurnsAwarded();
		}
		VoteSite::clearCache();
		self::assertSame(TIME_BETWEEN_VOTING, VoteSite::getMinTimeUntilFreeTurns($accountID));
	}

}
