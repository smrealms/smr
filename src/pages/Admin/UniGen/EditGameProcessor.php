<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use DateTime;
use Smr\Account;
use Smr\Game;
use Smr\Page\AccountPageProcessor;
use Smr\Request;

class EditGameProcessor extends AccountPageProcessor {

	public function __construct(
		private readonly int $gameID,
		private readonly EditGalaxy $returnTo,
	) {}

	public function build(Account $account): never {
		$join = new DateTime(Request::get('game_join'));
		$start = Request::get('game_start') === '' ? $join :
			new DateTime(Request::get('game_start'));
		$end = new DateTime(Request::get('game_end'));

		$game = Game::getGame($this->gameID);
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
		$game->setDestroyPorts(Request::getBool('destroy_ports'));
		if (!$game->isEnabled() || !$game->hasStarted()) {
			$game->setStartingRelations(Request::getInt('relations'));
		}
		$game->save();

		$this->returnTo->message = '<span class="green">SUCCESS: edited game details</span>';
		$this->returnTo->go();
	}

}
