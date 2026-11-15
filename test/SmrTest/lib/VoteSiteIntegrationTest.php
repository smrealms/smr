<?php declare(strict_types=1);

namespace SmrTest\lib;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use Smr\Database;
use Smr\Epoch;
use Smr\Player;
use Smr\SectorLock;
use Smr\VoteLink;
use Smr\VoteSite;
use SmrTest\BaseIntegrationSpec;

#[CoversClass(VoteLink::class)]
#[CoversClass(VoteSite::class)]
class VoteSiteIntegrationTest extends BaseIntegrationSpec {

	protected function tearDown(): void {
		Player::clearCache();
		SectorLock::getInstance()->release();
		VoteLink::clearCache();
	}

	public function test_awardFreeTurns_rewards_all_active_games_once(): void {
		$accountID = 7;
		$otherAccountID = 8;
		$db = Database::getInstance();
		$now = Epoch::time();
		// Set up two active games (41, 42), one ended game (43), and one
		// game that has yet to start (44), with a player in each game.
		foreach ([41, 42, 43, 44] as $gameID) {
			$db->insert('game', [
				'game_id' => $gameID,
				'game_name' => 'Vote test ' . $gameID,
				'game_description' => '',
				'join_time' => 0,
				'start_time' => $gameID === 44 ? $now + 1800 : 0,
				'end_time' => $gameID === 43 ? $now - 1 : $now + 3600,
				'max_turns' => 0,
				'game_type' => 1,
				'enabled' => 'TRUE',
				'alliance_max_players' => 0,
				'alliance_max_vets' => 0,
				'starting_credits' => 0,
			]);
			$db->insert('player', [
				'account_id' => $accountID,
				'game_id' => $gameID,
				'player_number' => 1,
				'player_name' => 'Voter ' . $gameID,
				'last_active' => 0,
				'newbie_status' => 'FALSE',
				'sector_id' => 1,
				'last_turn_update' => $now,
			]);
		}
		// Create an unrelated player in one of the active games
		// (to make sure it doesn't get selected for free turns).
		$db->insert('player', [
			'account_id' => $otherAccountID,
			'game_id' => 41,
			'player_number' => 2,
			'player_name' => 'Other voter',
			'last_active' => 0,
			'newbie_status' => 'FALSE',
			'sector_id' => 1,
			'last_turn_update' => $now,
		]);
		$link = new VoteLink(
			site: VoteSite::TWG,
			accountID: $accountID,
		);
		$link->setClicked();
		self::assertTrue($link->awardFreeTurns());
		self::assertFalse($link->awardFreeTurns());
		$anotherLink = new VoteLink(
			site: VoteSite::TWG,
			accountID: $accountID,
		);
		self::assertFalse($anotherLink->freeTurnsReady(forceUpdate: true));
		foreach ([41, 42, 43, 44] as $gameID) {
			$result = $db->select('player', ['account_id' => $accountID, 'game_id' => $gameID]);
			$expected = $gameID === 41 || $gameID === 42 ? $now - VOTE_BONUS_TURNS_TIME : $now;
			self::assertSame($expected, $result->record()->getInt('last_turn_update'));
		}
		$otherPlayer = $db->select('player', ['account_id' => $otherAccountID, 'game_id' => 41]);
		self::assertSame($now, $otherPlayer->record()->getInt('last_turn_update'));
	}

	public function test_getTimeUntilFreeTurns_invalid(): void {
		// Get a vote site that is not configured to award free turns
		$link = new VoteLink(VoteSite::PBBG, 1);

		// Make sure it raises an exception if we call getTimeUntilFreeTurns
		$this->expectException(Exception::class);
		$this->expectExceptionMessageIs('This vote site cannot award free turns!');
		$link->getTimeUntilFreeTurns();
	}

	public function test_vote_callback_happy_path(): void {
		// Get a vote site that is configured to award free turns
		$link = new VoteLink(VoteSite::TWG, 1);

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
		$link = new VoteLink(VoteSite::TWG, 1);

		// Award free turns normally
		$link->setClicked();
		$link->setFreeTurnsAwarded();

		// Test that we do not award free turns again
		self::assertFalse($link->setFreeTurnsAwarded());

		// Test that circumventing the timeout throws an exception
		$this->expectException(Exception::class);
		$this->expectExceptionMessageIs('Account ID 1 attempted vote link abuse');
		$link->setClicked();
	}

	public function test_vote_button_properties(): void {
		// Set some arbitrary test data
		$accountID = 7;

		// Set expected results when free turns are available
		$expected = [
			VoteSite::TWG->value => [
				'img' => 'twg_vote.png',
				'url' => 'https://topwebgames.com/in.aspx?ID=136&account=7&link=3&alwaysreward=1',
				'sn' => LOADER_URI . '?sn=gbuyay',
			],
			VoteSite::PBBG->value => [
				'img' => 'pbbg.png',
				'url' => 'https://pbbg.com/games/space-merchant-realms',
				'sn' => false,
			],
		];

		srand(123); // set rand seed for session href generation
		foreach ($expected as $siteID => $data) {
			$link = new VoteLink(VoteSite::from($siteID), $accountID);
			self::assertSame($data['img'], $link->getImg());
			self::assertSame($data['url'], $link->getUrl());
			self::assertSame($data['sn'], $link->getSN());
		}

		// Claim turns so the site is no longer eligible.
		$link = new VoteLink(VoteSite::TWG, $accountID);
		$link->setClicked();
		$link->setFreeTurnsAwarded();
		VoteLink::clearCache();

		// Set expected results when free turns are NOT available
		$expected = [
			VoteSite::TWG->value => [
				'img' => 'twg.png',
				'url' => 'https://topwebgames.com/in.aspx?ID=136',
				'sn' => false,
			],
			VoteSite::PBBG->value => [
				'img' => 'pbbg.png',
				'url' => 'https://pbbg.com/games/space-merchant-realms',
				'sn' => false,
			],
		];

		foreach ($expected as $siteID => $data) {
			$link = new VoteLink(VoteSite::from($siteID), $accountID);
			self::assertSame($data['img'], $link->getImg());
			self::assertSame($data['url'], $link->getUrl());
			self::assertSame($data['sn'], $link->getSN());
		}
	}

	public function test_getMinTimeUntilFreeTurns_default(): void {
		// Test that by default that the min time is zero
		$accountID = 9;
		self::assertSame(0, VoteLink::getMinTimeUntilFreeTurns($accountID));
	}

	public function test_getMinTimeUntilFreeTurns_all_links_clicked(): void {
		// Test that the min time is positive if we claim turns on all sites
		// (Note: we currently only have one free-turns-ready vote site, so we
		// can't test this function with only some links clicked.)
		$accountID = 9;
		foreach (VoteSite::cases() as $site) {
			$link = new VoteLink($site, $accountID);
			if ($link->freeTurnsReady()) {
				$link->setClicked();
				$link->setFreeTurnsAwarded();
			}
		}
		VoteLink::clearCache();
		self::assertSame(VoteLink::TIME_BETWEEN_VOTING, VoteLink::getMinTimeUntilFreeTurns($accountID));
	}

}
