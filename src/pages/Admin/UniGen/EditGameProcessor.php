<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use DateTime;
use Smr\Page\AccountPageProcessor;
use Smr\Request;
use SmrAccount;
use SmrGame;

class EditGameProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $gameID,
		private readonly int $galaxyID
	) {}

	public function build(SmrAccount $account): never {
		// Get the dates ("|" sets hr/min/sec to 0)
		$join = DateTime::createFromFormat('d/m/Y|', Request::get('game_join'));
		$start = empty(Request::get('game_start')) ? $join :
			DateTime::createFromFormat('d/m/Y|', Request::get('game_start'));
		$end = DateTime::createFromFormat('d/m/Y|', Request::get('game_end'));

		$game = SmrGame::getGame($this->gameID);
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
		$game->setIgnoreStats(Request::get('ignore_stats') == 'Yes');
		$game->setStartingCredits(Request::getInt('starting_credits'));
		$game->setCreditsNeeded(Request::getInt('creds_needed'));
		if (!$game->hasStarted()) {
			$game->setStartingRelations(Request::getInt('relations'));
		}
		$game->save();

		$message = '<span class="green">SUCCESS: edited game details</span>';
		$container = new EditGalaxy($this->gameID, $this->galaxyID, $message);
		$container->go();
	}

}
