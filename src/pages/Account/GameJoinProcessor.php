<?php declare(strict_types=1);

namespace Smr\Pages\Account;

use Smr\Account;
use Smr\Alliance;
use Smr\Database;
use Smr\DisplayNameValidator;
use Smr\Epoch;
use Smr\Game;
use Smr\Page\AccountPageProcessor;
use Smr\Player;
use Smr\Request;

class GameJoinProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $gameID
	) {}

	public function build(Account $account): never {
		$player_name = Request::get('player_name');

		DisplayNameValidator::validate($player_name);

		$gameID = $this->gameID;
		$game = Game::getGame($gameID);

		$race_id = Request::getInt('race_id');
		if (!in_array($race_id, $game->getPlayableRaceIDs())) {
			create_error('Please choose a race!');
		}

		// does it cost SMR Credits to join this game?
		$creditsNeeded = $game->getCreditsNeeded();
		if ($account->getTotalSmrCredits() < $creditsNeeded) {
			create_error('You do not have enough SMR credits to join this game!');
		}
		$account->decreaseTotalSmrCredits($creditsNeeded);

		// Decide whether the player should be "newbie" or "vet"
		if ($game->isGameType(Game::GAME_TYPE_SEMI_WARS)) {
			$isNewbie = false; // all players considered vet in Semi Wars
		} else {
			$isNewbie = !$account->isVeteran();
		}

		// for newbie and beginner another ship, more shields and armour
		if ($isNewbie) {
			$startingNewbieTurns = STARTING_NEWBIE_TURNS_NEWBIE;
		} else {
			$startingNewbieTurns = STARTING_NEWBIE_TURNS_VET;
		}

		// insert into player table.
		$player = Player::createPlayer($account->getAccountID(), $gameID, $player_name, $race_id, $isNewbie);

		// Equip the ship
		$player->getShip()->giveStarterShip();

		$player->giveStartingTurns(); // must be done after setting ship
		$player->setNewbieTurns($startingNewbieTurns);
		$player->setCredits($game->getStartingCredits());
		$player->giveStartingRelations();

		// The `player_visited_sector` table holds *unvisited* sectors, so that once
		// all sectors are visited (the majority of the game), the table is empty.
		$db = Database::getInstance();
		$db->write('INSERT INTO player_visited_sector (account_id, game_id, sector_id)
		            SELECT :account_id, game_id, sector_id
		              FROM sector WHERE game_id = :game_id', [
			'account_id' => $db->escapeNumber($account->getAccountID()),
			'game_id' => $db->escapeNumber($gameID),
		]);

		// Mark the player's start sector as visited
		$player->getSector()->markVisited($player);

		if ($isNewbie || $account->getAccountID() == ACCOUNT_ID_NHL) {
			// If player is a newb (or NHL), set alliance to be Newbie Help Allaince
			$NHA = Alliance::getAllianceByName(NHA_ALLIANCE_NAME, $gameID);
			$player->joinAlliance($NHA->getAllianceID());

			//we need to send them some messages
			$message = 'Welcome to Space Merchant Realms! You have been automatically placed into the <u>' . $player->getAllianceBBLink() . '</u>, which is led by a veteran player who can assist you while you learn the basics of the game. Your alliance leader is denoted with a star on your alliance roster.<br />
			For more tips to help you get started with the game, check out your alliance message boards. These can be reached by clicking the "Alliance" link on the left side of the page, and then clicking the "Message Board" menu link. The <u><a href="' . WIKI_URL . '" target="_blank">SMR Wiki</a></u> also gives detailed information on all aspects of the game.<br />
			SMR is integrated with both IRC and Discord. These are free chat services where you can talk to other players and coordinate with your alliance. Simply click the "Join Chat" link at the bottom left panel of the page.';

			Player::sendMessageFromAdmin($gameID, $account->getAccountID(), $message);
		}

		// We aren't in a game yet, so updates are not done automatically here
		$player->update();
		$player->getShip()->update();

		// Announce the player joining in the news
		$news = '[player=' . $player->getPlayerID() . '] has joined the game!';
		$db->insert('news', [
			'time' => Epoch::time(),
			'news_message' => $news,
			'game_id' => $gameID,
			'type' => 'admin',
			'killer_id' => $player->getAccountID(),
		]);

		// Send the player directly into the game
		$container = new GamePlayProcessor($this->gameID);
		$container->go();
	}

}
