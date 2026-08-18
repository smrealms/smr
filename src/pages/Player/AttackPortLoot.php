<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class AttackPortLoot extends PlayerPage {

	public function build(Player $player, Template $template): void {
		$template->pageTopic = 'Looting The Port';
		$template->pageRenderer = fn() => AttackPortLootRenderer::render(
			ThisPlayer: $player,
			ThisPort: $player->getSectorPort(),
			ThisShip: $player->getShip(),
		);
	}

}
