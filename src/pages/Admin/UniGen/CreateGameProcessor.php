<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use DateTime;
use Smr\Account;
use Smr\Database;
use Smr\Game;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class CreateGameProcessor extends AccountPageProcessor {

	public function build(Account $account): never {
		$db = Database::getInstance();

		//first create the game
		$dbResult = $db->read('SELECT 1 FROM game WHERE game_name = :game_name', [
			'game_name' => $db->escapeString(Request::get('game_name')),
		]);
		if ($dbResult->hasRecord()) {
			create_error('That game name is already taken.');
		}

		$dbResult = $db->read('SELECT IFNULL(MAX(game_id), 0) AS max_game_id FROM game');
		$newID = $dbResult->record()->getInt('max_game_id') + 1;

		$join = new DateTime(Request::get('game_join'));
		$start = Request::get('game_start') === '' ? $join :
			new DateTime(Request::get('game_start'));
		$end = new DateTime(Request::get('game_end'));

		$game = Game::createGame($newID);
		$game->setName(Request::get('game_name'));
		$game->setDescription(Request::get('desc'));
		$game->setGameTypeID(Request::getInt('game_type'));
		$game->setMaxTurns(Request::getInt('max_turns'));
		$game->setStartTurnHours(Request::getInt('start_turns'));
		$game->setMaxPlayers(Request::getInt('max_players'));
		$game->setAllianceMaxPlayers(Request::getInt('alliance_max_players'));
		$game->setAllianceMaxVets(Request::getInt('alliance_max_vets'));
		$game->setJoinTime($join->getTimestamp());
		$game->setStartTime($start->getTimestamp());
		$game->setEndTime($end->getTimestamp());
		$game->setGameSpeed(Request::getFloat('game_speed'));
		$game->setIgnoreStats(Request::getBool('ignore_stats'));
		$game->setStartingCredits(Request::getInt('starting_credits'));
		$game->setCreditsNeeded(Request::getInt('creds_needed'));
		$game->setStartingRelations(Request::getInt('relations'));

		// Start game disabled by default
		$game->setEnabled(false);
		$game->save();

		$container = new CreateGalaxies($game->getGameID());
		$container->go();
	}

}
