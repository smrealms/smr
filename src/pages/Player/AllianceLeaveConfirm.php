<?php declare(strict_types=1);

namespace Smr\Pages\Player;

use Smr\Menu;
use Smr\Page\PlayerPage;
use Smr\Player;
use Smr\Template;

class AllianceLeaveConfirm extends PlayerPage {

	public function build(Player $player, Template $template): void {
		$alliance = $player->getAlliance();

		$template->pageTopic = $alliance->getAllianceDisplayName(false, true);
		Menu::alliance($alliance->getAllianceID());

		$template->pageRenderer = fn() => AllianceLeaveConfirmRenderer::render(
			YesHREF: new AllianceLeaveProcessor()->href(),
			NoHREF: new CurrentSector()->href(),
		);
	}

}
