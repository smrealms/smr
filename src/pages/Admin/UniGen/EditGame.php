<?php declare(strict_types=1);

namespace Smr\Pages\Admin\UniGen;

use Globals;
use Smr\Page\AccountPage;
use Smr\Template;
use SmrAccount;
use SmrGame;

class EditGame extends AccountPage {

	public string $file = 'admin/unigen/game_edit.php';

	public function __construct(
		private readonly int $gameID,
		private readonly int $galaxyID
	) {}

	public function build(SmrAccount $account, Template $template): void {
		$template->assign('PageTopic', 'Edit Game Details');

		$gameID = $this->gameID;

		// Use Alskant-Creonti as a proxy for the starting political relations
		$relations = Globals::getRaceRelations($gameID, RACE_ALSKANT)[RACE_CREONTI];

		$game = SmrGame::getGame($gameID);
		$gameArray = [
			'name' => $game->getName(),
			'description' => $game->getDescription(),
			'speed' => $game->getGameSpeed(),
			'maxTurns' => $game->getMaxTurns(),
			'startTurnHours' => $game->getStartTurnHours(),
			'maxPlayers' => $game->getMaxPlayers(),
			'joinDate' => date('d/m/Y', $game->getJoinTime()),
			'startDate' => date('d/m/Y', $game->getStartTime()),
			'endDate' => date('d/m/Y', $game->getEndTime()),
			'smrCredits' => $game->getCreditsNeeded(),
			'gameType' => $game->getGameType(),
			'allianceMax' => $game->getAllianceMaxPlayers(),
			'allianceMaxVets' => $game->getAllianceMaxVets(),
			'startCredits' => $game->getStartingCredits(),
			'ignoreStats' => $game->isIgnoreStats(),
			'relations' => $relations,
		];
		$template->assign('Game', $gameArray);

		$container = new EditGameProcessor($this->gameID, $this->galaxyID);
		$template->assign('ProcessingHREF', $container->href());
		$template->assign('SubmitValue', 'Modify Game');

		$container = new EditGalaxy($this->gameID, $this->galaxyID);
		$template->assign('CancelHREF', $container->href());
	}

}
