<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Smr\Account;
use Smr\Game;
use Smr\Globals;
use Smr\Page\AccountPage;
use Smr\Template;

class EditGame extends AccountPage {

	public function __construct(
		private readonly int $gameID,
		private readonly EditGalaxy $returnTo,
	) {}

	public function build(Account $account, Template $template): void {
		$template->pageTopic = 'Edit Game Details';

		$gameID = $this->gameID;

		// Use Alskant-Creonti as a proxy for the starting political relations
		$relations = Globals::getRaceRelations($gameID, RACE_ALSKANT)[RACE_CREONTI];

		$game = Game::getGame($gameID);
		$gameArray = [
			'name' => $game->getName(),
			'description' => $game->getDescription(),
			'speed' => $game->getGameSpeed(),
			'maxTurns' => $game->getMaxTurns(),
			'startTurnHours' => $game->getStartTurnHours(),
			'maxPlayers' => $game->getMaxPlayers(),
			'joinDate' => date('Y-m-d', $game->getJoinTime()),
			'startDate' => date('Y-m-d', $game->getStartTime()),
			'endDate' => date('Y-m-d', $game->getEndTime()),
			'smrCredits' => $game->getCreditsNeeded(),
			'gameType' => $game->getGameType(),
			'allianceMax' => $game->getAllianceMaxPlayers(),
			'allianceMaxVets' => $game->getAllianceMaxVets(),
			'startCredits' => $game->getStartingCredits(),
			'ignoreStats' => $game->isIgnoreStats(),
			'relations' => $relations,
			'destroyPorts' => $game->canDestroyPorts(),
		];

		$template->pageRenderer = fn() => EditGameRenderer::render(
			Game: $gameArray,
			ProcessingHREF: new EditGameProcessor($this->gameID, $this->returnTo)->href(),
			SubmitValue: 'Modify Game',
			CancelHREF: $this->returnTo->href(),
		);
	}

}
