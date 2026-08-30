<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Force;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class ForcesDrop extends PlayerPage {

	public function __construct(
		private readonly ?int $ownerPlayerID = null,
	) {}

	public function build(Player $player, Template $template): void {
		if ($this->ownerPlayerID !== null) {
			$owner = Player::getPlayer($this->ownerPlayerID);
			$template->pageTopic = 'Change ' . htmlentities($owner->getPlayerName()) . '\'s Forces';
			$ownerPlayerID = $this->ownerPlayerID;
		} else {
			$template->pageTopic = 'Drop Forces';
			$ownerPlayerID = $player->getPlayerID();
		}

		$forces = Force::getForce($player->getGameID(), $player->getSectorID(), $ownerPlayerID);

		$template->pageRenderer = fn() => ForcesDropRenderer::render(
			Forces: $forces,
			SubmitHREF: new ForcesDropProcessor($ownerPlayerID)->href(),
			ThisShip: $player->getShip(),
		);
	}

}
